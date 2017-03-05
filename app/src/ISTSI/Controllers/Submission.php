<?php
declare(strict_types = 1);

namespace ISTSI\Controllers;

use ISTSI\Identifiers\Error;
use ISTSI\Identifiers\Notice;
use ISTSI\Identifiers\Info;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Stream;

class Submission
{
    protected $c;

    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;
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

        foreach ($proposalMapper->all() as $proposalData) {
            if (!in_array($proposalData->id, $doneProposals) &&
                in_array($student->course, $proposalData->courses)
            ) {
                array_push($availableProposals, $proposalData->id);
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
            throw new \Exception(Notice::SUBMISSION_INVALID);
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

        if (!in_array($file, ['CV', 'CM'])) {
            throw new \Exception(Notice::SUBMISSION_INVALID);
        }

        $submissionMapper = $database->mapper('\ISTSI\Entities\Submission');

        if (!$submissionMapper->first(['student_id' => $uid, 'proposal_id' => $proposal])) {
            throw new \Exception(Notice::SUBMISSION_INVALID);
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
        //TODO: VERIFY STUDENT HAS HIS ACCOUNT WITH ALL INFO
        $database = $this->c->get('database');
        $fileManager = $this->c->get('filemanager');
        $logger = $this->c->get('logger');
        $session = $this->c->get('session');
        $settingsProgram = $this->c->get('settings')['program'];

        $uid = $session->getUid();

        $submissionMapper = $database->mapper('\ISTSI\Entities\Submission');

        $proposal = $args['proposal'];

        // Database Update
        $submission = $submissionMapper->build([
            'student_id' => $uid,
            'proposal_id' => $proposal,
            'observations' => $request->getParsedBodyParam('observations')
        ]);

        if (!$submissionMapper->save($submission)) {
            throw new \Exception(Notice::SUBMISSION_INVALID);
        }

        // File Upload
        foreach ($request->getUploadedFiles() as $type => $file) {
            if (in_array($type, ['CV', 'CM']) && $file->file !== '') {
                $fileManager->parseUpload(
                    $request->getUploadedFiles()[$type],
                    $fileManager->getFilePath([
                        '{year}' => $settingsProgram['year'],
                        '{proposal}' => $proposal,
                        '{uid}' => $uid,
                        '{type}' => $type
                    ])
                );
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
        //TODO: VERIFY STUDENT HAS HIS ACCOUNT WITH ALL INFO
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
        if (!$submissionMapper->update($submission)) {
            throw new \Exception(Notice::SUBMISSION_INVALID);
        }

        foreach ($request->getUploadedFiles() as $type => $file) {
            if (in_array($type, ['CV', 'CM']) && $file->file !== '') {
                $fileManager->parseUpload(
                    $request->getUploadedFiles()[$type],
                    $fileManager->getFilePath([
                        '{year}' => $settingsProgram['year'],
                        '{proposal}' => $proposal,
                        '{uid}' => $uid,
                        '{type}' => $type
                    ])
                );
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
        //TODO: VERIFY STUDENT HAS HIS ACCOUNT WITH ALL INFO
        $database = $this->c->get('database');
        $fileManager = $this->c->get('filemanager');
        $logger = $this->c->get('logger');
        $session = $this->c->get('session');
        $settingsProgram = $this->c->get('settings')['program'];

        $uid = $session->getUid();

        $submissionMapper = $database->mapper('\ISTSI\Entities\Submission');

        $proposal = $args['proposal'];

        if (!$submissionMapper->delete(['student_id' => $uid, 'proposal_id' => $proposal])) {
            throw new \Exception(Notice::SUBMISSION_INVALID);
        }

        // Delete submission files
        $substitutions = [
            '{year}' => $settingsProgram['year'],
            '{proposal}' => $proposal,
            '{uid}' => $uid,
            '{type}' => 'CV'
        ];
        if (!$fileManager->deleteFile($fileManager->getFilePath($substitutions))) {
            throw new \Exception(Error::FILE_DELETE);
        };
        $substitutions['{type}'] = 'CM';
        if (!$fileManager->deleteFile($fileManager->getFilePath($substitutions))) {
            throw new \Exception(Error::FILE_DELETE);
        };

        $logger->addRecord(Info::SUBMISSION_DELETE, ['uid' => $uid, 'proposal' => $proposal]);

        return $response->withJson([
            'status' => 'success',
            'data'   => null
        ]);
    }
}
