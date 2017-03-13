<?php
declare(strict_types = 1);

namespace ISTSI\Controllers;

use ISTSI\Helpers\DateTime;
use ISTSI\Identifiers\Info;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class Company
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

        $companyMapper = $database->mapper('\ISTSI\Entities\Company');

        $uid = $session->getUid();

        $company = $companyMapper->first(['email' => $uid]);

        $templateArgs = [
            'programName'    => $settingsProgram['name'],
            'programYear'    => $settingsProgram['year'],
            'uid'            => $uid,
            'logout'         => '/auth/passwordless/logout',
            'name'           => $company->name,
            'representative' => $company->representative,
            'phone'          => $company->phone,
            'token'          => $session->getToken()
        ];

        return $this->c->get('renderer')->render($response, 'company/account.twig', $templateArgs);
    }

    public function showDashboard(Request $request, Response $response, $args)
    {
        $database = $this->c->get('database');
        $session = $this->c->get('session');
        $settingsProgram = $this->c->get('settings')['program'];

        // Check if company has already provided its info
        $companyMapper = $database->mapper('\ISTSI\Entities\Company');
        $uid = $session->getUid();
        $company = $companyMapper->first(['email' => $uid]);

        if ($company->name === null || $company->representative === null || $company->phone === null) {
            return $response->withStatus(302)->withHeader('Location', '/company/account');
        }

        $templateArgs = [
            'programName' => $settingsProgram['name'],
            'programYear' => $settingsProgram['year'],
            'uid'         => $uid,
            'logout'      => '/auth/passwordless/logout',
            'token'       => $session->getToken(),
            'onPeriod'    => DateTime::isBefore($settingsProgram['period']['end'])
        ];

        return $this->c->get('renderer')->render($response, 'company/dashboard.twig', $templateArgs);
    }

    public function showLogin(Request $request, Response $response, $args)
    {
        $settingsProgram = $this->c->get('settings')['program'];

        $templateArgs = [
            'programName' => $settingsProgram['name'],
            'programYear' => $settingsProgram['year'],
        ];

        return $this->c->get('renderer')->render($response, 'company/login.twig', $templateArgs);
    }

    public function update(Request $request, Response $response, $args)
    {
        $database = $this->c->get('database');
        $logger = $this->c->get('logger');
        $session = $this->c->get('session');

        $companyMapper = $database->mapper('\ISTSI\Entities\Company');
        $uid = $session->getUid();

        $company = $companyMapper->first(['email' => $uid]);
        $company->name = $request->getParsedBody()['name'];
        $company->representative = $request->getParsedBody()['representative'];
        $company->phone = $request->getParsedBody()['phone'];
        if (!$companyMapper->update($company)) {
            die('INVALID_DATA');
        }

        $logger->addRecord(Info::ACCOUNT_INFO, ['uid' => $uid]);

        $data = [
            'status' => 'success',
            'data'   => ''
        ];
        return $response->withJson($data);
    }
}
