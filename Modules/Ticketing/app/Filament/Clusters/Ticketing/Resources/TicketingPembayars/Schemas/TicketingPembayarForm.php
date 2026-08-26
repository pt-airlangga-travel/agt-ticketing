<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingPembayars\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TicketingPembayarForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_pembayar')
                    ->label('Nama Pembayar')
                    ->required()
                    ->columnSpanFull()
                    ->maxLength(255),
            ]);
    }
}
