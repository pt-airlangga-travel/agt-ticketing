<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiKeretas\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Ticketing\Filament\Clusters\Ticketing\ReservasiFormPartials;
use Modules\Ticketing\Models\TicketingKereta;
use Modules\Ticketing\Models\TicketingStasiun;
use Modules\Ticketing\Models\TicketingVendor;

class ReservasiKeretaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Pemesan')->schema(ReservasiFormPartials::kolomPemesan()),
                Section::make('Harga')->schema(ReservasiFormPartials::kolomHarga()),
                Section::make('Kereta (Keberangkatan)')->schema(self::kolomKereta()),
            ]);
    }

    public static function kolomKereta(): array
    {
        return [
            Grid::make(2)->schema([
                Select::make('kereta_id')
                    ->label('Kereta')
                    ->options(fn () => TicketingKereta::query()
                        ->where('is_active', true)
                        ->pluck('nama_kereta', 'id'))
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('kode_booking_kereta')
                    ->label('Kode Booking')
                    ->required()
                    ->maxLength(255),

                Select::make('stasiun_berangkat_id')
                    ->label('Stasiun Berangkat')
                    ->options(fn () => TicketingStasiun::query()
                        ->where('is_active', true)
                        ->pluck('nama_stasiun', 'id'))
                    ->searchable()
                    ->preload()
                    ->required(),

                Grid::make(2)
                    ->schema([
                        DateTimePicker::make('jadwal_berangkat_kereta')
                            ->label('Waktu Berangkat')
                            ->seconds(false)
                            ->required(),

                        ReservasiFormPartials::kolomZonaWaktuKeberangkatan(),
                    ]),

                Select::make('stasiun_tiba_id')
                    ->label('Stasiun Tiba')
                    ->options(fn () => TicketingStasiun::query()
                        ->where('is_active', true)
                        ->pluck('nama_stasiun', 'id'))
                    ->searchable()
                    ->preload()
                    ->required(),

                Grid::make(2)
                    ->schema([
                        DateTimePicker::make('jadwal_tiba_kereta')
                            ->label('Waktu Tiba')
                            ->seconds(false)
                            ->required(),

                        ReservasiFormPartials::kolomZonaWaktuKedatangan(),
                    ]),

                Select::make('vendor_id')
                    ->label('Vendor')
                    ->options(fn () => TicketingVendor::query()
                        ->where('jenis_vendor', 2)
                        ->pluck('nama_vendor', 'id'))
                    ->searchable()
                    ->preload()
                    ->required(),

            ]),
        ];
    }
}
