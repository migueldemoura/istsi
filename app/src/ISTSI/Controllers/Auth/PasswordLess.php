<?php
declare(strict_types = 1);

namespace ISTSI\Controllers\Auth;

use ISTSI\Identifiers\Auth;
use ISTSI\Identifiers\Error;
use ISTSI\Identifiers\Info;
use ISTSI\Identifiers\Info as IdentifiersInfo;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class PasswordLess
{
    protected $c;

    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;
    }

    public function init(Request $request, Response $response, $args)
    {
        $database = $this->c->get('database');
        $logger = $this->c->get('logger');
        $session = $this->c->get('session');

        $initTokenMapper = $database->mapper('\ISTSI\Entities\PasswordLess\InitToken');

        if ($initToken = $initTokenMapper->first(['token' => $args['token']])) {
            $email = $initToken->email;
            $initTokenMapper->delete(['email' => $email]);
        } else {
            die('E_INVALID_INIT_TOKEN');
        }

        $session->create($email, Auth::PASSWORDLESS);

        $logger->addRecord(Info::LOGIN, ['email' => $email]);

        return $response->withStatus(302)->withHeader('Location', '/company/account');
    }

    public function generate(Request $request, Response $response, $args)
    {
        $database = $this->c->get('database');
        $logger = $this->c->get('logger');

        $email = $request->getParam('email');

        $authTokenMapper = $database->mapper('\ISTSI\Entities\PasswordLess\AuthToken');

        $authTokenMapper->migrate();
        if ($authTokenMapper->get($email)) {
            if ($authToken = $authTokenMapper->first(
                ['email' => $email, 'updated_at <' => new \DateTime('-15 minutes')]
            )) {
                // Auth token has expired, create a new one
                $authToken->token = bin2hex(random_bytes(64));
                $authTokenMapper->update($authToken);

                //TODO: Send email with code

                $logger->addRecord(IdentifiersInfo::CODE_NEW, ['email' => $email]);
            } else {
                die('CODE_DUPLICATE');
            }
        } else {
            $companyMapper = $database->mapper('\ISTSI\Entities\Company');

            if ($companyMapper->get($email)) {
                if (!$authTokenMapper->create([
                    'email' => $email,
                    'token' => bin2hex(random_bytes(64))
                ])) {
                    throw new \Exception(Error::DB_OP);
                }
            } else {
                die('CODE_INVALID_EMAIL');
            }
        }

        return $response->withStatus(302)->withHeader('Location', '/');
    }

    public function login(Request $request, Response $response, $args)
    {
        $database = $this->c->get('database');
        $logger = $this->c->get('logger');
        $session = $this->c->get('session');

        $authTokenMapper = $database->mapper('\ISTSI\Entities\PasswordLess\AuthToken');

        if (!($authToken = $authTokenMapper->first([
            'token' => $request->getParam('token'),
            'updated_at >=' => new \DateTime('-15 minutes')])
        )) {
            die('TOKEN_INVALID');
        }

        $email = $authToken->email;

        $authTokenMapper->delete(['email' => $email]);

        $session->create($email, Auth::PASSWORDLESS);

        $logger->addRecord(Info::LOGIN, ['email' => $email]);

        $companyMapper = $database->mapper('\ISTSI\Entities\Company');
        $company = $companyMapper->get($email);
        return $response->withStatus(302)->withHeader(
            'Location',
            '/company/' . (($company->name === null && $company->representative === null) ? 'account' : 'dashboard')
        );
    }

    public function logout(Request $request, Response $response, $args)
    {
        $logger = $this->c->get('logger');
        $session = $this->c->get('session');

        $logger->addRecord(Info::LOGOUT, ['uid' => $session->getUid()]);

        $session->close();

        return $response->withStatus(302)->withHeader('Location', '/');
    }
}
