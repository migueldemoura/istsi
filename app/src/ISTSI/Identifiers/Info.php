<?php
declare(strict_types = 1);

namespace ISTSI\Identifiers;

class Info
{
    const CODE_NEW = ['INFO', 'Company requested new auth code'];
    const LOGIN = ['INFO', 'Student logged in'];
    const LOGOUT = ['INFO', 'Student logged out'];
    const ACCOUNT_INFO = ['INFO', 'Student edited account information'];
    const SUBMISSION_NEW = ['INFO', 'Student created new submission'];
    const SUBMISSION_EDIT = ['INFO', 'Student edited submission information'];
    const SUBMISSION_DELETE = ['INFO', 'Student deleted submission'];
    const SUBMISSION_VIEW = ['INFO', 'Student viewed submission'];
}
