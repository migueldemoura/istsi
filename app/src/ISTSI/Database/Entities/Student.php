<?php
declare(strict_types = 1);

namespace ISTSI\Database\Entities;

use Spot\Entity;
use Spot\EventEmitter;
use Spot\MapperInterface;
use Spot\EntityInterface;
use Valitron\Validator;

class Student extends Entity
{
    protected static $table = 'students';

    public static function fields()
    {
        return [
            'id'           => ['type' => 'string', 'primary' => true],
            'name'         => ['type' => 'string', 'required' => true],
            'email'        => ['type' => 'string', 'required' => true],
            'phone'        => ['type' => 'string'],
            'course'       => ['type' => 'string', 'required' => true],
            'year'         => ['type' => 'integer', 'required' => true],
            'created_at'   => ['type' => 'datetime', 'value' => new \DateTime()],
            'updated_at'   => ['type' => 'datetime']
        ];
    }

    public static function relations(MapperInterface $mapper, EntityInterface $entity)
    {
        return [
            'submissions' => $mapper->hasMany($entity, 'ISTSI\Database\Entities\Submission', 'id')
        ];
    }

    public static function events(EventEmitter $eventEmitter)
    {
        $eventEmitter->once('beforeSave', function (EntityInterface $entity) {
            $validator = new Validator([
                'email' => $entity->email,
                'phone' => $entity->phone
            ]);
            $rules = ['email' => 'email'];
            if ($entity->updated_at !== null) {
                $rules = array_merge($rules, ['required' => 'phone']);
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
