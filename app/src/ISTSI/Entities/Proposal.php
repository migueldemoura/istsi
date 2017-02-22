<?php
declare(strict_types = 1);

namespace ISTSI\Entities;

use Spot\Entity;
use Spot\EventEmitter;
use Spot\MapperInterface;
use Spot\EntityInterface;

class Proposal extends Entity
{
    protected static $table = 'proposals';

    public static function fields()
    {
        return [
            'id'           => ['type' => 'string', 'primary' => true],
            'company'      => ['type' => 'string'],
            'description'  => ['type' => 'string'],
            'courses'      => ['type' => 'array'],
            'years'        => ['type' => 'array'],
            'created_at'   => ['type' => 'datetime', 'value' => new \DateTime()],
            'updated_at'   => ['type' => 'datetime', 'value' => new \DateTime()]
        ];
    }

    public static function relations(MapperInterface $mapper, EntityInterface $entity)
    {
        return [
            'submission' => $mapper->hasMany($entity, 'ISTSI\Entities\Submission', 'proposal_id')
                                   ->order(['created_at' => 'ASC']),
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
