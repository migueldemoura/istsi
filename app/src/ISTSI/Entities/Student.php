<?php
declare(strict_types = 1);

namespace ISTSI\Entities;

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
            'name'         => ['type' => 'string'],
            'email'        => ['type' => 'string'],
            'phone'        => ['type' => 'string'],
            'course'       => ['type' => 'string'],
            'year'         => ['type' => 'integer'],
            'created_at'   => ['type' => 'datetime', 'value' => new \DateTime()],
            'updated_at'   => ['type' => 'datetime', 'value' => new \DateTime()]
        ];
    }

    public static function relations(MapperInterface $mapper, EntityInterface $entity)
    {
        return [
            'submissions' => $mapper->hasMany($entity, 'ISTSI\Entities\Submission', 'id')
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
                'courses' => $entity->courses,
                'years' => $entity->years,
            ]);
            $validator->rules([
                'email' => 'email'
                //TODO: VALIDATE COURSE AND YEAR
            ]);
            return $validator->validate();
        });
    }
}
