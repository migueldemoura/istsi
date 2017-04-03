<?php
declare(strict_types = 1);

namespace ISTSI\Database\Entities;

use Spot\Entity;
use Spot\EntityInterface;
use Spot\MapperInterface;

class ProposalCourse extends Entity
{
    protected static $table = 'proposalscourses';

    public static function fields()
    {
        return [
            'id'          => ['type' => 'integer', 'autoincrement' => true, 'primary' => true],
            'proposal_id' => ['type' => 'integer'],
            'course_id'   => ['type' => 'integer'],
            'created_at'  => ['type' => 'datetime', 'value' => new \DateTime()]
        ];
    }

    public static function relations(MapperInterface $mapper, EntityInterface $entity)
    {
        return [
            'proposal' => $mapper->belongsTo($entity, 'ISTSI\Database\Entities\Proposal', 'proposal_id'),
            'course' => $mapper->belongsTo($entity, 'ISTSI\Database\Entities\Course', 'course_id')
        ];
    }
}
