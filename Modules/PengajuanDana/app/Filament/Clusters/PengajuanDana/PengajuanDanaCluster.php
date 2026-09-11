<?php

namespace Modules\PengajuanDana\Filament\Clusters\PengajuanDana;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class PengajuanDanaCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $clusterBreadcrumb = 'Pengajuan Dana';

    protected static ?string $navigationLabel = 'Pengajuan Dana';

    protected static ?string $slug = 'pengajuan-dana';

    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user
            ? ($user->isSuperAdmin() || $user->hasRole('pengajuan-dana'))
            : false;
    }
}
