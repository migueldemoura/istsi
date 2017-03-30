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
            'id'           => ['type' => 'integer', 'autoincrement' => true, 'primary' => true],
            'company_id'   => ['type' => 'integer'],
            'description'  => ['type' => 'string'],
            'project'      => ['type' => 'text'],
            'requirements' => ['type' => 'text'],
            'salary'       => ['type' => 'string'],
            'observations' => ['type' => 'text'],
            'duration'     => ['type' => 'string'],
            'location'     => ['type' => 'string'],
            'vacancies'    => ['type' => 'integer'],
            'courses'      => ['type' => 'array'],
            'created_at'   => ['type' => 'datetime', 'value' => new \DateTime()],
            'updated_at'   => ['type' => 'datetime', 'value' => new \DateTime()]
        ];
    }

    public static function relations(MapperInterface $mapper, EntityInterface $entity)
    {
        return [
            'company' => $mapper->belongsTo($entity, 'ISTSI\Entities\Company', 'company_id'),
            'submissions' => $mapper->hasMany($entity, 'ISTSI\Entities\Submission', 'id'),
            'courses' => $mapper->hasManyThrough(
                $entity,
                'ISTSI\Entities\Course',
                'ISTSI\Entities\ProposalCourse',
                'course_id',
                'proposal_id'
            )
        ];
    }

    public static function events(EventEmitter $eventEmitter)
    {
        $eventEmitter->once('beforeSave', function (EntityInterface $entity, MapperInterface $mapper) {
            $entity->updated_at = new \DateTime();
        });
    }
}
