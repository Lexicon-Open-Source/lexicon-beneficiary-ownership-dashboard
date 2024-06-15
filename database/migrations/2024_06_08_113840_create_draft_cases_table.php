<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('draft_cases', function (Blueprint $table) {
            $table->uild('id')->primary();
            $table->string('subject');
            $table->string('subject_type');
            $table->string('person_in_charge');
            $table->string('beneficiary_ownership');
            $table->date('date');
            $table->string('decision_number');
            $table->string('source');
            $table->string('link');
            $table->date('punishment_start');
            $table->date('punishment_end');
            $table->string('type');
            $table->string('year');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('draft_cases');
    }
};
