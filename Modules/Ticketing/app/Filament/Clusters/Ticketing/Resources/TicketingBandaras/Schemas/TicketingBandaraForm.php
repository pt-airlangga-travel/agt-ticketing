<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingBandaras\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TicketingBandaraForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_bandara')
                    ->label('Nama Bandar Udara')
                    ->required()
                    ->maxLength(255),
                TextInput::make('kode_bandara')
                    ->label('Kode')
                    ->required()
                    ->maxLength(10),

            ]);
    }
}
