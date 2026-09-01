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
        Schema::table('ticketing_pembayaran_penumpang', function (Blueprint $table) {
            $table->string('nama_pembayar')->nullable()->after('tckt_pembayaran_id');
            $table->foreignId('tckt_unit_kerja_id')
                ->nullable()
                ->after('nama_pembayar')
                ->constrained('ticketing_unit_kerja')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticketing_pembayaran_penumpang', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tckt_unit_kerja_id');
            $table->dropColumn('nama_pembayar');
        });
    }
};
