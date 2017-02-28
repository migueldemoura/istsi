<?php
declare(strict_types = 1);

namespace ISTSI\Identifiers;

class Notice
{
    const TOKEN_INVALID = ['INFO', 'An invalid token was provided'];
    const CODE_INVALID_EMAIL = ['INFO', 'An auth code was request for an invalid email'];
    const CODE_DUPLICATE = ['INFO', 'An auth code has already been issued during the last minutes'];
    const FILE_UPLOAD = ['STUDENT', 'Uploaded file is invalid'];
    const FENIX_INVALID = ['STUDENT', 'Student isn\'t authenticated'];
    const FENIX_NOT_STUDENT = ['STUDENT', 'Student isn\'t a student'];
    const SUBMISSION_INVALID = ['STUDENT', 'Invalid submission'];
    const SUBMISSION_DUPLICATE = ['STUDENT', 'Duplicate submission'];
    const SUBMISSION_EDIT_EMPTY = ['STUDENT', 'Empty submission edit'];
    const EMAIL_INVALID = ['STUDENT', 'Invalid email address'];
    const PHONE_INVALID = ['STUDENT', 'Invalid phone number'];
    const INFO_MISSING = ['STUDENT', 'Student info is required to perform a submission'];
}
