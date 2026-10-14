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
}
