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
            ->recordUrl(null)
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
                        $editUrl = ReservasiHotelResource::getUrl('edit', ['record' => $record->ticketingPemesanan]);

                        return new HtmlString(<<<HTML
                            <div class="leading-tight">
                                <a href="{$editUrl}" class="font-semibold text-primary-600 dark:text-primary-400 hover:underline">{$invoice}</a>
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

                TextColumn::make('ticketingPemesanan.ticketingUnitKerja.nama_unit_kerja')
                    ->label('Unit Kerja Pemesan')
                    ->wrap()
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->toggleable(),

                TextColumn::make('ticketingPemesanan.ticketingUnitKerja.nama_unit_kerja')
                    ->label('Unit Kerja Pemesan')
                    ->wrap()
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->toggleable(),

                TextColumn::make('penumpang_id')
                    ->label('Penumpang ID')
                    ->state(function ($record) {
                        $invoice = $record->ticketingPemesanan?->invoice ?? $record->invoice ?? '-';
                        return collect($record->ticketingPenumpang)->sortBy('id')->values()->map(function ($p, $index) use ($invoice) {
                            return $invoice . '-' . ($index + 1);
                        })->all();
                    })
                    ->listWithLineBreaks()
                    ->toggleable(),

                TextColumn::make('ticketingPenumpang.nama_penumpang')
                    ->label('Penumpang')
                    ->state(function ($record) {
                        return collect($record->ticketingPenumpang)->sortBy('id')->pluck('nama_penumpang')->values()->all();
                    })
                    ->listWithLineBreaks()
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->toggleable(),

                TextColumn::make('pembayar_per_penumpang')
                    ->label('Pembayar')
                    ->state(function ($record) {
                        $items = collect();

                        foreach (collect($record->ticketingPenumpang ?? [])->sortBy('id') as $penumpang) {
                            $bayar = ($penumpang->ticketingPembayaranPenumpang ?? collect())->first();
                            $nama = $bayar?->ticketingPembayar?->nama_pembayar
                                ?? $bayar?->nama_pembayar
                                ?? '-';
                            $unit = $bayar?->ticketingUnitKerja?->nama_unit_kerja;

                            $items->push($unit ? "{$nama} ({$unit})" : $nama);
                        }

                        return $items->all();
                    })
                    ->searchable(isIndividual: true, isGlobal: false, query: function ($query, $search) {
                        $query->whereHas('ticketingPenumpang.ticketingPembayaranPenumpang', function ($query) use ($search) {
                            $query->where(function ($query) use ($search) {
                                $query->whereHas('ticketingPembayar', function ($query) use ($search) {
                                    $query->where('nama_pembayar', 'like', "%{$search}%");
                                })->orWhere('nama_pembayar', 'like', "%{$search}%");
                            });
                        });
                    })
                    ->listWithLineBreaks()
                    ->toggleable(),

                TextColumn::make('unit_kerja_pembayar_per_penumpang')
                    ->label('Unit Kerja Pembayar')
                    ->state(function ($record) {
                        $items = collect();

                        foreach (collect($record->ticketingPenumpang ?? [])->sortBy('id') as $penumpang) {
                            $bayar = ($penumpang->ticketingPembayaranPenumpang ?? collect())->first();
                            $unit = $bayar?->ticketingUnitKerja?->nama_unit_kerja
                                ?? $bayar?->ticketingPembayar?->ticketingUnitKerja?->nama_unit_kerja
                                ?? '-';
                            $items->push($unit);
                        }

                        return $items->all();
                    })
                    ->listWithLineBreaks()
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

                TextColumn::make('harga_jual_per_penumpang')
                    ->label('Harga Jual')
                    ->state(function ($record) {
                        $totalHarga = (int) ($record->ticketingPemesanan?->harga_jual ?? 0);
                        $penumpangs = collect($record->ticketingPenumpang ?? [])->sortBy('id');
                        $count = $penumpangs->count();

                        if ($count <= 1) {
                            return ['Rp ' . number_format($totalHarga, 0, ',', '.')];
                        }

                        $base = intdiv($totalHarga, $count);
                        $remainder = $totalHarga % $count;

                        return $penumpangs->values()->map(function ($p, $index) use ($base, $remainder) {
                            $harga = $base + ($index < $remainder ? 1 : 0);
                            return 'Rp ' . number_format($harga, 0, ',', '.');
                        })->all();
                    })
                    ->listWithLineBreaks()
                    ->toggleable(),

                TextColumn::make('harga_beli_per_penumpang')
                    ->label('Harga NTA (HPP)')
                    ->state(function ($record) {
                        $totalHpp = (int) ($record->ticketingPemesanan?->harga_beli ?? 0);
                        $penumpangs = collect($record->ticketingPenumpang ?? [])->sortBy('id');
                        $count = $penumpangs->count();

                        if ($count <= 1) {
                            return ['Rp ' . number_format($totalHpp, 0, ',', '.')];
                        }

                        $base = intdiv($totalHpp, $count);
                        $remainder = $totalHpp % $count;

                        return $penumpangs->values()->map(function ($p, $index) use ($base, $remainder) {
                            $harga = $base + ($index < $remainder ? 1 : 0);
                            return 'Rp ' . number_format($harga, 0, ',', '.');
                        })->all();
                    })
                    ->listWithLineBreaks()
                    ->toggleable(),

                TextColumn::make('harga_publish_per_penumpang')
                    ->label('Komisi (Publish)')
                    ->state(function ($record) {
                        $totalPublish = (int) ($record->ticketingPemesanan?->harga_publish ?? 0);
                        $penumpangs = collect($record->ticketingPenumpang ?? [])->sortBy('id');
                        $count = $penumpangs->count();

                        if ($count <= 1) {
                            return ['Rp ' . number_format($totalPublish, 0, ',', '.')];
                        }

                        $base = intdiv($totalPublish, $count);
                        $remainder = $totalPublish % $count;

                        return $penumpangs->values()->map(function ($p, $index) use ($base, $remainder) {
                            $harga = $base + ($index < $remainder ? 1 : 0);
                            return 'Rp ' . number_format($harga, 0, ',', '.');
                        })->all();
                    })
                    ->listWithLineBreaks()
                    ->toggleable(),

                TextColumn::make('profit_per_penumpang')
                    ->label('Profit')
                    ->state(function ($record) {
                        $totalJual = (int) ($record->ticketingPemesanan?->harga_jual ?? 0);
                        $totalHpp = (int) ($record->ticketingPemesanan?->harga_beli ?? 0);
                        $totalPublish = (int) ($record->ticketingPemesanan?->harga_publish ?? 0);
                        $totalProfit = $totalJual - $totalHpp - $totalPublish;

                        $penumpangs = collect($record->ticketingPenumpang ?? [])->sortBy('id');
                        $count = $penumpangs->count();

                        if ($count <= 1) {
                            return [($totalProfit < 0 ? '-Rp ' : 'Rp ') . number_format(abs($totalProfit), 0, ',', '.')];
                        }

                        $base = intdiv($totalProfit, $count);
                        $remainder = $totalProfit % $count;

                        return $penumpangs->values()->map(function ($p, $index) use ($base, $remainder) {
                            $profit = $base + ($index < $remainder ? 1 : 0);
                            return ($profit < 0 ? '-Rp ' : 'Rp ') . number_format(abs($profit), 0, ',', '.');
                        })->all();
                    })
                    ->listWithLineBreaks()
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
