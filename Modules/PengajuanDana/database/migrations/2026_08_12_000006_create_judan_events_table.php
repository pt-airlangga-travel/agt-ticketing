<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('judan_events', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('nama_singkat', 12);
            $table->string('slug', 12)->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->boolean('is_active')->default(true);
            $table->foreignId('judan_institution_id')->constrained('judan_institutions');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('judan_events');
    }
};
