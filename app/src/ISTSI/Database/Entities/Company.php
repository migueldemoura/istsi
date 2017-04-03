<?php
declare(strict_types = 1);

namespace ISTSI\Database\Entities;

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
            'email'          => ['type' => 'string', 'unique' => true, 'required' => true],
            'phone'          => ['type' => 'string'],
            'created_at'     => ['type' => 'datetime', 'value' => new \DateTime()],
            'updated_at'     => ['type' => 'datetime']
        ];
    }

    public static function relations(MapperInterface $mapper, EntityInterface $entity)
    {
        return [
            'proposals' => $mapper->hasMany($entity, 'ISTSI\Database\Entities\Proposal', 'id')
        ];
    }

    public static function events(EventEmitter $eventEmitter)
    {
        $eventEmitter->once('beforeSave', function (EntityInterface $entity) {
            $validator = new Validator([
                'email' => $entity->email,
            ]);
            $rules = ['email' => 'email'];
            if ($entity->updated_at !== null) {
                $rules = array_merge($rules, ['required' => ['name', 'representative', 'phone']]);
            }
            $validator->rules($rules);
            return $validator->validate();
        });
        $eventEmitter->once('afterSave', function (EntityInterface $entity, MapperInterface $mapper) {
            $entity->updated_at = new \DateTime();
            $mapper->save($entity);
        });
    }
}
