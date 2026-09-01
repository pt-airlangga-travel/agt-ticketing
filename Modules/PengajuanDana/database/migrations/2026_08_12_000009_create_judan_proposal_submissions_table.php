<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('judan_proposal_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('no_submission', 17)->unique();
            $table->string('event_identity');
            $table->string('booking_code')->nullable();
            $table->foreignId('judan_proposal_draft_id')->constrained('judan_proposal_drafts');
            $table->foreignId('organizer_admin_id')->constrained('users');
            $table->foreignId('inspiring_manager_id')->nullable()->constrained('users');
            $table->enum('status', ['menunggu', 'proses_tf', 'selesai', 'ditolak', 'dikembalikan'])->default('menunggu');
            $table->string('catatan_manager')->nullable();
            $table->date('checked_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('judan_proposal_submissions');
    }
};
