<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingPembayars\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingPembayars\TicketingPembayarResource;

class EditTicketingPembayar extends EditRecord
{
    protected static string $resource = TicketingPembayarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
