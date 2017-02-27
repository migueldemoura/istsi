<?php
declare(strict_types = 1);

namespace ISTSI\Entities\PasswordLess;

use Spot\Entity;
use Spot\EventEmitter;
use Spot\MapperInterface;
use Spot\EntityInterface;

class InitToken extends Entity
{
    protected static $table = 'passwordless_inittokens';

    public static function fields()
    {
        return [
            'email'        => ['type' => 'string', 'primary' => true],
            'tokens'       => ['type' => 'array'],
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
