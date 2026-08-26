<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;
use Modules\Ticketing\Filament\Clusters\Ticketing\Concerns\ScopesReservasiDashboardQuery;

class TicketingChart extends ChartWidget
{
    use ScopesReservasiDashboardQuery;

    protected static ?int $sort = 2;

    public function getHeading(): string|Htmlable|null
    {
        return 'Reservasi per Bulan';
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $labels = [];
        $data = [];

        $query = $this->getReservasiDashboardQuery();

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $labels[] = $month->format('M Y');
            $data[] = (clone $query)
                ->whereYear('tanggal_pemesanan', $month->year)
                ->whereMonth('tanggal_pemesanan', $month->month)
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Reservasi',
                    'data' => $data,
                    'backgroundColor' => '#20206c',
                ],
            ],
            'labels' => $labels,
        ];
    }
}
