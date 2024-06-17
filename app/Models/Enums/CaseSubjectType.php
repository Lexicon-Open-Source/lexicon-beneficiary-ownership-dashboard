<?php

namespace App\Models\Enums;

enum CaseSubjectType: int
{
    case SUBJECT_TYPE_INDIVIDUAL = 1;
    case SUBJECT_TYPE_COMPANY = 2;
    case SUBJECT_TYPE_ORGANIZATION = 3;

    public function label(): string
    {
        return match ($this) {
            self::SUBJECT_TYPE_INDIVIDUAL => 'Individual',
            self::SUBJECT_TYPE_COMPANY => 'Company',
            self::SUBJECT_TYPE_ORGANIZATION => 'Organization',
        };
    }
}
