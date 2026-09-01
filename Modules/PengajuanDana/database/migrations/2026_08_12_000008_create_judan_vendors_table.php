<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('judan_vendors', function (Blueprint $table) {
            $table->id();
            $table->string('nama_vendor');
            $table->decimal('sub_total', 15, 2)->default(0);
            $table->string('kontak')->nullable();
            $table->string('email')->nullable();
            $table->foreignId('judan_proposal_draft_id')->constrained('judan_proposal_drafts');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('judan_vendors');
    }
};
