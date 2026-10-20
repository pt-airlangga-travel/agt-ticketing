<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingPelanggans\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingPelanggans\TicketingPelangganResource;

class ListTicketingPelanggans extends ListRecords
{
    protected static string $resource = TicketingPelangganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('+ Tambah')
                ->createAnother(false)
                ->modalHeading('Tambah Nama Pemesan'),
        ];
    }
}
