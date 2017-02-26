<?php
declare(strict_types = 1);

namespace ISTSI\Identifiers;

class Exception
{
    const DB_OP = ['SERVER', 'Unable to execute the operation on the database'];
    const DATE_FORMAT = ['SERVER', 'Invalid registration date format'];
    const FILE_UPLOAD = ['USER', 'Uploaded file is invalid'];
    const FILE_DELETE = ['USER', 'Cannot delete file'];
    const FENIX_INVALID = ['USER', 'User isn\'t authenticated'];
    const FENIX_NOT_STUDENT = ['USER', 'User isn\'t a student'];
    const SUBMISSION_INVALID = ['USER', 'Invalid submission'];
    const SUBMISSION_DUPLICATE = ['USER', 'Duplicate submission'];
    const SUBMISSION_EDIT_EMPTY = ['USER', 'Empty submission edit'];
    const EMAIL_INVALID = ['USER', 'Invalid email address'];
    const PHONE_INVALID = ['USER', 'Invalid phone number'];
    const INFO_MISSING = ['USER', 'User info is required to perform a submission'];
}
