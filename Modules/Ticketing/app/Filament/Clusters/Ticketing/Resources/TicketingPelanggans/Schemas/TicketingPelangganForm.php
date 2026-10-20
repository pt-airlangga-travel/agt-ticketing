<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingPelanggans\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TicketingPelangganForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_pelanggan')
                    ->label('Nama Pemesan')
                    ->required()
                    ->columnSpanFull()
                    ->maxLength(255),
            ]);
    }
}
