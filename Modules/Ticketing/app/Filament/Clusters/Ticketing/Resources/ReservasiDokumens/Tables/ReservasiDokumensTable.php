<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiDokumens\Tables;

use Carbon\Carbon;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
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

                TextColumn::make('penumpang_id')
                    ->label('Penumpang ID')
                    ->state(function ($record, $livewire = null) {
                        $invoice = $record->ticketingPemesanan?->invoice ?? $record->invoice ?? '-';
                        $all = collect($record->ticketingPenumpang ?? $record->pemilikDokumen ?? [])->sortBy('id')->values();
                        $filtered = self::getFilteredPenumpangsForTable($record, $livewire);

                        return $filtered->map(function ($p) use ($all, $invoice) {
                            $index = $all->search(fn ($item) => (int) $item->id === (int) $p->id);

                            return $invoice . '-' . (($index !== false ? $index : 0) + 1);
                        })->all();
                    })
                    ->listWithLineBreaks()
                    ->toggleable(),

                TextColumn::make('ticketingPenumpang.nama_penumpang')
                    ->label('Pemilik Dokumen')
                    ->state(function ($record, $livewire = null) {
                        return self::getFilteredPenumpangsForTable($record, $livewire)
                            ->pluck('nama_penumpang')
                            ->values()
                            ->all();
                    })
                    ->listWithLineBreaks()
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->toggleable(),

                TextColumn::make('pembayar_per_pemilik')
                    ->label('Pembayar')
                    ->state(function ($record, $livewire = null) {
                        $items = collect();

                        foreach (self::getFilteredPenumpangsForTable($record, $livewire) as $pemilik) {
                            $bayar = ($pemilik->ticketingPembayaranPenumpang ?? collect())->first();
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
                                })->orWhere('nama_pembayar', 'like', "%{$search}%")
                                ->orWhereHas('ticketingUnitKerja', function ($query) use ($search) {
                                    $query->where('nama_unit_kerja', 'like', "%{$search}%");
                                });
                            });
                        });
                    })
                    ->listWithLineBreaks()
                    ->toggleable(),

                TextColumn::make('unit_kerja_pembayar_per_pemilik')
                    ->label('Unit Kerja Pembayar')
                    ->state(function ($record, $livewire = null) {
                        $items = collect();

                        foreach (self::getFilteredPenumpangsForTable($record, $livewire) as $pemilik) {
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
                    ->label('Harga Jual per Pax')
                    ->state(function ($record, $livewire = null) {
                        $totalHarga = (int) ($record->ticketingPemesanan?->harga_jual ?? 0);
                        $allPemiliks = collect($record->ticketingPenumpang ?? $record->pemilikDokumen ?? [])->sortBy('id')->values();
                        $filtered = self::getFilteredPenumpangsForTable($record, $livewire);
                        $count = $allPemiliks->count();

                        if ($count <= 1) {
                            return $filtered->isNotEmpty() ? ['Rp ' . number_format($totalHarga, 0, ',', '.')] : [];
                        }

                        $base = intdiv($totalHarga, $count);
                        $remainder = $totalHarga % $count;

                        return $filtered->map(function ($p) use ($allPemiliks, $base, $remainder) {
                            $index = $allPemiliks->search(fn ($item) => (int) $item->id === (int) $p->id);
                            $idx = $index !== false ? $index : 0;
                            $harga = $base + ($idx < $remainder ? 1 : 0);

                            return 'Rp ' . number_format($harga, 0, ',', '.');
                        })->all();
                    })
                    ->listWithLineBreaks()
                    ->toggleable(),

                TextColumn::make('sudah_dibayar_per_pemilik')
                    ->label('Sudah Dibayar')
                    ->state(function ($record, $livewire = null) {
                        $allPemiliks = collect($record->ticketingPenumpang ?? $record->pemilikDokumen ?? [])->sortBy('id')->values();
                        $filtered = self::getFilteredPenumpangsForTable($record, $livewire);
                        $totalHarga = (int) ($record->ticketingPemesanan?->harga_jual ?? 0);
                        $count = $allPemiliks->count();

                        $hargaPerPax = $count > 1 ? intdiv($totalHarga, $count) : $totalHarga;

                        $pemilikIds = $filtered->pluck('id')->all();
                        $terbayarMap = empty($pemilikIds)
                            ? []
                            : \Modules\Ticketing\Models\TicketingPembayaranPenumpang::query()
                                ->whereIn('tckt_penumpang_id', $pemilikIds)
                                ->groupBy('tckt_penumpang_id')
                                ->selectRaw('tckt_penumpang_id, SUM(jumlah_membayar) as total_terbayar')
                                ->pluck('total_terbayar', 'tckt_penumpang_id')
                                ->map(fn ($v) => (int) $v)
                                ->all();

                        return $filtered->map(function ($p) use ($terbayarMap, $hargaPerPax) {
                            $terbayar = min($terbayarMap[$p->id] ?? 0, $hargaPerPax);

                            return 'Rp ' . number_format($terbayar, 0, ',', '.');
                        })->all();
                    })
                    ->listWithLineBreaks()
                    ->toggleable(),

                TextColumn::make('belum_dibayar_per_pemilik')
                    ->label('Belum Dibayar')
                    ->state(function ($record, $livewire = null) {
                        $allPemiliks = collect($record->ticketingPenumpang ?? $record->pemilikDokumen ?? [])->sortBy('id')->values();
                        $filtered = self::getFilteredPenumpangsForTable($record, $livewire);
                        $totalHarga = (int) ($record->ticketingPemesanan?->harga_jual ?? 0);
                        $count = $allPemiliks->count();

                        $pemilikIds = $filtered->pluck('id')->all();
                        $terbayarMap = empty($pemilikIds)
                            ? []
                            : \Modules\Ticketing\Models\TicketingPembayaranPenumpang::query()
                                ->whereIn('tckt_penumpang_id', $pemilikIds)
                                ->groupBy('tckt_penumpang_id')
                                ->selectRaw('tckt_penumpang_id, SUM(jumlah_membayar) as total_terbayar')
                                ->pluck('total_terbayar', 'tckt_penumpang_id')
                                ->map(fn ($v) => (int) $v)
                                ->all();

                        return $filtered->map(function ($p) use ($allPemiliks, $totalHarga, $count, $terbayarMap) {
                            if ($count <= 1) {
                                $hargaPerPax = $totalHarga;
                            } else {
                                $index = $allPemiliks->search(fn ($item) => (int) $item->id === (int) $p->id);
                                $idx = $index !== false ? $index : 0;
                                $base = intdiv($totalHarga, $count);
                                $remainder = $totalHarga % $count;
                                $hargaPerPax = $base + ($idx < $remainder ? 1 : 0);
                            }
                            $terbayar = min($terbayarMap[$p->id] ?? 0, $hargaPerPax);
                            $belum = max($hargaPerPax - $terbayar, 0);

                            return 'Rp ' . number_format($belum, 0, ',', '.');
                        })->all();
                    })
                    ->listWithLineBreaks()
                    ->toggleable(),

                TextColumn::make('ticketingPemesanan.harga_jual')
                    ->label('Harga Jual Invoice')
                    ->formatStateUsing(fn ($state) => 'Rp '.number_format((int) $state, 0, ',', '.'))
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('ticketingPemesanan.harga_beli')
                    ->label('Harga NTA (HPP)')
                    ->formatStateUsing(fn ($state) => 'Rp '.number_format((int) $state, 0, ',', '.'))
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('ticketingPemesanan.harga_publish')
                    ->label('Komisi')
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

                IconColumn::make('lunas')
                    ->label('Lunas')
                    ->alignCenter()
                    ->boolean()
                    ->listWithLineBreaks()
                    ->state(function ($record, $livewire = null): array {
                        $pemesanan = $record->ticketingPemesanan;
                        $all = collect($record->ticketingPenumpang ?? $record->pemilikDokumen ?? [])->sortBy('id');
                        $penumpangs = self::getFilteredPenumpangsForTable($record, $livewire);

                        if ($all->isEmpty()) {
                            return [(bool) ($pemesanan?->lunas ?? false)];
                        }

                        return $penumpangs->map(function ($p) use ($pemesanan): bool {
                            return (bool) ($pemesanan?->isPenumpangLunas($p->id) ?? false);
                        })->all();
                    })
                    ->tooltip(function ($record, $state): string {
                        return (bool) $state ? 'Lunas' : 'Belum Lunas';
                    })
                    ->searchable(isIndividual: true, isGlobal: false, query: function (Builder $query, string $search): Builder {
                        $searchLower = strtolower(trim($search));
                        $isLunas = in_array($searchLower, ['lunas', 'ya', 'sudah', '1', 'true', 'v', 'ok', 'yes'], true)
                            || (str_starts_with('lunas', $searchLower) && ! str_starts_with('belum', $searchLower))
                            || (str_contains($searchLower, 'lunas') && ! str_contains($searchLower, 'belum'));
                        $isBelum = in_array($searchLower, ['belum', 'belum lunas', 'tidak', '0', 'false', 'x', 'no'], true)
                            || str_starts_with('belum', $searchLower)
                            || str_contains($searchLower, 'belum');

                        if ($isLunas) {
                            return $query->whereHas('ticketingPemesanan', function ($q) {
                                $q->where('lunas', true)
                                    ->orWhere(function ($sub) {
                                        $sub->whereNotNull('lunas_penumpang_ids')
                                            ->where('lunas_penumpang_ids', '!=', '[]')
                                            ->where('lunas_penumpang_ids', '!=', 'null');
                                    });
                            });
                        }

                        if ($isBelum) {
                            return $query->whereHas('ticketingPemesanan', function ($q) {
                                $q->where(function ($sub) {
                                    $sub->where('lunas', false)->orWhereNull('lunas');
                                });
                            });
                        }

                        return $query;
                    })
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

                SelectFilter::make('lunas')
                    ->label('Status Lunas')
                    ->options([
                        '1' => 'Lunas',
                        '0' => 'Belum Lunas',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (! filled($data['value'] ?? null)) {
                            return $query;
                        }

                        if ($data['value'] === '1') {
                            return $query->whereHas('ticketingPemesanan', fn ($q) => $q->where('lunas', true));
                        }

                        return $query->whereHas('ticketingPemesanan', fn ($q) => $q->where(fn ($sub) => $sub->where('lunas', false)->orWhereNull('lunas')));
                    }),
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
