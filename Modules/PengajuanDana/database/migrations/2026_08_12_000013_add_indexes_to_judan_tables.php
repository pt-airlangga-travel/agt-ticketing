<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Master
        Schema::table('judan_divisions', function (Blueprint $table) {
            $table->index('is_active', 'idx_judan_divisions_is_active');
        });

        Schema::table('judan_institutions', function (Blueprint $table) {
            $table->index('is_active', 'idx_judan_institutions_is_active');
        });

        Schema::table('judan_banks', function (Blueprint $table) {
            $table->index('is_active', 'idx_judan_banks_is_active');
        });

        Schema::table('judan_bank_asals', function (Blueprint $table) {
            $table->index('is_active', 'idx_judan_bank_asals_is_active');
            $table->index('no_rekening', 'idx_judan_bank_asals_no_rekening');
        });

        Schema::table('judan_needs', function (Blueprint $table) {
            $table->index('is_active', 'idx_judan_needs_is_active');
        });

        // Event
        Schema::table('judan_events', function (Blueprint $table) {
            $table->index(['is_active', 'tanggal_mulai'], 'idx_judan_events_active_start');
            $table->index('nama', 'idx_judan_events_nama');
        });

        // Proposal Draft
        Schema::table('judan_proposal_drafts', function (Blueprint $table) {
            $table->index('status', 'idx_judan_proposal_drafts_status');
            $table->index(['creative_member_id', 'status'], 'idx_judan_proposal_drafts_creator_status');
            $table->index(['judan_event_id', 'status'], 'idx_judan_proposal_drafts_event_status');
            $table->index('created_at', 'idx_judan_proposal_drafts_created_at');
        });

        // Vendor
        Schema::table('judan_vendors', function (Blueprint $table) {
            $table->index(['judan_proposal_draft_id', 'nama_vendor'], 'idx_judan_vendors_draft_nama');
        });

        // Proposal Submission
        Schema::table('judan_proposal_submissions', function (Blueprint $table) {
            $table->index('status', 'idx_judan_proposal_submissions_status');
            $table->index(['judan_proposal_draft_id', 'status'], 'idx_judan_proposal_submissions_draft_status');
            $table->index(['status', 'created_at'], 'idx_judan_proposal_submissions_status_created');
        });

        // Pivot need_submissions
        Schema::table('judan_need_submissions', function (Blueprint $table) {
            $table->index(['judan_need_id', 'judan_proposal_submission_id'], 'idx_judan_need_submissions_need_sub');
            $table->index(['judan_proposal_submission_id', 'judan_need_id'], 'idx_judan_need_submissions_sub_need');
        });

        // Bank Account
        Schema::table('judan_bank_accounts', function (Blueprint $table) {
            $table->index(['judan_proposal_submission_id', 'is_revised'], 'idx_judan_bank_accounts_sub_revised');
        });

        // Bank Transfer
        Schema::table('judan_bank_transfers', function (Blueprint $table) {
            $table->index(['judan_bank_account_id', 'tanggal_transfer'], 'idx_judan_bank_transfers_account_tanggal');
        });
    }

    public function down(): void
    {
        Schema::table('judan_divisions', function (Blueprint $table) {
            $table->dropIndex('idx_judan_divisions_is_active');
        });

        Schema::table('judan_institutions', function (Blueprint $table) {
            $table->dropIndex('idx_judan_institutions_is_active');
        });

        Schema::table('judan_banks', function (Blueprint $table) {
            $table->dropIndex('idx_judan_banks_is_active');
        });

        Schema::table('judan_bank_asals', function (Blueprint $table) {
            $table->dropIndex('idx_judan_bank_asals_is_active');
            $table->dropIndex('idx_judan_bank_asals_no_rekening');
        });

        Schema::table('judan_needs', function (Blueprint $table) {
            $table->dropIndex('idx_judan_needs_is_active');
        });

        Schema::table('judan_events', function (Blueprint $table) {
            $table->dropIndex('idx_judan_events_active_start');
            $table->dropIndex('idx_judan_events_nama');
        });

        Schema::table('judan_proposal_drafts', function (Blueprint $table) {
            $table->dropIndex('idx_judan_proposal_drafts_status');
            $table->dropIndex('idx_judan_proposal_drafts_creator_status');
            $table->dropIndex('idx_judan_proposal_drafts_event_status');
            $table->dropIndex('idx_judan_proposal_drafts_created_at');
        });

        Schema::table('judan_vendors', function (Blueprint $table) {
            $table->dropIndex('idx_judan_vendors_draft_nama');
        });

        Schema::table('judan_proposal_submissions', function (Blueprint $table) {
            $table->dropIndex('idx_judan_proposal_submissions_status');
            $table->dropIndex('idx_judan_proposal_submissions_draft_status');
            $table->dropIndex('idx_judan_proposal_submissions_status_created');
        });

        Schema::table('judan_need_submissions', function (Blueprint $table) {
            $table->dropIndex('idx_judan_need_submissions_need_sub');
            $table->dropIndex('idx_judan_need_submissions_sub_need');
        });

        Schema::table('judan_bank_accounts', function (Blueprint $table) {
            $table->dropIndex('idx_judan_bank_accounts_sub_revised');
        });

        Schema::table('judan_bank_transfers', function (Blueprint $table) {
            $table->dropIndex('idx_judan_bank_transfers_account_tanggal');
        });
    }
};
