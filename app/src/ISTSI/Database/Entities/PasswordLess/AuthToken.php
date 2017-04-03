<?php
declare(strict_types = 1);

namespace ISTSI\Database\Entities\PasswordLess;

use Spot\Entity;
use Spot\EntityInterface;
use Spot\EventEmitter;
use Spot\MapperInterface;

class AuthToken extends Entity
{
    protected static $table = 'passwordless_authtokens';

    public static function fields()
    {
        return [
            'email'        => ['type' => 'string', 'primary' => true],
            'token'        => ['type' => 'string', 'unique' => true],
            'created_at'   => ['type' => 'datetime', 'value' => new \DateTime()],
            'updated_at'   => ['type' => 'datetime', 'value' => new \DateTime()]
        ];
    }

    public static function events(EventEmitter $eventEmitter)
    {
        $eventEmitter->once('afterSave', function (EntityInterface $entity, MapperInterface $mapper) {
            $entity->updated_at = new \DateTime();
            $mapper->save($entity);
        });
    }
}
