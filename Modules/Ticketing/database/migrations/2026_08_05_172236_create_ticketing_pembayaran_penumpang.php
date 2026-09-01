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
        Schema::create('ticketing_pembayaran_penumpang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tckt_penumpang_id')
                ->constrained('ticketing_penumpang')
                ->name('fk_tckt_penumpang');
            $table->foreignId('tckt_pembayaran_id')
                ->constrained('ticketing_pembayaran')
                ->name('fk_tckt_pembayaran');
            $table->integer('jumlah_membayar');
            // $table->string('status_penumpang');
            $table->foreignId('user_id')->constrained('users');
            $table->string('bukti_pembayaran')->nullable();
            $table->date('tgl_membayar')->nullable();
            $table->boolean('status_bukti_bayar')->nullable();
            $table->timestamps();
            // $table->string('email')->unique();
            // $table->string('no_telp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticketing_pembayaran_penumpang');
    }
};
