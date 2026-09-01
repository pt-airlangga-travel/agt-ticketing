<?php

namespace Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;
use Modules\PengajuanDana\Models\ProposalSubmission;

class PengajuanDanaChart extends ChartWidget
{
    protected static ?int $sort = 2;

    public function getHeading(): string|Htmlable|null
    {
        return 'Submission per Bulan';
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $labels = [];
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $labels[] = $month->format('M Y');
            $data[] = ProposalSubmission::query()
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Submission',
                    'data' => $data,
                    'backgroundColor' => '#20206c',
                ],
            ],
            'labels' => $labels,
        ];
    }
}
