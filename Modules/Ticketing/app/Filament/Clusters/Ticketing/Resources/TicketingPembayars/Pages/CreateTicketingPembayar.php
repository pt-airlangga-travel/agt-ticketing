<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingPembayars\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingPembayars\TicketingPembayarResource;

class CreateTicketingPembayar extends CreateRecord
{
    protected static string $resource = TicketingPembayarResource::class;
}
