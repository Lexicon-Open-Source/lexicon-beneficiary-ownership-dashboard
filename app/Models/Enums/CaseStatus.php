<?php

namespace App\Models\Enums;

use Filament\Support\Contracts\HasLabel;

enum CaseStatus: int implements HasLabel
{
    case CASE_STATUS_DELETED = 0;
    case CASE_STATUS_VERIFIED = 1;
    case CASE_STATUS_DRAFT = 2;


    public function getlabel(): ?string
    {
        return match ($this) {
            self::CASE_STATUS_DELETED => 'Deleted',
            self::CASE_STATUS_VERIFIED => 'Verified',
            self::CASE_STATUS_DRAFT => 'Draft',
        };
    }
}
