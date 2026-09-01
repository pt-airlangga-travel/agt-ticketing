<?php

namespace Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\Events\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Concerns\HasClusterSubNavigation;
use Modules\PengajuanDana\Filament\Clusters\PengajuanDana\Resources\Events\EventResource;

class ViewEvent extends ViewRecord
{
    use HasClusterSubNavigation;

    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Edit'),
        ];
    }
}
