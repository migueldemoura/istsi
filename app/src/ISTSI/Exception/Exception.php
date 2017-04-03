<?php
declare(strict_types = 1);

namespace ISTSI\Exception;

class Exception extends \Exception
{
    protected $message;
    protected $code;

    public function __construct($data)
    {
        $this->message = $data[0];
        $this->code = $data[1];
    }
}
