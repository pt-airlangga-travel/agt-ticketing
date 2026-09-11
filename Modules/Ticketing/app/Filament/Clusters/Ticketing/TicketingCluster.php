<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Navigation\NavigationItem;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\Widget;
use Filament\Widgets\WidgetConfiguration;
use Modules\Ticketing\Filament\Clusters\Ticketing\Widgets\RecentReservasiWidget;
use Modules\Ticketing\Filament\Clusters\Ticketing\Widgets\TicketingChart;
use Modules\Ticketing\Filament\Clusters\Ticketing\Widgets\TicketingStatsOverview;

class TicketingCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $clusterBreadcrumb = 'Ticketing';

    protected static ?string $navigationLabel = 'Ticketing';

    protected static ?string $title = 'Dashboard Ticketing';

    protected static ?string $slug = 'ticketing';

    protected static ?int $navigationSort = 2;

    public function mount(): void
    {
        // Render dashboard modul ini alih-alih redirect ke halaman resource pertama.
    }

    /**
     * Item Dashboard pada sub-navigation cluster.
     */
    public static function getDashboardNavigationItem(): NavigationItem
    {
        return NavigationItem::make('Dashboard')
            ->icon(Heroicon::OutlinedSquares2x2)
            ->url(static::getUrl())
            ->isActiveWhen(fn (): bool => request()->routeIs(static::getRouteName()))
            ->sort(0);
    }

    public function getSubNavigation(): array
    {
        return [
            static::getDashboardNavigationItem(),
            ...$this->generateNavigationItems(static::getClusteredComponents()),
        ];
    }

    /**
     * @return array<class-string<Widget> | WidgetConfiguration>
     */
    public function getWidgets(): array
    {
        return [
            TicketingStatsOverview::class,
            TicketingChart::class,
            RecentReservasiWidget::class,
        ];
    }

    public function getColumns(): int|array
    {
        return 2;
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user
            ? ($user->isSuperAdmin() || $user->hasRole('ticketing'))
            : false;
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make($this->getColumns())
                    ->schema(
                        fn (): array => $this->getWidgetsSchemaComponents(
                            $this->getWidgets()
                        )
                    ),
            ]);
    }
}
