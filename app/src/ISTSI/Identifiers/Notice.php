<?php
declare(strict_types = 1);

namespace ISTSI\Identifiers;

class Notice
{
    const FILE_UPLOAD = ['USER', 'Uploaded file is invalid'];
    const FENIX_INVALID = ['USER', 'User isn\'t authenticated'];
    const FENIX_NOT_STUDENT = ['USER', 'User isn\'t a student'];
    const SUBMISSION_INVALID = ['USER', 'Invalid submission'];
    const SUBMISSION_DUPLICATE = ['USER', 'Duplicate submission'];
    const SUBMISSION_EDIT_EMPTY = ['USER', 'Empty submission edit'];
    const EMAIL_INVALID = ['USER', 'Invalid email address'];
    const PHONE_INVALID = ['USER', 'Invalid phone number'];
    const INFO_MISSING = ['USER', 'User info is required to perform a submission'];
}
