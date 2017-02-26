<?php
declare(strict_types = 1);

namespace ISTSI\Controllers;

use ISTSI\Identifiers\Exception;
use ISTSI\Identifiers\Information;
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

    public function connect(Request $request, Response $response, $args)
    {
        $fenix = $this->c->get('fenix');
        return $response->withStatus(302)->withHeader('Location', $fenix->getAuthUrl());
    }

    public function login(Request $request, Response $response, $args)
    {
        $database = $this->c->get('database');
        $fenix = $this->c->get('fenix');
        $logger = $this->c->get('logger');
        $session = $this->c->get('session');

        //TODO: THIS MAY TROW AN EXCEPTION
        $fenix->getAccessTokenFromCode($request->getQueryParam('code'));

        $uid = $fenix->getUid();
        $name = $fenix->getName();
        $email = $fenix->getEmail();
        $course = $fenix->getCourse();
        if ($course === null) {
            //TODO: throw new IException(E_FENIX_NOT_STUDENT);
            die('E_FENIX_NOT_STUDENT');
        }
        $year = $fenix->getYear($course);

        $userMapper = $database->mapper('\ISTSI\Entities\User');

        if ($user = $userMapper->get($uid)) {
            $user->name = $name;
            $user->email = $email;
            $user->course = $course;
            $user->year = $year;
            $userMapper->update($user);
        } else {
            if (!$userMapper->create([
                'id'     => $uid,
                'name'   => $name,
                'email'  => $email,
                'course' => $course,
                'year'   => $year
            ])) {
                throw new \Exception(Exception::DB_OP);
            }
        }

        $session->create($uid);

        $logger->addRecord(Information::LOGIN, ['uid' => $uid]);

        return $response->withStatus(302)->withHeader(
            'Location',
            '/user/' . (($userMapper->get($uid)->phone === null) ? 'account' : 'dashboard')
        );
    }

    public function logout(Request $request, Response $response, $args)
    {
        $logger = $this->c->get('logger');
        $session = $this->c->get('session');

        $logger->addRecord(Information::LOGOUT, ['uid' => $session->getUid()]);

        $session->close();

        return $response->withStatus(302)->withHeader('Location', '/');
    }
}
