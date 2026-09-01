<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('judan_need_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('judan_need_id')->constrained('judan_needs');
            $table->foreignId('judan_proposal_submission_id')->constrained('judan_proposal_submissions');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('judan_need_submissions');
    }
};
