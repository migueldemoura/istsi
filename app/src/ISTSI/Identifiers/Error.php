<?php
declare(strict_types = 1);

namespace ISTSI\Identifiers;

class Error
{
    const DB_OP = ['APP', 'Unable to execute the operation on the database'];
    const DIR_CREATE = ['APP', 'Unable to create directory'];
    const FILE_DELETE = ['APP', 'Cannot delete file'];
}
