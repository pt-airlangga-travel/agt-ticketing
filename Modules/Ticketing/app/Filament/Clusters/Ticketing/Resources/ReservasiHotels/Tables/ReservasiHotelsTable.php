<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiHotels\Tables;

use Carbon\Carbon;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Modules\Ticketing\Filament\Clusters\Ticketing\Concerns\ReservasiToggleableColumns;
use Modules\Ticketing\Filament\Clusters\Ticketing\Filters\KategoriPemesananFilter;
use Modules\Ticketing\Filament\Clusters\Ticketing\Filters\TanggalKeberangkatanFilter;
use Modules\Ticketing\Filament\Clusters\Ticketing\Filters\TanggalPemesananFilter;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiHotels\ReservasiHotelResource;

class ReservasiHotelsTable
{
    use ReservasiToggleableColumns;

    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->recordAction(null)
            ->defaultSort('ticketing_kamar_hotel.created_at', 'desc')
            ->searchable(false)
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->reorderableColumns()
            ->columns([
                TextColumn::make('#')->rowIndex(),

                TextColumn::make('ticketingPemesanan.invoice')
                    ->label('Invoice')
                    ->formatStateUsing(function ($state, $record) {
                        $invoice = $record->ticketingPemesanan?->invoice;
                        $tanggal = $record->ticketingPemesanan?->tanggal_pemesanan
                            ? Carbon::parse($record->ticketingPemesanan->tanggal_pemesanan)->format('d M Y')
                            : null;

                        return new HtmlString(<<<HTML
                            <div class="leading-tight">
                                <div class="font-semibold text-gray-800 dark:text-white">{$invoice}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{$tanggal}</div>
                            </div>
                        HTML);
                    })
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('ticketingPemesanan.nama_customer')
                    ->label('Pemesan')
                    ->wrap()
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->toggleable(),

                TextColumn::make('penumpang_id')
                    ->label('Penumpang ID')
                    ->state(function ($record) {
                        $invoice = $record->ticketingPemesanan?->invoice ?? $record->invoice ?? '-';
                        return collect($record->ticketingPenumpang)->values()->map(function ($p, $index) use ($invoice) {
                            return $invoice . '-' . ($index + 1);
                        })->all();
                    })
                    ->listWithLineBreaks()
                    ->toggleable(),

                TextColumn::make('ticketingPenumpang.nama_penumpang')
                    ->label('Penumpang')
                    ->badge()
                    ->listWithLineBreaks()
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('ticketingPemesanan.ticketingPembayaran.nama_pembayar')
                    ->label('Pembayar')
                    ->wrap()
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('ticketingPemesanan.ticketingUnitKerja.nama_unit_kerja')
                    ->label('Unit Kerja')
                    ->wrap()
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->toggleable(),

                TextColumn::make('ticketingHotel.nama_hotel')
                    ->label('Hotel')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->toggleable(),

                TextColumn::make('jadwal_checkin')
                    ->label('Check-in')
                    ->formatStateUsing(function ($state) {
                        if (! $state) {
                            return '-';
                        }

                        $tanggal = Carbon::parse($state)->format('d M Y');
                        $jam = Carbon::parse($state)->format('H:i');

                        return new HtmlString(<<<HTML
                            <div class="leading-tight">
                                <div class="font-semibold text-gray-800 dark:text-white">{$tanggal}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{$jam}</div>
                            </div>
                        HTML);
                    })
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('jadwal_checkout')
                    ->label('Check-out')
                    ->formatStateUsing(function ($state) {
                        if (! $state) {
                            return '-';
                        }

                        $tanggal = Carbon::parse($state)->format('d M Y');
                        $jam = Carbon::parse($state)->format('H:i');

                        return new HtmlString(<<<HTML
                            <div class="leading-tight">
                                <div class="font-semibold text-gray-800 dark:text-white">{$tanggal}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{$jam}</div>
                            </div>
                        HTML);
                    })
                    ->sortable(),

                TextColumn::make('ticketingPemesanan.harga_jual')
                    ->label('Harga Jual')
                    ->formatStateUsing(fn ($state) => 'Rp '.number_format((int) $state, 0, ',', '.'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('ticketingPemesanan.status_pemesanan')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Confirmed' => 'success',
                        'Canceled', 'Refund' => 'danger',
                        default => 'info',
                    })
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('jumlah_kamar')
                    ->label('Jumlah Kamar')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('lama_menginap')
                    ->label('Lama Menginap')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('tipe_kamar')
                    ->label('Tipe Kamar')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('include_breakfast')
                    ->label('Termasuk Sarapan')
                    ->formatStateUsing(fn ($state) => $state ? 'Ya' : 'Tidak')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('zona_waktu')
                    ->label('Zona Waktu')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('ticketingHotel.bintang')
                    ->label('Bintang Hotel')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('ticketingHotel.kota')
                    ->label('Kota Hotel')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('ticketingHotel.telepon')
                    ->label('Telepon Hotel')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('ticketingHotel.email')
                    ->label('Email Hotel')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('ticketingHotel.alamat')
                    ->label('Alamat Hotel')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                ...self::reservasiCommonToggleableColumns(['ticketingPemesanan.pulang_pergi'], individualSearch: true),
            ])
            ->filters([
                TanggalKeberangkatanFilter::make()
                    ->filterColumn('ticketing_kamar_hotel.jadwal_checkin')
                    ->label('Periode Tanggal Check-in'),

                TanggalPemesananFilter::make(),

                KategoriPemesananFilter::make(),
            ])
            ->recordActions([
                EditAction::make()
                    ->button()
                    ->hiddenLabel()
                    ->url(fn ($record) => ReservasiHotelResource::getUrl('edit', ['record' => $record->ticketingPemesanan])),
            ])
            ->toolbarActions([]);
    }
}
