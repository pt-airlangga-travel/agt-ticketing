<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingStasiuns\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TicketingStasiunForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_stasiun')
                    ->label('Nama Stasiun')
                    ->required()
                    ->maxLength(255),
                
                TextInput::make('kode_stasiun')
                    ->label('Kode Stasiun')
                    ->required()
                    ->maxLength(255),
                
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }
}
