<?php

namespace App\Models\Enums;

enum CaseStatus: int
{
    case CASE_STATUS_DELETED = 0;
    case CASE_STATUS_VERIFIED = 1;
    case CASE_STATUS_DRAFT = 2;


    public function label(): string
    {
        return match ($this) {
            self::CASE_STATUS_DELETED => 'Deleted',
            self::CASE_STATUS_VERIFIED => 'Verified',
            self::CASE_STATUS_DRAFT => 'Draft',
        };
    }
}
