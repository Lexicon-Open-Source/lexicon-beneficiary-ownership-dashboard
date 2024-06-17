<?php

namespace App\Models;

use App\Models\Enums\CaseSubjectType;
use App\Models\Enums\CaseType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DraftCase extends Model
{
    use HasFactory;

    protected $casts =  ['id' => 'string', 'subject_type' => CaseSubjectType::class, 'case_type' => CaseType::class, 'created_at' => 'datetime', 'updated_at' => 'datetime', 'case_date' => 'datetime', 'punishment_start' => 'datetime', 'punishment_end' => 'datetime'];
    protected $keyType = 'string';



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
