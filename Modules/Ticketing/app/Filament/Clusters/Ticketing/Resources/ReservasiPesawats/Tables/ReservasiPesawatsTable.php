<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiPesawats\Tables;

use Carbon\Carbon;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Modules\Ticketing\Filament\Clusters\Ticketing\Concerns\ReservasiToggleableColumns;
use Modules\Ticketing\Filament\Clusters\Ticketing\Filters\KategoriPemesananFilter;
use Modules\Ticketing\Filament\Clusters\Ticketing\Filters\TanggalKeberangkatanFilter;
use Modules\Ticketing\Filament\Clusters\Ticketing\Filters\TanggalPemesananFilter;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiPesawats\ReservasiPesawatResource;

class ReservasiPesawatsTable
{
    use ReservasiToggleableColumns;

    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->recordAction(null)
            ->defaultSort('ticketing_tiket_pesawat.created_at', 'desc')
            ->searchable(false)
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->reorderableColumns()
            ->columns([
                TextColumn::make('#')
                    ->rowIndex(),

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

                TextColumn::make('kode_booking_pesawat')
                    ->label('Kode Booking')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->toggleable(),

                TextColumn::make('ticketingPemesanan.nama_customer')
                    ->label('Pemesan')
                    ->wrap()
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->toggleable(),

                TextColumn::make('penumpang_id')
                    ->label('Penumpang ID edit')
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
                    ->listWithLineBreaks()
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->toggleable(),

                TextColumn::make('pembayar_per_penumpang')
                    ->label('Pembayar')
                    ->state(function ($record) {
                        $items = collect();

                        foreach ($record->ticketingPenumpang ?? [] as $penumpang) {
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

                TextColumn::make('ticketingMaskapai.nama_maskapai')
                    ->label('Maskapai')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->toggleable(),

                TextColumn::make('ticketingBerangkatBandara.nama_bandara')
                    ->label('Bandara Berangkat')
                    ->formatStateUsing(function ($state, $record) {
                        $nama = $record->ticketingBerangkatBandara?->nama_bandara;
                        $kode = $record->ticketingBerangkatBandara?->kode_bandara;

                        return new HtmlString(<<<HTML
                            <div class="leading-tight">
                                <div class="font-semibold text-gray-800 dark:text-white">{$nama}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{$kode}</div>
                            </div>
                        HTML);
                    })
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->toggleable(),

                TextColumn::make('jadwal_berangkat_pesawat')
                    ->label('Jadwal Berangkat')
                    ->formatStateUsing(function ($state, $record) {
                        $tanggal = $state ? Carbon::parse($state)->format('d M Y') : null;
                        $jam = $state ? Carbon::parse($state)->format('H:i') : null;
                        $zona = $record->zona_waktu;

                        return new HtmlString(<<<HTML
                            <div class="leading-tight">
                                <div class="text-gray-800 dark:text-white">{$tanggal}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{$jam} {$zona}</div>
                            </div>
                        HTML);
                    })
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('ticketingTibaBandara.nama_bandara')
                    ->label('Bandara Tiba')
                    ->formatStateUsing(function ($state, $record) {
                        $nama = $record->ticketingTibaBandara?->nama_bandara;
                        $kode = $record->ticketingTibaBandara?->kode_bandara;

                        return new HtmlString(<<<HTML
                            <div class="leading-tight">
                                <div class="font-semibold text-gray-800 dark:text-white">{$nama}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{$kode}</div>
                            </div>
                        HTML);
                    })
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->toggleable(),

                TextColumn::make('jadwal_tiba_pesawat')
                    ->label('Jadwal Tiba')
                    ->formatStateUsing(function ($state, $record) {
                        $tanggal = $state ? Carbon::parse($state)->format('d M Y') : null;
                        $jam = $state ? Carbon::parse($state)->format('H:i') : null;
                        $zona = $record->zona_waktu_kedatangan;

                        return new HtmlString(<<<HTML
                            <div class="leading-tight">
                                <div class="text-gray-800 dark:text-white">{$tanggal}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{$jam} {$zona}</div>
                            </div>
                        HTML);
                    })
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

                TextColumn::make('nomor_ticket')
                    ->label('No. Tiket')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('nomor_penerbangan')
                    ->label('No. Penerbangan')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('kelas')
                    ->label('Kelas')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('jenis_penerbangan')
                    ->label('Jenis Penerbangan')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Internasional' => 'info',
                        default => 'success',
                    })
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable()
                    ->toggleable(),

                // TextColumn::make('detail_pulang_pergi')
                //     ->label('Detail Pulang Pergi')
                //     ->searchable(isIndividual: true, isGlobal: false)
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('ticketingBerangkatBandara.kode_bandara')
                    ->label('Kode Bandara Berangkat')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('ticketingTibaBandara.kode_bandara')
                    ->label('Kode Bandara Tiba')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('ticketingPemesanan.harga_jual')
                    ->label('Harga Jual')
                    ->formatStateUsing(fn ($state) => 'Rp '.number_format((int) $state, 0, ',', '.'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                ...self::reservasiCommonToggleableColumns(individualSearch: true),
            ])
            // ->defaultGroup(
            //     Group::make('tckt_pemesanan_id')
            //         ->label('Invoice')
            //         ->getTitleFromRecordUsing(function ($record) {
            //             $pemesanan = $record->ticketingPemesanan;

            //             return $pemesanan
            //                 ? "{$pemesanan->invoice} — {$pemesanan->nama_customer}"
            //                 : 'Tanpa Invoice';
            //         })
            //         ->collapsible()
            // )
            ->modifyQueryUsing(function ($query) use ($table) {
                $searchPenumpang = $table->getLivewire()->getTableColumnSearches()['ticketingPenumpang.nama_penumpang'] ?? null;
                $searchPembayar = $table->getLivewire()->getTableColumnSearches()['pembayar_per_penumpang'] ?? null;

                return $query->with([
                    'ticketingPemesanan.ticketingPembayaran',
                    'ticketingPenumpang' => function ($query) use ($searchPenumpang, $searchPembayar) {
                        $query->with([
                            'ticketingPembayaranPenumpang.ticketingPembayar',
                            'ticketingPembayaranPenumpang.ticketingUnitKerja',
                        ]);

                        if (filled($searchPenumpang)) {
                            $query->where('nama_penumpang', 'like', "%{$searchPenumpang}%");
                        }

                        if (filled($searchPembayar)) {
                            $query->whereHas('ticketingPembayaranPenumpang', function ($query) use ($searchPembayar) {
                                $query->where(function ($query) use ($searchPembayar) {
                                    $query->whereHas('ticketingPembayar', function ($query) use ($searchPembayar) {
                                        $query->where('nama_pembayar', 'like', "%{$searchPembayar}%");
                                    })->orWhere('nama_pembayar', 'like', "%{$searchPembayar}%");
                                });
                            });
                        }
                    },
                ]);
            })
            ->filters([
                TanggalKeberangkatanFilter::make()
                    ->filterColumn('ticketing_tiket_pesawat.jadwal_berangkat_pesawat'),

                TanggalPemesananFilter::make(),

                KategoriPemesananFilter::make(),
            ])
            ->recordActions([
                EditAction::make()
                    ->button()
                    ->hiddenLabel()
                    ->url(fn ($record) => ReservasiPesawatResource::getUrl('edit', ['record' => $record])),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
