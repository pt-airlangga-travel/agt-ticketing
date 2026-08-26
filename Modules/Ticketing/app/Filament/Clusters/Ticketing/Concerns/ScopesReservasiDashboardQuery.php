<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiDokumens\ReservasiDokumenResource;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiHotels\ReservasiHotelResource;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiKeretas\ReservasiKeretaResource;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiPesawats\ReservasiPesawatResource;
use Modules\Ticketing\Models\TicketingPemesanan;

trait ScopesReservasiDashboardQuery
{
    /**
     * Query pemesanan sesuai hak akses user:
     * - punya akses view pada minimal satu jenis reservasi -> lihat semua data jenis itu
     * - selain itu -> hanya reservasi yang dibuatnya sendiri
     */
    protected function getReservasiDashboardQuery(): Builder
    {
        $query = TicketingPemesanan::query();

        $user = auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        $canViewAll = [
            ReservasiPesawatResource::class,
            ReservasiKeretaResource::class,
            ReservasiHotelResource::class,
            ReservasiDokumenResource::class,
        ];

        $hasAnyView = false;

        foreach ($canViewAll as $resource) {
            if ($user->canAccess($resource::getRbacPermissionNames()['view'])) {
                $hasAnyView = true;
                break;
            }
        }

        if (! $hasAnyView) {
            $query->where('created_by', $user->id);
        }

        return $query;
    }
}
