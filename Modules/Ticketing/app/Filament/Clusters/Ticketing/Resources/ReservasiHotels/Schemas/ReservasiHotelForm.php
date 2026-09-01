<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiHotels\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Modules\Ticketing\Filament\Clusters\Ticketing\ReservasiFormPartials;
use Modules\Ticketing\Models\TicketingHotel;
use Modules\Ticketing\Models\TicketingVendor;

class ReservasiHotelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Pemesan')->schema(ReservasiFormPartials::kolomPemesan()),
                Section::make('Pembayar')->schema(ReservasiFormPartials::kolomPembayar()),
                Section::make('Harga')->schema(ReservasiFormPartials::kolomHarga()),
                Section::make('Hotel')->schema(self::kolomHotel()),
            ]);
    }

    public static function kolomHotel(): array
    {
        return [
            Grid::make(2)->schema([

                Select::make('hotel_id')
                    ->label('Hotel')
                    ->options(fn () => TicketingHotel::query()
                        ->where('is_active', true)
                        ->pluck('nama_hotel', 'id'))
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('tipe_kamar')
                    ->label('Tipe Kamar')
                    ->required()
                    ->maxLength(255),

                TextInput::make('lama_menginap')
                    ->label('Lama Menginap (hari)')
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters('.')
                    ->dehydrateStateUsing(fn ($state) => (int) str_replace('.', '', $state))
                    ->minValue(1)
                    ->required(),

                TextInput::make('jumlah_kamar')
                    ->label('Jumlah Kamar')
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters('.')
                    ->dehydrateStateUsing(fn ($state) => (int) str_replace('.', '', $state))
                    ->minValue(1)
                    ->required(),

                DateTimePicker::make('jadwal_checkin')
                    ->label('Waktu Check-in')
                    ->seconds(false)
                    ->required(),

                DateTimePicker::make('jadwal_checkout')
                    ->label('Waktu Check-out')
                    ->seconds(false)
                    ->required(),

                ReservasiFormPartials::kolomZonaWaktu(),

                Select::make('include_breakfast')
                    ->label('Termasuk Sarapan')
                    ->options([
                        1 => 'Ya',
                        0 => 'Tidak',
                    ]),

                Select::make('vendor_id')
                    ->label('Vendor')
                    ->options(fn () => TicketingVendor::query()
                        ->where('jenis_vendor', 3)
                        ->pluck('nama_vendor', 'id'))
                    ->searchable()
                    ->preload()
                    ->required(),
            ]),
        ];
    }
}
