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
        $filteredToken = preg_replace('/([?&])csrf_token=[^&]+(&|$)/', '$1', $_SERVER['REQUEST_URI']);

        $url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}{$filteredToken}";

        switch ($error[0]) {
            case 'INFO':
                $this->logger->addInfo($error[1] . ' - ' . $error[2] . ' ' . $url, $context);
                break;
            case 'USER':
                $this->logger->addNotice($error[1] . ' - ' . $error[2] . ' ' . $url, $context);
                break;
            case 'SERVER':
                $this->logger->addError($error[1] . ' - ' . $error[2] . ' ' . $url, $context);
                break;
            default:
        }
    }
}
