<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Concerns;

use Filament\Tables\Columns\TextColumn;

trait ReservasiToggleableColumns
{
    protected static function reservasiCommonToggleableColumns(array $exclude = [], bool $individualSearch = false): array
    {
        $searchable = $individualSearch
            ? fn (TextColumn $column): TextColumn => $column->searchable(isIndividual: true, isGlobal: false)
            : fn (TextColumn $column): TextColumn => $column->searchable();

        $columns = [
            $searchable(TextColumn::make('ticketingPemesanan.ticketingKategoriPemesanan.nama_kategori'))
                ->label('Kategori')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            $searchable(TextColumn::make('ticketingPemesanan.pulang_pergi'))
                ->label('Pulang Pergi')
                ->formatStateUsing(fn ($state) => $state ? 'Ya' : 'Tidak')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('ticketingPemesanan.harga_beli')
                ->label('Harga Beli')
                ->formatStateUsing(fn ($state) => 'Rp '.number_format((int) $state, 0, ',', '.'))
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('ticketingPemesanan.harga_publish')
                ->label('Komisi')
                ->formatStateUsing(fn ($state) => 'Rp '.number_format((int) $state, 0, ',', '.'))
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('profit_invoice')
                ->label('Profit')
                ->state(function ($record) {
                    $jual = (int) ($record->ticketingPemesanan?->harga_jual ?? 0);
                    $beli = (int) ($record->ticketingPemesanan?->harga_beli ?? 0);
                    $komisi = (int) ($record->ticketingPemesanan?->harga_publish ?? 0);
                    $profit = $jual - $beli - $komisi;

                    return ($profit < 0 ? '-Rp ' : 'Rp ') . number_format(abs($profit), 0, ',', '.');
                })
                ->sortable(query: function ($query, string $direction) {
                    return $query->orderByRaw('(harga_jual - harga_beli - harga_publish) ' . $direction);
                })
                ->toggleable(isToggledHiddenByDefault: true),

            $searchable(TextColumn::make('ticketingVendor.nama_vendor'))
                ->label('Vendor')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            $searchable(TextColumn::make('ticketingVendor.jenis_vendor'))
                ->label('Jenis Vendor')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            $searchable(TextColumn::make('ticketingPemesanan.creator.name'))
                ->label('Dibuat Oleh')
                ->sortable()
                ->toggleable(),
        ];

        return array_values(array_filter(
            $columns,
            fn (TextColumn $column): bool => ! in_array($column->getName(), $exclude, true),
        ));
    }

    public static function getFilteredPenumpangsForTable($record, $livewire = null): \Illuminate\Support\Collection
    {
        $penumpangs = collect($record->ticketingPenumpang ?? $record->pemilikDokumen ?? [])->sortBy('id');

        if (! $livewire) {
            return $penumpangs;
        }

        $searches = method_exists($livewire, 'getTableColumnSearches')
            ? (array) $livewire->getTableColumnSearches()
            : [];

        $searchPenumpang = $searches['ticketingPenumpang.nama_penumpang'] ?? null;
        $searchPembayar = $searches['pembayar_per_penumpang'] ?? $searches['pembayar_per_pemilik'] ?? null;
        $searchLunas = $searches['lunas'] ?? null;

        // 1. Filter nama penumpang per kepala
        if (filled($searchPenumpang)) {
            $penumpangs = $penumpangs->filter(function ($p) use ($searchPenumpang): bool {
                return str_contains(strtolower($p->nama_penumpang ?? ''), strtolower(trim($searchPenumpang)));
            });
        }

        // 2. Filter nama / unit pembayar per kepala
        if (filled($searchPembayar)) {
            $term = strtolower(trim($searchPembayar));
            $penumpangs = $penumpangs->filter(function ($p) use ($term): bool {
                $bayar = ($p->ticketingPembayaranPenumpang ?? collect())->first();
                $nama = $bayar?->ticketingPembayar?->nama_pembayar ?? $bayar?->nama_pembayar ?? '';
                $unit = $bayar?->ticketingUnitKerja?->nama_unit_kerja ?? $bayar?->ticketingPembayar?->ticketingUnitKerja?->nama_unit_kerja ?? '';

                return str_contains(strtolower($nama), $term) || str_contains(strtolower($unit), $term);
            });
        }

        // 3. Filter status lunas per kepala
        if (filled($searchLunas)) {
            $pemesanan = $record->ticketingPemesanan;
            $searchLower = strtolower(trim($searchLunas));
            $isLunasSearch = in_array($searchLower, ['lunas', 'ya', 'sudah', '1', 'true', 'v', 'ok', 'yes'], true)
                || (str_starts_with('lunas', $searchLower) && ! str_starts_with('belum', $searchLower))
                || (str_contains($searchLower, 'lunas') && ! str_contains($searchLower, 'belum'));
            $isBelumSearch = in_array($searchLower, ['belum', 'belum lunas', 'tidak', '0', 'false', 'x', 'no'], true)
                || str_starts_with('belum', $searchLower)
                || str_contains($searchLower, 'belum');

            if ($isLunasSearch) {
                $penumpangs = $penumpangs->filter(function ($p) use ($pemesanan): bool {
                    return (bool) ($pemesanan?->isPenumpangLunas($p->id) ?? false);
                });
            } elseif ($isBelumSearch) {
                $penumpangs = $penumpangs->filter(function ($p) use ($pemesanan): bool {
                    return ! ($pemesanan?->isPenumpangLunas($p->id) ?? false);
                });
            }
        }

        return $penumpangs->values();
    }
}
