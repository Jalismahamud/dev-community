<?php

namespace App\Enums;

enum ReportStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Dismissed = 'dismissed';
    case Removed = 'removed';
}