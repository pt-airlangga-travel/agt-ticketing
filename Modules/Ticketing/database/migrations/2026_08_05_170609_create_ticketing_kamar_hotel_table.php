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

        Schema::create('ticketing_kamar_hotel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tckt_hotel_id')
                ->constrained('ticketing_hotel')
                ->name('fk_tckt_hotel');
            $table->foreignId('tckt_vendor_id')
                ->constrained('ticketing_vendor')
                ->name('fk_tckt_vendor');
            $table->foreignId('tckt_pemesanan_id')
                ->constrained('ticketing_pemesanan')
                ->name('fk_tckt_pemesanan');
            $table->integer('jumlah_kamar');
            $table->integer('lama_menginap');
            $table->string('tipe_kamar');
            $table->dateTime('jadwal_checkin');
            $table->dateTime('jadwal_checkout');
            $table->boolean('include_breakfast');
            $table->string('zona_waktu')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticketing_kamar_hotel');
    }
};
