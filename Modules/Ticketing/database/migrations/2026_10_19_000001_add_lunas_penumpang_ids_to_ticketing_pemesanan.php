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
        Schema::table('ticketing_pemesanan', function (Blueprint $table) {
            if (! Schema::hasColumn('ticketing_pemesanan', 'lunas_penumpang_ids')) {
                $table->json('lunas_penumpang_ids')->nullable()->after('lunas');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticketing_pemesanan', function (Blueprint $table) {
            if (Schema::hasColumn('ticketing_pemesanan', 'lunas_penumpang_ids')) {
                $table->dropColumn('lunas_penumpang_ids');
            }
        });
    }
};
