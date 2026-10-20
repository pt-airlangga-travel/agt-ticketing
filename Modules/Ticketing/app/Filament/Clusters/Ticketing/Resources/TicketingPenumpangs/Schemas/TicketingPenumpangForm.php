<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingPenumpangs\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TicketingPenumpangForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_penumpang')
                    ->label('Nama Penumpang')
                    ->required()
                    ->maxLength(255),

                Select::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->options([
                        0 => 'Laki-laki',
                        1 => 'Perempuan',
                    ])
                    ->required()
                    ->native(false),
            ]);
    }
}
