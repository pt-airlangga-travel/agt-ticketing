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
        Schema::create('ticketing_tiket_kereta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tckt_pemesanan_id')
                ->constrained('ticketing_pemesanan')
                ->name('fk_tckt_pemesanan');
            $table->foreignId('tckt_vendor_id')
                ->constrained('ticketing_vendor')
                ->name('fk_tckt_vendor');
            $table->foreignId('tckt_kereta_id')
                ->constrained('ticketing_kereta')
                ->name('fk_tckt_kereta');
            $table->foreignId('tckt_stasiun_berangkat_id')
                ->constrained('ticketing_stasiun')
                ->name('fk_tckt_stasiun');
            $table->foreignId('tckt_stasiun_tiba_id')
                ->constrained('ticketing_stasiun')
                ->name('fk_tckt_stasiun');
            $table->string('kode_booking_kereta')->unique();
            $table->dateTime('jadwal_berangkat_kereta');
            $table->dateTime('jadwal_tiba_kereta');
            // $table->string('jenis_tiket_kereta');
            $table->string('zona_waktu')->nullable();
            $table->string('zona_waktu_kedatangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticketing_tiket_kereta');
    }
};
