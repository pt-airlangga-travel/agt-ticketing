<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingVendors\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TicketingVendorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_vendor')
                    ->label('Nama Vendor')
                    ->required()
                    ->maxLength(255),

                Select::make('jenis_vendor')
                    ->label('Jenis Vendor')
                    ->options([
                        1 => 'Pesawat',
                        2 => 'Kereta',
                        3 => 'Hotel',
                        4 => 'Dokumen',
                    ])
                    ->required(),
            ]);
    }
}
