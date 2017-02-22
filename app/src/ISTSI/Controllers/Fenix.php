<?php
declare(strict_types = 1);

namespace ISTSI\Controllers;

use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class Fenix
{
    protected $c;

    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;
    }

    public function login(Request $request, Response $response, $args)
    {
        $fenix = $this->c->get('fenix');
        return $response->withStatus(302)->withHeader('Location', $fenix->getAuthUrl());
    }

    public function callback(Request $request, Response $response, $args)
    {
        $database = $this->c->get('database');
        $fenix = $this->c->get('fenix');
        $session = $this->c->get('session');

        if ($request->getQueryParam('error', $default = null) !== null) {
            //TODO:throw new IException(E_FENIX_ACCESS_DENIED);
            die('E_FENIX_ACCESS_DENIED');
        } elseif ($request->getQueryParam('code', $default = null) === null) {
            //TODO:throw new IException(E_URL_INVALID);
            die('E_URL_INVALID');
        }
        $authCode = $request->getQueryParam('code');

        //TODO:try {
            $fenix->getAccessTokenFromCode($authCode);
        //} catch (\Exception $exception) {
            //TODO:throw new IException(E_URL_INVALID);
            //die('E_URL_INVALID');
        //}

        $uid = $fenix->getUid();
        $name = $fenix->getName();
        $email = $fenix->getEmail();
        $course = $fenix->getCourse();
        if ($course === null) {
            //TODO: throw new IException(E_FENIX_NOT_STUDENT);
            die('E_FENIX_NOT_STUDENT');
        }
        $year = $fenix->getYear($course);

        // User Update
        $userMapper = $database->mapper('\ISTSI\Entities\User');

        if ($user = $userMapper->get($uid)) {
            $user->name = $name;
            $user->email = $email;
            $user->course = $course;
            $user->year = $year;
            $userMapper->update($user);
        } else {
            $result = $userMapper->create([
                'id'     => $uid,
                'name'   => $name,
                'email'  => $email,
                'course' => $course,
                'year'   => $year
            ]);
            if (!$result) {
                //TODO:$logger->addRecord(E_DB_OP);
                die('E_DB_OP');
            }
        }

        $session->create($uid);
        //TODO:$logger->addRecord(I_LOGIN, ['$uid' => $_SESSION['$uid']]);

        // Redirect to appropriate page
        $user = $userMapper->get($uid);

        return $response->withStatus(302)->withHeader(
            'Location',
            '/user/' . (($user->phone === null) ? 'account' : 'dashboard')
        );
    }

    public function logout(Request $request, Response $response, $args)
    {
        $session = $this->c->get('session');

        if ($session->isLogged() && $session->isValidToken($request->getQueryParam('token'))) {
            //TODO:$logger->addRecord(I_LOGOUT, ['$uid' => $_SESSION['$uid']]);
            $session->close();
        } else {
            //TODO:$logger->addRecord(E_FENIX_INVALID);
        }

        return $response->withStatus(302)->withHeader('Location', '/');
    }
}
