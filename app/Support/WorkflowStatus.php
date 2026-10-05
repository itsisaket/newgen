<?php

namespace App\Support;

/**
 * Blueprint section 16.1 / Appendix D. The single set of statuses shared by
 * every F01-F15 module that has a workflow, so WorkflowService has one
 * vocabulary to work with instead of each module inventing its own.
 */
final class WorkflowStatus
{
    public const DRAFT = 'draft';
    public const SUBMITTED = 'submitted';
    public const VERIFIED = 'verified';
    public const APPROVED = 'approved';
    public const REVISION_REQUESTED = 'revision_requested';

    public const ALL = [
        self::DRAFT,
        self::SUBMITTED,
        self::VERIFIED,
        self::APPROVED,
        self::REVISION_REQUESTED,
    ];
}
