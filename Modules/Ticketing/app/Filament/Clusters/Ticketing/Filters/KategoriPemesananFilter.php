<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Filters;

use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Modules\Ticketing\Models\TicketingKategoriPemesanan;

class KategoriPemesananFilter extends SelectFilter
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Kategori Pemesanan');

        $this->options(function (): array {
            return TicketingKategoriPemesanan::query()
                ->pluck('nama_kategori', 'id')
                ->all();
        });

        $this->query(function (Builder $query, array $state): Builder {
            $value = $state['value'] ?? null;

            if (blank($value)) {
                return $query;
            }

            return $query->whereHas('ticketingPemesanan', fn (Builder $q) => $q->where('tckt_kategori_pemesanan_id', $value));
        });

        $this->indicateUsing(function (array $state): array {
            if (blank($state['value'] ?? null)) {
                return [];
            }

            return ['Kategori: '.(TicketingKategoriPemesanan::find($state['value'])?->nama_kategori ?? $state['value'])];
        });
    }

    public static function getDefaultName(): ?string
    {
        return 'kategori';
    }
}
