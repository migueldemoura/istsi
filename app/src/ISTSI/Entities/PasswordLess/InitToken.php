<?php
declare(strict_types = 1);

namespace ISTSI\Entities\PasswordLess;

use Spot\Entity;

class InitToken extends Entity
{
    protected static $table = 'passwordless_inittokens';

    public static function fields()
    {
        return [
            'email'        => ['type' => 'string', 'primary' => true],
            'token'        => ['type' => 'string', 'unique' => true],
        ];
    }
}
