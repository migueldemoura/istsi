<?php
declare(strict_types = 1);

namespace ISTSI\Identifiers;

class Info
{
    const CODE_NEW = ['INFO', 'Company requested new auth code'];
    const LOGIN = ['INFO', 'User logged in'];
    const LOGOUT = ['INFO', 'User logged out'];
    const ACCOUNT_INFO = ['INFO', 'User edited account information'];
    const SUBMISSION_NEW = ['INFO', 'Student created new submission'];
    const SUBMISSION_EDIT = ['INFO', 'Student edited submission information'];
    const SUBMISSION_DELETE = ['INFO', 'Student deleted submission'];
    const SUBMISSION_VIEW = ['INFO', 'Student viewed submission'];
    const SUBMISSION_DOWNLOAD_ALL = ['INFO', 'Company downloaded submissions'];
    const PROPOSAL_NEW = ['INFO', 'Company created new proposal'];
    const PROPOSAL_EDIT = ['INFO', 'Company edited proposal information'];
    const PROPOSAL_DELETE = ['INFO', 'Company deleted proposal'];
    const PROPOSAL_VIEW = ['INFO', 'User viewed proposal'];
}
