<?php
declare(strict_types = 1);

namespace ISTSI\Controllers\Auth;

use ISTSI\Exception\Exception;
use ISTSI\Identifiers\Auth;
use ISTSI\Identifiers\Error;
use ISTSI\Identifiers\Info;
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

        return $response->withStatus(302)->withHeader(
            'Location',
            $fenix->getAuthUrl($request->getQueryParam('csrf_token'))
        );
    }

    public function login(Request $request, Response $response, $args)
    {
        $database = $this->c->get('database');
        $fenix = $this->c->get('fenix');
        $logger = $this->c->get('logger');
        $session = $this->c->get('session');

        $fenix->getAccessTokenFromCode($request->getQueryParam('code'));

        $uid = $fenix->getUid();
        $name = $fenix->getName();
        $email = $fenix->getEmail();
        $course = $fenix->getCourse();
        if ($course === null) {
            //TODO
            die('FENIX_NOT_STUDENT');
        }
        $year = $fenix->getYear();

        $studentMapper = $database->mapper('\ISTSI\Database\Entities\Student');

        if ($student = $studentMapper->get($uid)) {
            $student->name = $name;
            // Don't overwrite the student's email if he has already approved it
            if (in_array(null, [$student->email, $student->phone])) {
                $student->email = $email;
            }
            $student->course = $course;
            $student->year = $year;
            if ($studentMapper->update($student) === false) {
                throw new Exception(Error::DB_OP);
            }
        } else {
            if ($studentMapper->create([
                'id'     => $uid,
                'name'   => $name,
                'email'  => $email,
                'course' => $course,
                'year'   => $year
            ]) === false) {
                throw new Exception(Error::DB_OP);
            }
        }

        $session->create($uid, Auth::FENIX);

        $logger->addRecord(Info::LOGIN, ['uid' => $uid]);

        return $response->withStatus(302)->withHeader(
            'Location',
            '/student/' . (($studentMapper->get($uid)->phone === null) ? 'account' : 'dashboard')
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
