<?php

namespace Modules\User\Filament;

use Coolsam\Modules\Concerns\ModuleFilamentPlugin;
use Filament\Contracts\Plugin;
use Filament\Panel;

class UserPlugin implements Plugin
{
    use ModuleFilamentPlugin;

    public function getModuleName(): string
    {
        return 'User';
    }

    public function getId(): string
    {
        return 'user';
    }

    public function boot(Panel $panel): void
    {
        // TODO: Implement boot() method.
    }

    public function register(Panel $panel): void
    {
        $panel
            ->discoverResources(in: __DIR__.'/Resources', for: 'Modules\\User\\Filament\\Resources')
            ->discoverPages(in: __DIR__.'/Pages', for: 'Modules\\User\\Filament\\Pages')
            ->discoverWidgets(in: __DIR__.'/Widgets', for: 'Modules\\User\\Filament\\Widgets')
            ->discoverClusters(in: __DIR__.'/Clusters', for: 'Modules\\User\\Filament\\Clusters'); // <- ini yang mungkin hilang/salah path
    }
}
