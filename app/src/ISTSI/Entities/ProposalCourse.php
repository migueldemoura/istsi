<?php
declare(strict_types = 1);

namespace ISTSI\Entities;

use Spot\Entity;

class ProposalCourse extends Entity
{
    protected static $table = 'proposalscourses';

    public static function fields()
    {
        return [
            'id'          => ['type' => 'integer', 'autoincrement' => true, 'primary' => true],
            'proposal_id' => ['type' => 'integer'],
            'company_id'  => ['type' => 'integer'],
            'created_at'  => ['type' => 'datetime', 'value' => new \DateTime()]
        ];
    }
}
