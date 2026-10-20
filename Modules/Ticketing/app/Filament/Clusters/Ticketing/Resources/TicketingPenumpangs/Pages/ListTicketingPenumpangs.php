<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingPenumpangs\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingPenumpangs\TicketingPenumpangResource;

class ListTicketingPenumpangs extends ListRecords
{
    protected static string $resource = TicketingPenumpangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('+ Tambah')
                ->createAnother(false)
                ->modalHeading('Tambah Nama Penumpang'),
        ];
    }
}
