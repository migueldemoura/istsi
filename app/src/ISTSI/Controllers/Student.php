<?php
declare(strict_types = 1);

namespace ISTSI\Controllers;

use ISTSI\Helpers\DateTime;
use ISTSI\Identifiers\Info;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class Student
{
    protected $c;

    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;
    }

    public function showAccount(Request $request, Response $response, $args)
    {
        $database = $this->c->get('database');
        $session = $this->c->get('session');
        $settingsProgram = $this->c->get('settings')['program'];

        $studentMapper = $database->mapper('\ISTSI\Entities\Student');
        $uid = $session->getUid();
        $student = $studentMapper->get($uid);

        $templateArgs = [
            'programName'  => $settingsProgram['name'],
            'programYear'  => $settingsProgram['year'],
            'uid'          => $uid,
            'logout'       => '/auth/fenix/logout',
            'email'        => $student->email,
            'emailMaxSize' => $settingsProgram['emailMaxSize'],
            'token'        => $session->getToken()
        ];

        return $this->c->get('renderer')->render($response, 'student/account.twig', $templateArgs);
    }

    public function showDashboard(Request $request, Response $response, $args)
    {
        $database = $this->c->get('database');
        $session = $this->c->get('session');
        $settingsProgram = $this->c->get('settings')['program'];

        // Check if student has already provided its phone
        $studentMapper = $database->mapper('\ISTSI\Entities\Student');
        $uid = $session->getUid();
        $student = $studentMapper->get($uid);

        if ($student->phone === null) {
            return $response->withStatus(302)->withHeader('Location', '/student/account');
        }

        $templateArgs = [
            'programName' => $settingsProgram['name'],
            'programYear' => $settingsProgram['year'],
            'uid'         => $uid,
            'logout'      => '/auth/fenix/logout',
            'observationsMaxSize' => $settingsProgram['observationsMaxSize'],
            'token'       => $session->getToken(),
            'onPeriod'    => DateTime::isBetween(
                $settingsProgram['period']['start'],
                $settingsProgram['period']['end']
            )
        ];

        return $this->c->get('renderer')->render($response, 'student/dashboard.twig', $templateArgs);
    }

    public function update(Request $request, Response $response, $args)
    {
        $database = $this->c->get('database');
        $logger = $this->c->get('logger');
        $session = $this->c->get('session');
        $settingsProgram = $this->c->get('settings')['program'];

        $email = trim($request->getParsedBody()['email']);
        $phone = trim($request->getParsedBody()['phone']);

        if (empty($email) ||
            !filter_var($email, FILTER_VALIDATE_EMAIL) ||
            !filter_var(
                $email,
                FILTER_VALIDATE_REGEXP,
                ['options' => ['regexp' => '/^(.){0,' . $settingsProgram['emailMaxSize'] . '}$/']]
            )
        ) {
            //TODO:throw new IException(E_EMAIL_INVALID, null, 'email');
            die('EMAIL_INVALID');
        }

        if (empty($phone) ||
            !filter_var(
                $phone,
                FILTER_VALIDATE_REGEXP,
                ['options' => ['regexp' => '/^[0-9]{0,' . $settingsProgram['phoneSize'] . '}$/']]
            )
        ) {
            //TODO:throw new IException(E_PHONE_INVALID, null, 'phone');
            die('PHONE_INVALID');
        }

        $studentMapper = $database->mapper('\ISTSI\Entities\Student');
        $uid = $session->getUid();
        $student = $studentMapper->get($uid);
        $student->email = $email;
        $student->phone = $phone;
        $studentMapper->update($student);

        $logger->addRecord(Info::ACCOUNT_INFO, ['uid' => $uid]);

        $data = [
            'status' => 'success',
            'data'   => ''
        ];
        return $response->withJson($data);
    }
}
