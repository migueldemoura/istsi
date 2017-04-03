<?php
declare(strict_types = 1);

namespace ISTSI\Controllers;

use ISTSI\Exception\Exception;
use ISTSI\Identifiers\Error;
use ISTSI\Identifiers\Notice;
use ISTSI\Identifiers\Info;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Stream;
use ZipArchive;

class Submission
{
    protected $c;

    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;
    }

    public function getAll(Request $request, Response $response, $args)
    {
        $database = $this->c->get('database');
        $fileManager = $this->c->get('filemanager');
        $logger = $this->c->get('logger');
        $session = $this->c->get('session');
        $settingsFiles = $this->c->get('settings')['files'];
        $settingsProgram = $this->c->get('settings')['program'];

        $uid = $session->getUid();

        $proposalMapper = $database->mapper('\ISTSI\Entities\Proposal');
        $companyMapper = $database->mapper('\ISTSI\Entities\Company');
        $submissionMapper = $database->mapper('\ISTSI\Entities\Submission');

        $companyId = $companyMapper->first(['email' => $uid])->id;

        $submissions = $submissionMapper->where([
            'proposal_id' => array_column($proposalMapper->where(['company_id' => $companyId])->toArray(), 'id')
        ]);

        $zip = new ZipArchive();
        $directory = $settingsFiles['directoryRoot'] . 'zip/';
        if (!$fileManager->createDirectory($directory)) {
            throw new Exception(Error::DIR_CREATE);
        };
        $filePath = realpath($directory) . '/' . $companyId;
        if (!$fileManager->deleteFile($filePath)) {
            throw new Exception(Error::ZIP_DELETE);
        }
        if ($zip->open($filePath, ZipArchive::CREATE) !== true) {
            throw new Exception(Error::ZIP_CREATE);
        }
        sleep(5);
        if (count($submissions) === 0) {
            $zip->addFromString('Nenhuma candidatura submetida', '');
        }
        foreach ($submissions as $submission) {
            $map = [
                '{year}' => $settingsProgram['year'],
                '{proposal}' => $submission->proposal_id,
                '{uid}' => $submission->student_id,
                '{type}' => 'CV'
            ];
            if (!$zip->addFile($fileManager->getFilePath($map), $fileManager->getRelativeFilePath($map))) {
                throw new Exception(Error::ZIP_CREATE);
            };
            $map['{type}'] = 'CM';
            if (!$zip->addFile($fileManager->getFilePath($map), $fileManager->getRelativeFilePath($map))) {
                throw new Exception(Error::ZIP_CREATE);
            };
        }
        $zip->close();

        $logger->addRecord(Info::SUBMISSION_DOWNLOAD_ALL, ['uid' => $uid]);

        return $response->withHeader('Content-Type:', 'application/zip')
                        ->withHeader('Content-Disposition', 'inline; filename="CVS.zip"')
                        ->withHeader('Content-Length', filesize($filePath))
                        ->withBody(new Stream(fopen($filePath, 'rb')));
    }

    public function getList(Request $request, Response $response, $args)
    {
        $database = $this->c->get('database');
        $session = $this->c->get('session');

        $uid = $session->getUid();

        $studentMapper = $database->mapper('\ISTSI\Entities\Student');
        $proposalMapper = $database->mapper('\ISTSI\Entities\Proposal');
        $submissionMapper = $database->mapper('\ISTSI\Entities\Submission');

        $student = $studentMapper->get($uid);

        $doneProposals = array_column($submissionMapper->where(['student_id'=> $uid])->toArray(), 'proposal_id');

        $availableProposals = [];

        foreach ($proposalMapper->all() as $proposal) {
            if (!in_array($proposal->id, $doneProposals, true) &&
                in_array(
                    $student->course,
                    array_column($proposal->relation('courses')->getIterator()->toArray(), 'acronym'),
                    true
                )
            ) {
                array_push($availableProposals, $proposal->id);
            }
        }

        return $response->withJson([
            'status' => 'success',
            'data'   => [
                'proposals' => ['done' => $doneProposals, 'available' => $availableProposals]
            ]
        ]);
    }

    public function getData(Request $request, Response $response, $args)
    {
        $database = $this->c->get('database');
        $session = $this->c->get('session');

        $uid = $session->getUid();

        $proposal = $args['proposal'];

        $submissionMapper = $database->mapper('\ISTSI\Entities\Submission');

        $result = $submissionMapper->first(['student_id' => $uid, 'proposal_id' => $proposal]);
        if (!$result) {
            throw new Exception(Notice::SUBMISSION_INVALID);
        }

        return $response->withJson([
            'status' => 'success',
            'data'   => ['observations' => $result->observations]
        ]);
    }

    public function getFile(Request $request, Response $response, $args)
    {
        $database = $this->c->get('database');
        $fileManager = $this->c->get('filemanager');
        $logger = $this->c->get('logger');
        $session = $this->c->get('session');
        $settingsFiles = $this->c->get('settings')['files'];
        $settingsProgram = $this->c->get('settings')['program'];

        $uid = $session->getUid();

        $proposal = $args['proposal'];
        $file = $args['file'];

        if (!in_array($file, ['CV', 'CM'], true)) {
            throw new Exception(Notice::SUBMISSION_INVALID);
        }

        $submissionMapper = $database->mapper('\ISTSI\Entities\Submission');

        if (!$submissionMapper->first(['student_id' => $uid, 'proposal_id' => $proposal])) {
            throw new Exception(Notice::SUBMISSION_INVALID);
        }

        $filePath = $fileManager->getFilePath([
            '{year}' => $settingsProgram['year'],
            '{proposal}' => $proposal,
            '{uid}' => $uid,
            '{type}' => $file
        ]);

        $logger->addRecord(Info::SUBMISSION_VIEW, ['uid' => $uid, 'file' => $file, 'proposal' => $proposal]);

        return $response->withHeader('Content-Type:', $settingsFiles['mimeType'])
                        ->withHeader('Content-Disposition', 'inline; filename=' . basename($filePath))
                        ->withHeader('Content-Length', filesize($filePath))
                        ->withBody(new Stream(fopen($filePath, 'rb')));
    }

    public function create(Request $request, Response $response, $args)
    {
        $database = $this->c->get('database');
        $fileManager = $this->c->get('filemanager');
        $logger = $this->c->get('logger');
        $session = $this->c->get('session');
        $settingsProgram = $this->c->get('settings')['program'];

        $uid = $session->getUid();

        $proposalMapper = $database->mapper('\ISTSI\Entities\Proposal');
        $studentMapper = $database->mapper('\ISTSI\Entities\Student');
        $submissionMapper = $database->mapper('\ISTSI\Entities\Submission');

        $proposal = $args['proposal'];

        // Check if proposal accepts the student's course
        if (!in_array(
            $studentMapper->get($uid)->course,
            array_column($proposalMapper->get($proposal)->relation('courses')->getIterator()->toArray(), 'acronym'),
            true
        )) {
            throw new Exception(Notice::SUBMISSION_INVALID);
        }

        // Database Update
        $submission = $submissionMapper->build([
            'student_id' => $uid,
            'proposal_id' => $proposal,
            'observations' => $request->getParsedBodyParam('observations')
        ]);

        if ($submissionMapper->save($submission) === false) {
            throw new Exception(Notice::SUBMISSION_INVALID);
        }

        // File Upload
        foreach ($request->getUploadedFiles() as $type => $file) {
            if (in_array($type, ['CV', 'CM'], true) && $file->file !== '') {
                if (!$fileManager->parseUpload(
                    $request->getUploadedFiles()[$type],
                    $fileManager->getFilePath([
                        '{year}' => $settingsProgram['year'],
                        '{proposal}' => $proposal,
                        '{uid}' => $uid,
                        '{type}' => $type
                    ])
                )) {
                    $substitutions = [
                        '{year}' => $settingsProgram['year'],
                        '{proposal}' => $proposal,
                        '{uid}' => $uid,
                        '{type}' => 'CV'
                    ];
                    $fileManager->deleteFile($fileManager->getFilePath($substitutions));
                    $substitutions['{type}'] = 'CM';
                    $fileManager->deleteFile($fileManager->getFilePath($substitutions));

                    $submissionMapper->delete(['student_id' => $uid, 'proposal_id' => $proposal]);

                    return $response->withJson([
                        'status' => 'fail',
                        'data'   => 'data'
                    ]);
                }
            }
        }

        $logger->addRecord(Info::SUBMISSION_NEW, ['uid' => $uid, 'proposal' => $proposal]);

        return $response->withJson([
            'status' => 'success',
            'data'   => null
        ]);
    }

    public function update(Request $request, Response $response, $args)
    {
        $database = $this->c->get('database');
        $fileManager = $this->c->get('filemanager');
        $logger = $this->c->get('logger');
        $session = $this->c->get('session');
        $settingsProgram = $this->c->get('settings')['program'];

        $uid = $session->getUid();

        $submissionMapper = $database->mapper('\ISTSI\Entities\Submission');

        $proposal = $args['proposal'];

        $submission = $submissionMapper->first(['student_id' => $uid, 'proposal_id' => $proposal]);
        $submission->observations = $request->getParsedBodyParam('observations');
        if ($submissionMapper->update($submission) === false) {
            throw new Exception(Notice::SUBMISSION_INVALID);
        }

        foreach ($request->getUploadedFiles() as $type => $file) {
            if (in_array($type, ['CV', 'CM'], true) && $file->file !== '') {
                if (!$fileManager->parseUpload(
                    $request->getUploadedFiles()[$type],
                    $fileManager->getFilePath([
                        '{year}' => $settingsProgram['year'],
                        '{proposal}' => $proposal,
                        '{uid}' => $uid,
                        '{type}' => $type
                    ])
                )) {
                    return $response->withJson([
                        'status' => 'fail',
                        'data'   => 'data'
                    ]);
                }
            }
        }

        $logger->addRecord(Info::SUBMISSION_EDIT);

        return $response->withJson([
            'status' => 'success',
            'data'   => null
        ]);
    }

    public function delete(Request $request, Response $response, $args)
    {
        $database = $this->c->get('database');
        $fileManager = $this->c->get('filemanager');
        $logger = $this->c->get('logger');
        $session = $this->c->get('session');
        $settingsProgram = $this->c->get('settings')['program'];

        $uid = $session->getUid();

        $submissionMapper = $database->mapper('\ISTSI\Entities\Submission');

        $proposal = $args['proposal'];

        if ($submissionMapper->delete(['student_id' => $uid, 'proposal_id' => $proposal]) === false) {
            throw new Exception(Notice::SUBMISSION_INVALID);
        }

        // Delete submission files
        $substitutions = [
            '{year}' => $settingsProgram['year'],
            '{proposal}' => $proposal,
            '{uid}' => $uid,
            '{type}' => 'CV'
        ];
        if (!$fileManager->deleteFile($fileManager->getFilePath($substitutions))) {
            throw new Exception(Error::FILE_DELETE);
        };
        $substitutions['{type}'] = 'CM';
        if (!$fileManager->deleteFile($fileManager->getFilePath($substitutions))) {
            throw new Exception(Error::FILE_DELETE);
        };

        $logger->addRecord(Info::SUBMISSION_DELETE, ['uid' => $uid, 'proposal' => $proposal]);

        return $response->withJson([
            'status' => 'success',
            'data'   => null
        ]);
    }
}
