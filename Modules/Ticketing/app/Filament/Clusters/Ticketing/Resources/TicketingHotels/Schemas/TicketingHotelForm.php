<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingHotels\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TicketingHotelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_hotel')
                    ->label('Nama Hotel')
                    ->required()
                    ->maxLength(255),
                TextInput::make('alamat')
                    ->label('Alamat')
                    ->required(),
                TextInput::make('bintang')
                    ->label('Bintang')
                    ->required(),
                TextInput::make('kota')
                    ->label('Kota')
                    ->required(),
                TextInput::make('Telepon')
                    ->label('Telepon')
                    ->required(),
                TextInput::make('email')
                    ->label('Email')
                    ->required(),
            ]);
    }
}
