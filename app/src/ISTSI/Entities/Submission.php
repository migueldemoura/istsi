<?php
declare(strict_types = 1);

namespace ISTSI\Entities;

use Spot\Entity;
use Spot\EventEmitter;
use Spot\MapperInterface;
use Spot\EntityInterface;

class Submission extends Entity
{
    protected static $table = 'submissions';

    public static function fields()
    {
        return [
            'id'           => ['type' => 'integer', 'autoincrement' => true, 'primary' => true],
            'student_id'   => ['type' => 'string', 'unique' => 'student_idProposal_id', 'required' => true],
            'proposal_id'  => ['type' => 'integer', 'unique' => 'student_idProposal_id', 'required' => true],
            'observations' => ['type' => 'text'],
            'created_at'   => ['type' => 'datetime', 'value' => new \DateTime()],
            'updated_at'   => ['type' => 'datetime']
        ];
    }

    public static function relations(MapperInterface $mapper, EntityInterface $entity)
    {
        return [
            'proposal' => $mapper->belongsTo($entity, 'ISTSI\Entities\Proposal', 'proposal_id'),
            'student' => $mapper->belongsTo($entity, 'ISTSI\Entities\Student', 'student_id')
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
