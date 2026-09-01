<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('judan_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->decimal('sub_total', 15, 2)->default(0);
            $table->string('pemilik', 150);
            $table->string('nomor_rekening', 50);
            $table->boolean('is_revised')->default(false);
            $table->text('alasan_revisi')->nullable();
            $table->foreignId('judan_bank_id')->constrained('judan_banks');
            $table->foreignId('judan_proposal_submission_id')->constrained('judan_proposal_submissions');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('judan_bank_accounts');
    }
};
