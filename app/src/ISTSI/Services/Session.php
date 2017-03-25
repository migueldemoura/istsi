<?php
declare(strict_types = 1);

namespace ISTSI\Services;

class Session
{
    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public function close()
    {
        session_unset();
        session_destroy();
    }

    public function create(string $uid, int $authType)
    {
        session_regenerate_id(true);
        $this->setUid($uid);
        $this->setAuthType($authType);
        $this->setToken();
    }

    public function setToken()
    {
        $_SESSION['token'] = bin2hex(random_bytes(64));
    }

    public function isLogged(int $authType)
    {
        return isset($_SESSION['uid']) && $_SESSION['authType'] === $authType;
    }

    public function getToken()
    {
        return isset($_SESSION['token']) ? $_SESSION['token'] : null;
    }

    public function hasValidToken($token)
    {
        return isset($_SESSION['token']) && ($_SESSION['token'] === $token);
    }

    public function getUid()
    {
        return $_SESSION['uid'];
    }

    private function setUid(string $uid)
    {
        $_SESSION['uid'] = $uid;
    }

    public function getAuthType()
    {
        return $_SESSION['authType'];
    }

    private function setAuthType(int $authType)
    {
        $_SESSION['authType'] = $authType;
    }
}
