<?php

namespace App\Models;

use App\Models\Enums\CaseStatus;
use App\Models\Enums\CaseSubjectType;
use App\Models\Enums\CaseType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorruptionCase extends Model
{
    use HasFactory;

    protected $table = 'cases';

    protected $guarded = [];

    protected $casts = ['id' => 'string', 'status' => CaseStatus::class, 'subject_type' => CaseSubjectType::class, 'case_type' => CaseType::class, 'created_at' => 'datetime', 'updated_at' => 'datetime', 'case_date' => 'datetime', 'punishment_start' => 'datetime', 'punishment_end' => 'datetime'];

    protected $keyType = 'string';

    const CASE_STATUS = [
        CaseStatus::CASE_STATUS_DELETED->value => 'Deleted',
        CaseStatus::CASE_STATUS_VERIFIED->value => 'Verified',
        CaseStatus::CASE_STATUS_DRAFT->value => 'Draft',

    ];

    const SUBJECT_TYPE = [
        CaseSubjectType::SUBJECT_TYPE_INDIVIDUAL->value => 'Individual',
        CaseSubjectType::SUBJECT_TYPE_COMPANY->value => 'Company',
        CaseSubjectType::SUBJECT_TYPE_ORGANIZATION->value => 'Organization',
    ];

    const CASE_TYPE = [
        CaseType::CASE_TYPE_VERDICT->value => 'Verdict',
        CaseType::CASE_TYPE_BLACKLIST->value => 'Blacklist',
        CaseType::CASE_TYPE_SANCTION->value => 'Sanction',
    ];
}
