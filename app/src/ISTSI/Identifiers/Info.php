<?php
declare(strict_types = 1);

namespace ISTSI\Identifiers;

class Info
{
    const LOGIN = ['INFO', 'User logged in'];
    const LOGOUT = ['INFO', 'User logged out'];
    const ACCOUNT_INFO = ['INFO', 'User edited account information'];
    const SUBMISSION_NEW = ['INFO', 'User created new submission'];
    const SUBMISSION_EDIT = ['INFO', 'User edited submission information'];
    const SUBMISSION_DELETE = ['INFO', 'User deleted submission'];
    const SUBMISSION_VIEW = ['INFO', 'User viewed submission'];
}
