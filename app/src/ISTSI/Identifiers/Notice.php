<?php
declare(strict_types = 1);

namespace ISTSI\Identifiers;

class Notice
{
    const TOKEN_INVALID = ['INFO', 'An invalid token was provided'];
    const CODE_INVALID_EMAIL = ['INFO', 'An auth code was request for an invalid email'];
    const CODE_DUPLICATE = ['INFO', 'An auth code has already been issued during the last minutes'];
    const FILE_UPLOAD = ['USER', 'Uploaded file is invalid'];
    const FENIX_INVALID = ['USER', 'Student isn\'t authenticated'];
    const FENIX_NOT_STUDENT = ['USER', 'Student isn\'t a student'];
    const SUBMISSION_INVALID = ['USER', 'Invalid submission'];
    const SUBMISSION_DUPLICATE = ['USER', 'Duplicate submission'];
    const PROPOSAL_INVALID = ['USER', 'Invalid proposal'];
    const PROPOSAL_DUPLICATE = ['USER', 'Duplicate proposal'];
    const INFO_MISSING = ['USER', 'User info is required to perform a submission'];
}
