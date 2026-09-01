<?php

namespace Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\Widget;
use Filament\Widgets\WidgetConfiguration;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\PengajuanDanaCluster;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Widgets\PengajuanDanaChart;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Widgets\PengajuanDanaStatsOverview;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Widgets\RecentSubmissionsWidget;

class Dashboard extends Page
{
    protected static ?string $cluster = PengajuanDanaCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $title = 'Dashboard Pengajuan Dana';

    protected static ?int $navigationSort = -2;

    /**
     * @return array<class-string<Widget> | WidgetConfiguration>
     */
    public function getWidgets(): array
    {
        return [
            PengajuanDanaStatsOverview::class,
            PengajuanDanaChart::class,
            RecentSubmissionsWidget::class,
        ];
    }

    public function getColumns(): int|array
    {
        return 2;
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make($this->getColumns())
                    ->schema(fn (): array => $this->getWidgetsSchemaComponents($this->getWidgets())),
            ]);
    }
}
