<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\RawJs;
use Modules\Ticketing\Models\TicketingKategoriPemesanan;
use Modules\Ticketing\Models\TicketingPelanggan;
use Modules\Ticketing\Models\TicketingUnitKerja;

class ReservasiFormPartials
{
    public static function statusOptions(): array
    {
        return [
            'Confirmed' => 'Confirmed',
            'Canceled' => 'Canceled',
            'No show' => 'No show',
            'Refund' => 'Refund',
            'Reroute' => 'Reroute',
            'Open' => 'Open',
            'Reschedule' => 'Reschedule',
        ];
    }

    public static function zonaWaktuOptions(): array
    {
        $options = [
            'WIB' => 'WIB (GMT+7)',
            'WITA' => 'WITA (GMT+8)',
            'WIT' => 'WIT (GMT+9)',
        ];

        for ($i = 0; $i <= 14; $i++) {
            if (in_array($i, [7, 8, 9], true)) {
                continue;
            }
            $options['GMT+'.$i] = 'GMT+'.$i;
        }

        return $options;
    }

    public static function kolomZonaWaktu(): Select
    {
        return Select::make('zona_waktu')
            ->label('Zona Waktu')
            ->options(self::zonaWaktuOptions())
            ->default('WIB')
            ->required();
    }

    public static function kolomZonaWaktuKeberangkatan(): Select
    {
        return Select::make('zona_waktu')
            ->label('Zona Waktu Keberangkatan')
            ->options(self::zonaWaktuOptions())
            ->default('WIB')
            ->required();
    }

    public static function kolomZonaWaktuKedatangan(): Select
    {
        return Select::make('zona_waktu_kedatangan')
            ->label('Zona Waktu Kedatangan')
            ->options(self::zonaWaktuOptions())
            ->default('WIB')
            ->required();
    }

    public static function kolomPemesan(): array
    {
        return [
            Grid::make(2)->schema([
                DatePicker::make('tanggal_pemesanan')
                    ->label('Tanggal Pemesanan')
                    ->default(now())
                    ->required(),

                Select::make('nama_customer')
                    ->label('Nama Pemesan')
                    ->options(fn () => TicketingPelanggan::query()
                        ->pluck('nama_pelanggan', 'nama_pelanggan'))
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('unit_kerja_pemesan')
                    ->label('Unit Kerja Pemesan')
                    ->options(fn () => TicketingUnitKerja::query()
                        ->where('is_active', true)
                        ->pluck('nama_unit_kerja', 'id'))
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('status_pemesanan')
                    ->label('Status Pemesanan')
                    ->options(self::statusOptions())
                    ->required(),

                Select::make('kategori_pemesanan_id')
                    ->label('Kategori Pemesanan')
                    ->options(fn () => TicketingKategoriPemesanan::query()
                        ->pluck('nama_kategori', 'id'))
                    ->searchable()
                    ->preload()
                    ->required(),
            ]),
        ];
    }

    public static function kolomPembayar(): array
    {
        return [
            Grid::make(2)->schema([
                Select::make('nama_pembayar')
                    ->label('Nama Pembayar')
                    ->options(fn () => TicketingPelanggan::query()
                        ->pluck('nama_pelanggan', 'nama_pelanggan'))
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('unit_kerja_pembayar')
                    ->label('Unit Kerja Pembayar')
                    ->options(fn () => TicketingUnitKerja::query()
                        ->where('is_active', true)
                        ->pluck('nama_unit_kerja', 'id'))
                    ->searchable()
                    ->preload()
                    ->required(),
            ]),
        ];
    }

    public static function kolomHarga(): array
    {
        return [
            Grid::make(3)->schema([
                TextInput::make('harga_beli')
                    ->label('Harga NTA (Beli) / HPP')
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters('.')
                    ->dehydrateStateUsing(fn ($state) => (int) self::parseRupiah($state))
                    ->live()
                    ->required(),

                TextInput::make('harga_publish')
                    ->label('Komisi (Publish)')
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters('.')
                    ->dehydrateStateUsing(fn ($state) => (int) self::parseRupiah($state))
                    ->live()
                    ->required(),

                TextInput::make('harga_jual')
                    ->label('Harga Jual')
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters('.')
                    ->dehydrateStateUsing(fn ($state) => (int) self::parseRupiah($state))
                    ->live()
                    ->required()
                    ->rules([
                        fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get): void {
                            $hpp = self::parseRupiah($get('harga_beli'));
                            $komisi = self::parseRupiah($get('harga_publish'));
                            $jual = self::parseRupiah($value);
                            $profit = $jual - $hpp - $komisi;

                            if ($profit < 0) {
                                $fail('Harga jual harus cukup menutup HPP ('.number_format($hpp, 0, ',', '.').') + Komisi ('.number_format($komisi, 0, ',', '.').'). Profit minus, invoice tidak dapat terbit.');
                            }
                        },
                    ]),

                Placeholder::make('profit')
                    ->label('Profit')
                    ->content(fn (Get $get) => self::formatProfit($get)),
            ]),
            // Grid::make(2)->schema([
            //     Placeholder::make('komisi')
            //         ->label('Komisi Publish')
            //         ->content(fn (Get $get) => 'Rp ' . number_format((int) self::parseRupiah($get('harga_publish')), 0, ',', '.')),

            // ]),

            Text::make('profit_rincian')
                ->content(fn (Get $get) => self::rincianProfit($get))
                ->color('gray')
                ->size('sm'),
        ];
    }

    private static function parseRupiah(mixed $value): float
    {
        return (float) str_replace([',', '.'], '', (string) ($value ?? 0));
    }

    private static function formatProfit(Get $get): string
    {
        $hpp = self::parseRupiah($get('harga_beli'));
        $publish = self::parseRupiah($get('harga_publish'));
        $jual = self::parseRupiah($get('harga_jual'));
        $profit = $jual - $hpp - $publish;

        return ($profit < 0 ? '-' : '').'Rp '.number_format(abs($profit), 0, ',', '.');
    }

    private static function rincianProfit(Get $get): string
    {
        $hpp = (int) self::parseRupiah($get('harga_beli'));
        $publish = (int) self::parseRupiah($get('harga_publish'));
        $jual = (int) self::parseRupiah($get('harga_jual'));
        $profit = $jual - $hpp - $publish;

        return sprintf(
            'Profit = Harga Jual − HPP − Komisi = %s − %s − %s = %s',
            number_format($jual, 0, ',', '.'),
            number_format($hpp, 0, ',', '.'),
            number_format($publish, 0, ',', '.'),
            number_format($profit, 0, ',', '.')
        );
    }
}
