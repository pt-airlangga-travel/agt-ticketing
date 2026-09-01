<?php

namespace Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Widgets;

use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;
use Modules\PengajuanDana\Enums\ProposalSubmissionStatus;
use Modules\PengajuanDana\Models\BankAccount;
use Modules\PengajuanDana\Models\ProposalDraft;
use Modules\PengajuanDana\Models\ProposalSubmission;

class PengajuanDanaStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalDanaTersalur = BankAccount::query()
            ->whereHas('proposalSubmission', fn ($q) => $q->where('status', ProposalSubmissionStatus::Selesai))
            ->sum('sub_total');

        return [
            Stat::make('Total Pengajuan', ProposalDraft::count())
                ->description('Proposal draft diajukan')
                ->icon(Heroicon::OutlinedDocumentText),

            Stat::make('Menunggu', ProposalSubmission::where('status', ProposalSubmissionStatus::Menunggu)->count())
                ->description('Perlu disetujui')
                ->color('warning')
                ->icon(Heroicon::OutlinedClock),

            Stat::make('Proses Transfer', ProposalSubmission::where('status', ProposalSubmissionStatus::ProsesTransfer)->count())
                ->description('Sedang diproses bendahara')
                ->color('info')
                ->icon(Heroicon::OutlinedBanknotes),

            Stat::make('Selesai', ProposalSubmission::where('status', ProposalSubmissionStatus::Selesai)->count())
                ->description('Reimburse selesai')
                ->color('success')
                ->icon(Heroicon::OutlinedCheckCircle),

            Stat::make('Ditolak', ProposalSubmission::where('status', ProposalSubmissionStatus::Ditolak)->count())
                ->description('Submission ditolak')
                ->color('danger')
                ->icon(Heroicon::OutlinedHandThumbDown),

            Stat::make('Total Dana Tersalur', 'Rp '.Number::format($totalDanaTersalur, 0, null, 'id'))
                ->description('Dari submission selesai')
                ->color('success')
                ->icon(Heroicon::OutlinedArrowTrendingUp),
        ];
    }
}
