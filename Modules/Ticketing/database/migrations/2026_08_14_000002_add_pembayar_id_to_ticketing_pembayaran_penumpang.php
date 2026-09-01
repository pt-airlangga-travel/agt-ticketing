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
            $table->unsignedBigInteger('tckt_pembayar_id')->nullable()->after('tckt_pembayaran_id');

            $table->foreign('tckt_pembayar_id')
                ->references('id')
                ->on('ticketing_pembayar')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticketing_pembayaran_penumpang', function (Blueprint $table) {
            $table->dropForeign(['tckt_pembayar_id']);
            $table->dropColumn('tckt_pembayar_id');
        });
    }
};
