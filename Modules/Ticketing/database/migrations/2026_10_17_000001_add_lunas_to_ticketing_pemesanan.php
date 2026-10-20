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
            if (! Schema::hasColumn('ticketing_pemesanan', 'lunas')) {
                $table->boolean('lunas')->default(false)->after('status_pemesanan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticketing_pemesanan', function (Blueprint $table) {
            if (Schema::hasColumn('ticketing_pemesanan', 'lunas')) {
                $table->dropColumn('lunas');
            }
        });
    }
};
