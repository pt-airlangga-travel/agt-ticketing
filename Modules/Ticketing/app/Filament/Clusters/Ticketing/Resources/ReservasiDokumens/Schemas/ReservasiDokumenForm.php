<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiDokumens\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Ticketing\Filament\Clusters\Ticketing\ReservasiFormPartials;
use Modules\Ticketing\Models\TicketingVendor;

class ReservasiDokumenForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Pemesan')->schema(ReservasiFormPartials::kolomPemesan()),
                Section::make('Harga')->schema(ReservasiFormPartials::kolomHarga()),
                Section::make('Dokumen')->schema(self::kolomDokumen()),
            ]);
    }

    public static function kolomDokumen(): array
    {
        return [
            Grid::make(2)->schema([
                Select::make('vendor_id')
                    ->label('Vendor')
                    ->options(fn () => TicketingVendor::query()
                        ->where('jenis_vendor', 4)
                        ->pluck('nama_vendor', 'id'))
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('jenis_dokumen')
                    ->label('Jenis Dokumen')
                    ->required()
                    ->maxLength(255),

                Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->rows(3)
                    ->columnSpanFull()
                    ->required(),
            ]),
        ];
    }
}
