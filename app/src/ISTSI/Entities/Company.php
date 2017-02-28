<?php
declare(strict_types = 1);

namespace ISTSI\Entities;

use Spot\Entity;
use Spot\EventEmitter;
use Spot\MapperInterface;
use Spot\EntityInterface;

class Company extends Entity
{
    protected static $table = 'companies';

    public static function fields()
    {
        return [
            'id'             => ['type' => 'integer', 'autoincrement' => true, 'primary' => true],
            'name'           => ['type' => 'string'],
            'representative' => ['type' => 'string'],
            'email'          => ['type' => 'string'],
            'created_at'     => ['type' => 'datetime', 'value' => new \DateTime()],
            'updated_at'     => ['type' => 'datetime', 'value' => new \DateTime()]
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
