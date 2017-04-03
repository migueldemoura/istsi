<?php
declare(strict_types = 1);

namespace ISTSI\Database\Migrations;

use Psr\Container\ContainerInterface;

class Migrations
{
    protected $c;
    private $entityBasePath = '\ISTSI\Database\Entities\\';
    private $entityNames = [
        'PasswordLess\AuthToken',
        'Company',
        'Course',
        'Proposal',
        'ProposalCourse',
        'Student',
        'Submission',
    ];

    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;
    }

    public function migrate()
    {
        $database = $this->c->get('database');

        foreach ($this->entityNames as $entityName) {
            $database->mapper($this->entityBasePath . $entityName)->migrate();
        }
    }
}
