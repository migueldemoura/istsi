<?php
declare(strict_types = 1);

namespace ISTSI\Entities;

use Spot\Entity;
use Spot\EventEmitter;
use Spot\MapperInterface;
use Spot\EntityInterface;
use Valitron\Validator;

class Proposal extends Entity
{
    protected static $table = 'proposals';

    public static function fields()
    {
        return [
            'id'           => ['type' => 'integer', 'autoincrement' => true, 'primary' => true],
            'company_id'   => ['type' => 'integer', 'required' => true],
            'description'  => ['type' => 'string', 'required' => true],
            'project'      => ['type' => 'text', 'required' => true],
            'requirements' => ['type' => 'text', 'required' => true],
            'salary'       => ['type' => 'string', 'required' => true],
            'observations' => ['type' => 'text'],
            'duration'     => ['type' => 'string', 'required' => true],
            'location'     => ['type' => 'string', 'required' => true],
            'vacancies'    => ['type' => 'integer', 'required' => true],
            'created_at'   => ['type' => 'datetime', 'value' => new \DateTime()],
            'updated_at'   => ['type' => 'datetime']
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
        $eventEmitter->once('beforeSave', function (EntityInterface $entity) {
            $validator = new Validator([
                'vacancies' => $entity->vacancies
            ]);
            $validator->rules([
                'min' => [['vacancies', 1]]
            ]);
            return $validator->validate();
        });
        $eventEmitter->once('afterSave', function (EntityInterface $entity, MapperInterface $mapper) {
            $entity->updated_at = new \DateTime();
            $mapper->save($entity);
        });
    }
}
