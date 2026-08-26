<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingStasiuns\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingStasiuns\TicketingStasiunResource;

class CreateTicketingStasiun extends CreateRecord
{
    protected static string $resource = TicketingStasiunResource::class;
}
