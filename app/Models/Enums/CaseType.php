<?php

namespace App\Models\Enums;

enum CaseType: int
{
    case CASE_TYPE_VERDICT = 1;
    case CASE_TYPE_BLACKLIST = 2;
    case CASE_TYPE_SANCTION = 3;

    public function label(): string
    {
        return match ($this) {
            self::CASE_TYPE_VERDICT => 'Verdict',
            self::CASE_TYPE_BLACKLIST => 'Blacklist',
            self::CASE_TYPE_SANCTION => 'Sanction',
        };
    }
}
