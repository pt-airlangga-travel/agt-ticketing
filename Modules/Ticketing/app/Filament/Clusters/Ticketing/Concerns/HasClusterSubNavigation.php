<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Concerns;

trait HasClusterSubNavigation
{
    /**
     * Tampilkan sub-navigation cluster juga di halaman Create/Edit/View,
     * tidak hanya di halaman List (perilaku bawaan Filament).
     */
    public function getSubNavigation(): array
    {
        if (filled($cluster = static::getCluster())) {
            return [
                $cluster::getDashboardNavigationItem(),
                ...$this->generateNavigationItems($cluster::getClusteredComponents()),
            ];
        }

        return [];
    }
}
