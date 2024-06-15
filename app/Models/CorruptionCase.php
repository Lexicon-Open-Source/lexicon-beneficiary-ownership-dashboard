<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorruptionCase extends Model
{
    use HasFactory;

    protected $table = 'cases';

    protected $guarded = [];
    protected $casts = ['id' => 'string'];
    protected $keyType = 'string';
}
