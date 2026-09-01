<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('judan_proposal_drafts', function (Blueprint $table) {
            $table->renameColumn('judan_event_id', 'event_id');
        });
    }

    public function down(): void
    {
        Schema::table('judan_proposal_drafts', function (Blueprint $table) {
            $table->renameColumn('event_id', 'judan_event_id');
        });
    }
};
