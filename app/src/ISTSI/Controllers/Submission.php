<?php
declare(strict_types = 1);

namespace ISTSI\Controllers;

use ISTSI\Helpers\Registration;
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

        $userMapper = $database->mapper('\ISTSI\Entities\User');
        $proposalMapper = $database->mapper('\ISTSI\Entities\Proposal');
        $submissionMapper = $database->mapper('\ISTSI\Entities\Submission');

        $user = $userMapper->get($uid);

        $doneProposals = array_column($submissionMapper->where(['user_id'=> $uid])->toArray(), 'proposal_id');

        $availableProposals = [];

        foreach ($proposalMapper->all() as $proposalData) {
            if (!in_array($proposalData->id, $doneProposals) &&
                in_array($user->course, $proposalData->courses) &&
                in_array($user->year, $proposalData->years)
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

        $result = $submissionMapper->first(['user_id' => $uid, 'proposal_id' => $proposal]);

        if (!$result) {
            //TODO:
            die('E_INVALID_PROPOSAL');
        }

        return $response->withJson([
            'status' => 'success',
            'data'   => ['observations' => $result->observations]
        ]);
    }

    public function getFile(Request $request, Response $response, $args)
    {
        $database = $this->c->get('database');
        $session = $this->c->get('session');
        $settingsFiles = $this->c->get('settings')['files'];
        $settingsProgram = $this->c->get('settings')['program'];

        $uid = $session->getUid();

        $proposal = $args['proposal'];
        $file = $args['file'];

        if ($file !== 'CV' && $file !== 'CM') {
            //TODO:throw new IException(E_URL_INVALID);
            die('E_INVALID_FILE');
        }

        $submissionMapper = $database->mapper('\ISTSI\Entities\Submission');
        $result = $submissionMapper->first(['user_id' => $uid, 'proposal_id' => $proposal]);

        if (!$result) {
            die('E_INVALID_PROPOSAL');
        }

        $fileDir = $settingsFiles['path'] . $settingsProgram['year'] . '/' . $proposal . '/';
        $fileName = $uid . '-'. $file . '.' . $settingsFiles['extension'];
        $filePath = $fileDir . $fileName;

        $stream = new Stream(fopen($filePath, 'rb'));

        //TODO:$logger->addRecord(I_VIEW_FILE, ['userID' => $_SESSION['userID'], 'fileID' => $file, 'proposal' => $proposal]);

        return $response->withHeader('Content-Type:', $settingsFiles['mimeType'])
                        ->withHeader('Content-Disposition', 'inline; filename=' . basename($filePath))
                        ->withHeader('Content-Length', filesize($filePath))
                        ->withBody($stream);
    }

    public function create(Request $request, Response $response, $args)
    {
        //TODO: VERIFIY USER HAS HIS ACCOUNT WITH ALL INFO
        $database = $this->c->get('database');
        $fileManager = $this->c->get('filemanager');
        $session = $this->c->get('session');
        $settingsProgram = $this->c->get('settings')['program'];

        $uid = $session->getUid();

        $userMapper = $database->mapper('\ISTSI\Entities\User');
        $proposalMapper = $database->mapper('\ISTSI\Entities\Proposal');
        $submissionMapper = $database->mapper('\ISTSI\Entities\Submission');

        $user = $userMapper->get($uid);
        $proposals = $proposalMapper->all();

        // Check whether registration is open
        if (!Registration::isOpen($settingsProgram['registrationStart'], $settingsProgram['registrationEnd'])) {
            //TODO: throw new IException(E_REGISTRATION_CLOSED, null, 'registration');
            die('E_REGISTRATION_CLOSED');
        }

        $proposal = $args['proposal'];

        // Validate given proposal
        $valid = false;
        foreach ($proposals as $proposalData) {
            if ($proposalData->id === $proposal &&
                in_array($user->course, $proposalData->courses) &&
                in_array($user->year, $proposalData->years)
            ) {
                $valid = true;
                break;
            }
        }
        if (!$valid) {
            //throw new IException(E_PROPOSAL_INVALID, null, 'proposal');
            die('E_PROPOSAL_INVALID');
        }

        // Insert new submission
        $observations = $request->getParsedBodyParam('observations');
        if (!empty($observations) &&
            !filter_var(
                $observations,
                FILTER_VALIDATE_REGEXP,
                ['options' => ['regexp' => '/^(.){0,' . $settingsProgram['observationsMaxSize'] . '}$/s']]
            )
        ) {
            //TODO: throw new IException(E_OBSERVATIONS_INVALID
            die('E_OBSERVATIONS_INVALID');
        }

        // File validation
        if (!$fileManager->isUploaded('CV')) {
            //TODO:throw new IException(E_FILE_UPLOAD, ['fileID' => 'CV'], 'fileCV');
            die('E_FILE_UPLOAD');
        };
        if (!$fileManager->isUploaded('CM')) {
            //TODO:throw new IException(E_FILE_UPLOAD, ['fileID' => 'CM'], 'fileCM');
            die('E_FILE_UPLOAD');
        };

        // File Upload
        $settingsFiles = $this->c->get('settings')['files'];

        $fileDir = $settingsFiles['path'] . $settingsProgram['year'] . '/' . $proposal . '/';
        $fileNameCV = $uid . '-CV.' . $settingsFiles['extension'];
        $fileNameCM = $uid . '-CM.' . $settingsFiles['extension'];

        $fileManager->parseUpload('CV', $fileDir, $fileNameCV, true);
        $fileManager->parseUpload('CM', $fileDir, $fileNameCM, true);


        // Database Update
        $submission = $submissionMapper->build([
            'user_id'      => $uid,
            'proposal_id'  => $proposal,
            'observations' => $observations,
        ]);

        if (!$submissionMapper->save($submission)) {
            //TODO:throw new IException(E_SUBMISSION_NEW_DUPLICATE, null, 'proposal');
            die('E_SUBMISSION_NEW_DUPLICATE');
        }

        //TODO:$logger->addRecord(I_NEW_SUBMISSION, ['userID' => $_SESSION['userID'], 'proposal' => $proposal]);
        return $response->withJson([
            'status' => 'success',
            'data'   => null
        ]);
    }

    public function update(Request $request, Response $response, $args)
    {
        //TODO: VERIFIY USER HAS HIS ACCOUNT WITH ALL INFO
        $database = $this->c->get('database');
        $fileManager = $this->c->get('filemanager');
        $session = $this->c->get('session');
        $settingsProgram = $this->c->get('settings')['program'];
        $settingsFiles = $this->c->get('settings')['files'];

        $uid = $session->getUid();

        $submissionMapper = $database->mapper('\ISTSI\Entities\Submission');

        // Check whether registration is open
        if (!Registration::isOpen($settingsProgram['registrationStart'], $settingsProgram['registrationEnd'])) {
            //TODO: throw new IException(E_REGISTRATION_CLOSED, null, 'registration');
            die('E_REGISTRATION_CLOSED');
        }

        $proposal = $args['proposal'];

        if (!isset($_POST['observations']) && empty($_FILES['CV']['name']) && empty($_FILES['CM']['name'])) {
            //TODO:throw new IException(E_SUBMISSION_EDIT_EMPTY, null, 'all');
            die('E_SUBMISSION_EDIT_EMPTY');
        }

        if (isset($_POST['observations'])) {
            $observations = $request->getParsedBodyParam('observations');

            if (!empty($observations) &&
                !filter_var(
                    $observations,
                    FILTER_VALIDATE_REGEXP,
                    ['options' => ['regexp' => '/^(.){0,' . $settingsProgram['observationsMaxSize'] . '}$/s'],]
                )
            ) {
                //TODO:throw new IException(E_OBSERVATIONS_INVALID, ['observationsMaxSize' => $configProgram['observationsMaxSize']],'observations');
                die('E_OBSERVATIONS_INVALID');
            }

            $submission = $submissionMapper->first(['user_id' => $uid, 'proposal_id' => $proposal]);
            $submission->observations = $observations;
            $submissionMapper->update($submission);

            //TODO:$logger->addRecord(I_EDIT_OBSERVATIONS, ['userID' => $_SESSION['userID'], 'proposal' => $proposal]);
        }

        if (!empty($_FILES['CV']['name'])) {
            if (!$fileManager->isUploaded('CV')) {
                //TODO:throw new IException(E_FILE_UPLOAD, ['fileID' => 'CV'], 'fileCV');
                die('E_FILE_UPLOAD');
            };

            $fileDir = $settingsFiles['path'] . $settingsProgram['year'] . '/' . $proposal . '/';
            $fileName = $uid . '-CV.' . $settingsFiles['extension'];

            $fileManager->parseUpload('CV', $fileDir, $fileName, true);

            //TODO:$logger->addRecord(I_EDIT_FILE,['userID' => $_SESSION['userID'], 'fileID' => 'CV', 'proposal' => $proposal]);
        }

        if (!empty($_FILES['CM']['name'])) {
            if (!$fileManager->isUploaded('CM')) {
                //TODO:throw new IException(E_FILE_UPLOAD, ['fileID' => 'CM'], 'fileCM');
                die('E_FILE_UPLOAD');
            };

            $fileDir = $settingsFiles['path'] . $settingsProgram['year'] . '/' . $proposal . '/';
            $fileName = $uid . '-CM.' . $settingsFiles['extension'];

            $fileManager->parseUpload('CM', $fileDir, $fileName, true);

            //TODO: $logger->addRecord(I_EDIT_FILE,['userID' => $_SESSION['userID'], 'fileID' => 'CM', 'proposal' => $proposal]);
        }

        return $response->withJson([
            'status' => 'success',
            'data'   => null
        ]);
    }

    public function delete(Request $request, Response $response, $args)
    {
        //TODO: VERIFIY USER HAS HIS ACCOUNT WITH ALL INFO
        $database = $this->c->get('database');
        $fileManager = $this->c->get('filemanager');
        $session = $this->c->get('session');
        $settingsProgram = $this->c->get('settings')['program'];

        $uid = $session->getUid();

        $submissionMapper = $database->mapper('\ISTSI\Entities\Submission');

        // Check whether registration is open
        if (!Registration::isOpen($settingsProgram['registrationStart'], $settingsProgram['registrationEnd'])) {
            //TODO: throw new IException(E_REGISTRATION_CLOSED, null, 'registration');
            die('E_REGISTRATION_CLOSED');
        }

        $proposal = $args['proposal'];

        $result = $submissionMapper->delete(['user_id' => $uid, 'proposal_id' => $proposal]);
        if (!$result) {
            //TODO:If submission doesn't exist, dont attempt to delete file
            die('E_DB_OP');
        }

        // Delete submission files
        $settingsFiles = $this->c->get('settings')['files'];

        $fileDir = $settingsFiles['path'] . $settingsProgram['year'] . '/' . $proposal . '/';
        $fileNameCV = $uid . '-CV.' . $settingsFiles['extension'];
        $fileNameCM = $uid . '-CM.' . $settingsFiles['extension'];

        if (!$fileManager->deleteFile($fileDir . $fileNameCV)) {
            //TODO:throw new IException(E_FILE_DELETE, ['fileID' => 'CV'], 'file');
            die('E_FILE_DELETE');
        };
        if (!$fileManager->deleteFile($fileDir . $fileNameCM)) {
            //TODO:throw new IException(E_FILE_DELETE, ['fileID' => 'CM'], 'file');
            die('E_FILE_DELETE');
        };

        //TODO:$logger->addRecord(I_DELETE_SUBMISSION, ['userID' => $_SESSION['userID'], 'proposal' => $proposal]);
        return $response->withJson([
            'status' => 'success',
            'data'   => null
        ]);
    }
}
