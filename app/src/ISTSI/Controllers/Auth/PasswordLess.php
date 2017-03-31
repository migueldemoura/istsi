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

        $email = $request->getParsedBodyParam('email');

        $authTokenMapper = $database->mapper('\ISTSI\Entities\PasswordLess\AuthToken');
        $companyMapper = $database->mapper('\ISTSI\Entities\Company');

        // Check if email belongs to a company
        if ($company = $companyMapper->first(['email' => $email])) {
            // Check if a token exits
            if ($authTokenMapper->get($email)) {
                // Check if token has expired
                if ($authToken = $authTokenMapper->first(
                    ['email' => $email, 'updated_at <' => new \DateTime('-30 minutes')]
                )) {
                    // Update existing token
                    $authToken->token = bin2hex(random_bytes(64));
                    $authTokenMapper->update($authToken);
                } else {
                    $data = [
                        'status' => 'fail',
                        'data'   => 'duplicate'
                    ];
                    return $response->withJson($data);
                }
            } else {
                // Create new token
                if (!($authToken = $authTokenMapper->create(
                    ['email' => $email, 'token' => bin2hex(random_bytes(64))]
                ))) {
                    throw new \Exception(Error::DB_OP);
                }
            }
        } else {
            $data = [
                'status' => 'fail',
                'data'   => 'email'
            ];
            return $response->withJson($data);
        }

        // Send mail
        $loginUrl = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' .
                    $_SERVER['HTTP_HOST'] . '/auth/passwordless/login?token=' . $authToken->token;
        $mailer->sendMail(
            $email,
            'ISTSI Login Link',
            '<p>O link abaixo pode ser utilizado somente uma vez.</p>
             <a href="' . $loginUrl .'">Login</a>'
        );

        $logger->addRecord(IdentifiersInfo::CODE_NEW, ['email' => $email]);

        $data = [
            'status' => 'success',
            'data'   => null
        ];
        return $response->withJson($data);
    }

    public function login(Request $request, Response $response, $args)
    {
        $database = $this->c->get('database');
        $logger = $this->c->get('logger');
        $session = $this->c->get('session');

        $authTokenMapper = $database->mapper('\ISTSI\Entities\PasswordLess\AuthToken');

        if (!($authToken = $authTokenMapper->first([
            'token' => $request->getParam('token'),
            'updated_at >=' => new \DateTime('-30 minutes')])
        )) {
            //TODO
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
