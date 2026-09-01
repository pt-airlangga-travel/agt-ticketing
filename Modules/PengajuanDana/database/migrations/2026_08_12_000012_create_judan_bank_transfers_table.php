<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('judan_bank_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('no_transfer', 17)->unique();
            $table->foreignId('judan_bank_account_id')->constrained('judan_bank_accounts');
            $table->foreignId('judan_bank_asal_id')->nullable()->constrained('judan_bank_asals');
            $table->foreignId('eagle_treasurer_id')->constrained('users');
            $table->string('file_attached');
            $table->string('melalui');
            $table->date('tanggal_transfer');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('judan_bank_transfers');
    }
};
