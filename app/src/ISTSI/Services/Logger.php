<?php
declare(strict_types = 1);

namespace ISTSI\Services;

use Monolog\Handler\StreamHandler;
use Monolog\Processor\UidProcessor;

class Logger
{
    protected $logger;

    public function __construct(string $name, string $path, $level)
    {
        $this->logger = new \Monolog\Logger($name);
        $this->logger->pushHandler(new StreamHandler($path, $level));
        $this->logger->pushProcessor(new UidProcessor());
    }

    public function addRecord($error, $context = [])
    {
        $path = preg_replace('/([?&])csrf_token=[^&]+(&|$)/', '$1', $_SERVER['REQUEST_URI']);

        $url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' .
               $_SERVER['HTTP_HOST'] . $path;

        $this->logger->{'add' . ucwords(strtolower($error[0]))}($error[1] . ' ' . $url, $context);
    }
}
