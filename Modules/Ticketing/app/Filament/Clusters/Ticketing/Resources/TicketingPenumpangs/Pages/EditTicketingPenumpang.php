<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingPenumpangs\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingPenumpangs\TicketingPenumpangResource;

class EditTicketingPenumpang extends EditRecord
{
    protected static string $resource = TicketingPenumpangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
