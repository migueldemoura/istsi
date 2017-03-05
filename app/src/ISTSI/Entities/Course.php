<?php
declare(strict_types = 1);

namespace ISTSI\Entities;

use Spot\Entity;

class Course extends Entity
{
    protected static $table = 'courses';

    public static function fields()
    {
        return [
            'id'         => ['type' => 'integer', 'autoincrement' => true, 'primary' => true],
            'acronym'    => ['type' => 'string', 'unique' => true],
            'name'       => ['type' => 'text'],
            'created_at' => ['type' => 'datetime', 'value' => new \DateTime()]
        ];
    }
}
