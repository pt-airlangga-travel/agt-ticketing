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
        Schema::create('ticketing_kamar_hotel_penumpang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tckt_penumpang_id')
                ->constrained('ticketing_penumpang')
                ->name('fk_tckt_penumpang');
            $table->foreignId('tckt_kamar_hotel_id')
                ->constrained('ticketing_kamar_hotel')
                ->name('fk_tckt_kamar_hotel');
            $table->unique(
                ['tckt_penumpang_id', 'tckt_kamar_hotel_id'],
                'uq_penumpang_kamar_hotel'
            );
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticketing_kamar_hotel_penumpang');
    }
};
