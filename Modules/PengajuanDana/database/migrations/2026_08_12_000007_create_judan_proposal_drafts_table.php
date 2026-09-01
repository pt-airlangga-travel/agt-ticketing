<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('judan_proposal_drafts', function (Blueprint $table) {
            $table->id();
            $table->string('no_pengajuan', 17)->unique();
            $table->foreignId('judan_event_id')->constrained('judan_events');
            $table->foreignId('creative_member_id')->constrained('users');
            $table->foreignId('organizer_admin_id')->nullable()->constrained('users');
            $table->enum('status', ['menunggu', 'diajukan', 'diterima', 'ditolak'])->default('menunggu');
            $table->string('catatan_admin')->nullable();
            $table->string('catatan_member')->nullable();
            $table->date('deadline_pembayaran');
            $table->string('file_attached');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('judan_proposal_drafts');
    }
};
