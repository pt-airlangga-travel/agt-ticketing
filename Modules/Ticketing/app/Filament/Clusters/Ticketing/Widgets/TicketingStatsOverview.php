<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Widgets;

use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;
use Modules\Ticketing\Filament\Clusters\Ticketing\Concerns\ScopesReservasiDashboardQuery;

class TicketingStatsOverview extends StatsOverviewWidget
{
    use ScopesReservasiDashboardQuery;

    protected function getStats(): array
    {
        $query = $this->getReservasiDashboardQuery();

        $total = (clone $query)->count();
        $totalNilai = (clone $query)->sum('harga_jual');

        $countBy = fn (string $relation): int => (clone $query)->whereHas($relation)->count();

        $statusCounts = (clone $query)
            ->selectRaw('status_pemesanan, count(*) as total')
            ->groupBy('status_pemesanan')
            ->pluck('total', 'status_pemesanan')
            ->all();

        return [
            Stat::make('Total Reservasi', $total)
                ->description('Semua jenis reservasi')
                ->icon(Heroicon::OutlinedClipboardDocumentList),

            Stat::make('Pesawat', $countBy('ticketingTiketPesawat'))
                ->description('Reservasi tiket pesawat')
                ->icon(Heroicon::OutlinedRocketLaunch),

            Stat::make('Kereta', $countBy('ticketingTiketKereta'))
                ->description('Reservasi tiket kereta')
                ->icon(Heroicon::OutlinedTruck),

            Stat::make('Hotel', $countBy('ticketingKamarHotel'))
                ->description('Reservasi kamar hotel')
                ->icon(Heroicon::OutlinedBuildingOffice2),

            Stat::make('Dokumen', $countBy('ticketingDokumen'))
                ->description('Reservasi dokumen')
                ->icon(Heroicon::OutlinedDocumentText),

            Stat::make('Total Nilai', 'Rp '.Number::format($totalNilai, 0, null, 'id'))
                ->description('Total harga jual')
                ->color('success')
                ->icon(Heroicon::OutlinedArrowTrendingUp),

            Stat::make('Confirmed', $statusCounts['Confirmed'] ?? 0)
                ->description('Reservasi dikonfirmasi')
                ->color('success')
                ->icon(Heroicon::OutlinedCheckCircle),

            Stat::make('Canceled / Reschedule', ($statusCounts['Canceled'] ?? 0) + ($statusCounts['Reschedule'] ?? 0))
                ->description('Perlu perhatian')
                ->color('danger')
                ->icon(Heroicon::OutlinedExclamationTriangle),
        ];
    }
}
