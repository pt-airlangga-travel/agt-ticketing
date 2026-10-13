<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiDokumens\Tables;

use Carbon\Carbon;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Modules\Ticketing\Filament\Clusters\Ticketing\Concerns\ReservasiToggleableColumns;
use Modules\Ticketing\Filament\Clusters\Ticketing\Filters\TanggalPemesananFilter;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiDokumens\ReservasiDokumenResource;

class ReservasiDokumensTable
{
    use ReservasiToggleableColumns;

    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->recordAction(null)
            ->recordUrl(null)
            ->defaultSort('created_at', 'desc')
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
                        $editUrl = ReservasiDokumenResource::getUrl('edit', ['record' => $record->ticketingPemesanan]);

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

                TextColumn::make('nama_customer')
                    ->label('Pemesan')
                    ->wrap()
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->toggleable(),

                TextColumn::make('ticketingUnitKerja.nama_unit_kerja')
                    ->label('Unit Kerja Pemesan')
                    ->wrap()
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->toggleable(),

                TextColumn::make('ticketingUnitKerja.nama_unit_kerja')
                    ->label('Unit Kerja Pemesan')
                    ->wrap()
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->toggleable(),

                TextColumn::make('penumpang_id')
                    ->label('Penumpang ID')
                    ->state(function ($record) {
                        $invoice = $record->ticketingPemesanan?->invoice ?? $record->invoice ?? '-';
                        return collect($record->pemilikDokumen)->sortBy('id')->values()->map(function ($p, $index) use ($invoice) {
                            return $invoice . '-' . ($index + 1);
                        })->all();
                    })
                    ->listWithLineBreaks()
                    ->toggleable(),

                TextColumn::make('ticketingPenumpang.nama_penumpang')
                    ->label('Pemilik Dokumen')
                    ->state(function ($record) {
                        return collect($record->pemilikDokumen)->sortBy('id')->pluck('nama_penumpang')->values()->all();
                    })
                    ->listWithLineBreaks()
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->toggleable(),

                TextColumn::make('pembayar_per_pemilik')
                    ->label('Pembayar')
                    ->state(function ($record) {
                        $items = collect();

                        foreach (collect($kolom = $record->pemilikDokumen ?? [])->sortBy('id') as $pemilik) {
                            $bayar = ($pemilik->ticketingPembayaranPenumpang ?? collect())->first();
                            $nama = $bayar?->ticketingPembayar?->nama_pembayar
                                ?? $bayar?->nama_pembayar
                                ?? '-';
                            $unit = $bayar?->ticketingUnitKerja?->nama_unit_kerja;

                            $items->push($unit ? "{$nama} ({$unit})" : $nama);
                        }

                        return $items->all();
                    })
                    ->listWithLineBreaks()
                    ->toggleable(),

                TextColumn::make('unit_kerja_pembayar_per_pemilik')
                    ->label('Unit Kerja Pembayar')
                    ->state(function ($record) {
                        $items = collect();

                        foreach (collect($record->pemilikDokumen ?? [])->sortBy('id') as $pemilik) {
                            $bayar = ($pemilik->ticketingPembayaranPenumpang ?? collect())->first();
                            $unit = $bayar?->ticketingUnitKerja?->nama_unit_kerja
                                ?? $bayar?->ticketingPembayar?->ticketingUnitKerja?->nama_unit_kerja
                                ?? '-';
                            $items->push($unit);
                        }

                        return $items->all();
                    })
                    ->listWithLineBreaks()
                    ->toggleable(),

                TextColumn::make('jenis_dokumen')
                    ->label('Jenis Dokumen')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->toggleable(),

                TextColumn::make('harga_jual_per_pemilik')
                    ->label('Harga Jual')
                    ->state(function ($record) {
                        $totalHarga = (int) ($record->ticketingPemesanan?->harga_jual ?? 0);
                        $pemiliks = collect($record->pemilikDokumen ?? [])->sortBy('id');
                        $count = $pemiliks->count();

                        if ($count <= 1) {
                            return ['Rp ' . number_format($totalHarga, 0, ',', '.')];
                        }

                        $base = intdiv($totalHarga, $count);
                        $remainder = $totalHarga % $count;

                        return $pemiliks->values()->map(function ($p, $index) use ($base, $remainder) {
                            $harga = $base + ($index < $remainder ? 1 : 0);
                            return 'Rp ' . number_format($harga, 0, ',', '.');
                        })->all();
                    })
                    ->listWithLineBreaks()
                    ->toggleable(),

                TextColumn::make('harga_beli_per_pemilik')
                    ->label('Harga NTA (HPP)')
                    ->state(function ($record) {
                        $totalHpp = (int) ($record->ticketingPemesanan?->harga_beli ?? 0);
                        $pemiliks = collect($record->pemilikDokumen ?? [])->sortBy('id');
                        $count = $pemiliks->count();

                        if ($count <= 1) {
                            return ['Rp ' . number_format($totalHpp, 0, ',', '.')];
                        }

                        $base = intdiv($totalHpp, $count);
                        $remainder = $totalHpp % $count;

                        return $pemiliks->values()->map(function ($p, $index) use ($base, $remainder) {
                            $harga = $base + ($index < $remainder ? 1 : 0);
                            return 'Rp ' . number_format($harga, 0, ',', '.');
                        })->all();
                    })
                    ->listWithLineBreaks()
                    ->toggleable(),

                TextColumn::make('harga_publish_per_pemilik')
                    ->label('Komisi (Publish)')
                    ->state(function ($record) {
                        $totalPublish = (int) ($record->ticketingPemesanan?->harga_publish ?? 0);
                        $pemiliks = collect($record->pemilikDokumen ?? [])->sortBy('id');
                        $count = $pemiliks->count();

                        if ($count <= 1) {
                            return ['Rp ' . number_format($totalPublish, 0, ',', '.')];
                        }

                        $base = intdiv($totalPublish, $count);
                        $remainder = $totalPublish % $count;

                        return $pemiliks->values()->map(function ($p, $index) use ($base, $remainder) {
                            $harga = $base + ($index < $remainder ? 1 : 0);
                            return 'Rp ' . number_format($harga, 0, ',', '.');
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

                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                ...self::reservasiCommonToggleableColumns(['ticketingPemesanan.pulang_pergi'], individualSearch: true),
            ])
            ->filters([
                TanggalPemesananFilter::make(),
            ])
            ->recordActions([
                EditAction::make()
                    ->button()
                    ->hiddenLabel()
                    ->url(fn ($record) => ReservasiDokumenResource::getUrl('edit', ['record' => $record->ticketingPemesanan])),
            ])
            ->toolbarActions([]);
    }
}
