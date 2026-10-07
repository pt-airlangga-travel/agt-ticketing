<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiKeretas\Tables;

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
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiKeretas\ReservasiKeretaResource;

class ReservasiKeretasTable
{
    use ReservasiToggleableColumns;

    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->recordAction(null)
            ->defaultSort('ticketing_tiket_kereta.created_at', 'desc')
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

                TextColumn::make('ticketingKereta.nama_kereta')
                    ->label('Kereta')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->toggleable(),

                TextColumn::make('jadwal_berangkat_kereta')
                    ->label('Keberangkatan')
                    ->formatStateUsing(function ($state, $record) {
                        $nama = $record->ticketingBerangkatStasiun?->nama_stasiun;
                        $kode = $record->ticketingBerangkatStasiun?->kode_stasiun;
                        $tanggal = $state ? Carbon::parse($state)->format('d M Y') : null;
                        $jam = $state ? Carbon::parse($state)->format('H:i') : null;
                        $zona = $record->zona_waktu;

                        return new HtmlString(<<<HTML
                            <div class="leading-tight">
                                <div class="font-semibold text-gray-800 dark:text-white">{$nama}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{$kode} • {$tanggal}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{$jam} {$zona}</div>
                            </div>
                        HTML);
                    })
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('jadwal_tiba_kereta')
                    ->label('Kedatangan')
                    ->formatStateUsing(function ($state, $record) {
                        $nama = $record->ticketingTibaStasiun?->nama_stasiun;
                        $kode = $record->ticketingTibaStasiun?->kode_stasiun;
                        $tanggal = $state ? Carbon::parse($state)->format('d M Y') : null;
                        $jam = $state ? Carbon::parse($state)->format('H:i') : null;
                        $zona = $record->zona_waktu_kedatangan;

                        return new HtmlString(<<<HTML
                            <div class="leading-tight">
                                <div class="font-semibold text-gray-800 dark:text-white">{$nama}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{$kode} • {$tanggal}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{$jam} {$zona}</div>
                            </div>
                        HTML);
                    })
                    ->sortable(),

                TextColumn::make('ticketingPemesanan.harga_jual')
                    ->label('Harga Jual')
                    ->formatStateUsing(fn ($state) => 'Rp '.number_format((int) $state, 0, ',', '.'))
                    ->sortable()
                    ->toggleable(),

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

                TextColumn::make('kode_booking_kereta')
                    ->label('Kode Booking Kereta')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('zona_waktu')
                    ->label('Zona Waktu')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('zona_waktu_kedatangan')
                    ->label('Zona Waktu Kedatangan')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('ticketingBerangkatStasiun.kode_stasiun')
                    ->label('Kode Stasiun Berangkat')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('ticketingTibaStasiun.kode_stasiun')
                    ->label('Kode Stasiun Tiba')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                ...self::reservasiCommonToggleableColumns(individualSearch: true),
            ])
            ->filters([
                TanggalKeberangkatanFilter::make()
                    ->filterColumn('ticketing_tiket_kereta.jadwal_berangkat_kereta'),

                TanggalPemesananFilter::make(),

                KategoriPemesananFilter::make(),
            ])
            ->recordActions([
                EditAction::make()
                    ->button()
                    ->hiddenLabel()
                    ->url(fn ($record) => ReservasiKeretaResource::getUrl('edit', ['record' => $record->ticketingPemesanan])),
            ])
            ->toolbarActions([]);
    }
}
