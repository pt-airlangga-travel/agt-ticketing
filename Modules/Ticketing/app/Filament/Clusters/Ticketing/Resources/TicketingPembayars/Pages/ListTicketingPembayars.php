<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingPembayars\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingPembayars\TicketingPembayarResource;

class ListTicketingPembayars extends ListRecords
{
    protected static string $resource = TicketingPembayarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('+ Tambah')
                ->createAnother(false)
                ->modalHeading('Tambah Nama Pembayar'),
        ];
    }
}
