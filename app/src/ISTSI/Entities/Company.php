<?php
declare(strict_types = 1);

namespace ISTSI\Entities;

use Spot\Entity;
use Spot\EventEmitter;
use Spot\MapperInterface;
use Spot\EntityInterface;
use Valitron\Validator;

class Company extends Entity
{
    protected static $table = 'companies';

    public static function fields()
    {
        return [
            'id'             => ['type' => 'integer', 'autoincrement' => true, 'primary' => true],
            'name'           => ['type' => 'string'],
            'representative' => ['type' => 'string'],
            'email'          => ['type' => 'string', 'unique' => true],
            'phone'          => ['type' => 'string'],
            'created_at'     => ['type' => 'datetime', 'value' => new \DateTime()],
            'updated_at'     => ['type' => 'datetime', 'value' => new \DateTime()]
        ];
    }

    public static function relations(MapperInterface $mapper, EntityInterface $entity)
    {
        return [
            'proposals' => $mapper->hasMany($entity, 'ISTSI\Entities\Proposal', 'id')
        ];
    }

    public static function events(EventEmitter $eventEmitter)
    {
        $eventEmitter->once('afterSave', function (EntityInterface $entity, MapperInterface $mapper) {
            $entity->updated_at = new \DateTime();
            $mapper->save($entity);
        });
        $eventEmitter->once('afterValidate', function (EntityInterface $entity) {
            $validator = new Validator([
                'email' => $entity->email,
            ]);
            $validator->rules([
                'email' => 'email'
            ]);
            return $validator->validate();
        });
    }
}
