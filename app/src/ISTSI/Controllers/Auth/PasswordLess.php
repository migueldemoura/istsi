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

    public function generate(Request $request, Response $response, $args)
    {
        $database = $this->c->get('database');
        $logger = $this->c->get('logger');
        $mailer = $this->c->get('mailer');

        $email = $request->getParam('email');

        $authTokenMapper = $database->mapper('\ISTSI\Entities\PasswordLess\AuthToken');

        if ($authTokenMapper->get($email)) {
            if ($authToken = $authTokenMapper->first(
                ['email' => $email, 'updated_at <' => new \DateTime('-15 minutes')]
            )) {
                // Auth token has expired, create a new one
                $authToken->token = bin2hex(random_bytes(64));
                $authTokenMapper->update($authToken);

                // Send mail with new auth token
                $companyMapper = $database->mapper('\ISTSI\Entities\Company');
                $company = $companyMapper->get($email);

                $loginUrl = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' .
                    $_SERVER['HTTP_HOST'] . '/auth/passwordless/login?token=' . $authToken->token;
                $mailer->sendMail($company->email, 'ISTSI Login Link', '<a href="' . $loginUrl .'">Login</a>');

                $logger->addRecord(IdentifiersInfo::CODE_NEW, ['email' => $email]);
            } else {
                die('CODE_DUPLICATE');
            }
        } else {
            $companyMapper = $database->mapper('\ISTSI\Entities\Company');

            if ($company = $companyMapper->first(['email' => $email])) {
                // No auth token found, create a new one
                if (!($authToken = $authTokenMapper->create([
                    'email' => $email,
                    'token' => bin2hex(random_bytes(64))
                ]))) {
                    throw new \Exception(Error::DB_OP);
                }

                // Send mail
                $loginUrl = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' .
                    $_SERVER['HTTP_HOST'] . '/auth/passwordless/login?token=' . $authToken->token;
                $mailer->sendMail(
                    $company->email,
                    'ISTSI Login Link',
                    '<p>O link abaixo é apenas pode ser utilizado uma vez.</p></p>
                     <a href="' . $loginUrl .'">Login</a>'
                );
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

        return $response->withStatus(302)->withHeader('Location', '/company/dashboard');
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
