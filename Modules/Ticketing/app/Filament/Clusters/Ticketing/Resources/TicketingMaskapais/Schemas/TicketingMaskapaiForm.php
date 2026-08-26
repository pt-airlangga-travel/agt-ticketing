<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingMaskapais\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TicketingMaskapaiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_maskapai')
                    ->label('Nama Maskapai')
                    ->required()
                    ->columnSpanFull()
                    ->maxLength(255),
            ]);
    }
}
