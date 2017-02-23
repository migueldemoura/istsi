<?php
declare(strict_types = 1);

namespace ISTSI\Services;

class Session
{
    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_set_cookie_params(null, '/', $_SERVER['SERVER_NAME'], isset($_SERVER['HTTPS']), true);
            session_start();
        }
    }

    public function close()
    {
        session_unset();
        session_destroy();
    }

    public function create(string $uid)
    {
        session_regenerate_id(true);
        $this->setUid($uid);
        $this->setToken();
    }

    private function setToken()
    {
        $_SESSION['token'] = substr(bin2hex(openssl_random_pseudo_bytes(128)), 0, 128);
    }

    public function isLogged()
    {
        return isset($_SESSION['uid']);
    }

    public function getToken()
    {
        return $_SESSION['token'];
    }

    public function hasValidToken($token)
    {
        return ($_SESSION['token'] === $token);
    }

    public function getUid()
    {
        return $_SESSION['uid'];
    }

    private function setUid(string $uid)
    {
        $_SESSION['uid'] = $uid;
    }
}
