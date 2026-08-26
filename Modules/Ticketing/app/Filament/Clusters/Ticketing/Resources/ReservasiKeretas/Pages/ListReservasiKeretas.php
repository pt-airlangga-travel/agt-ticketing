<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiKeretas\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Modules\Ticketing\Filament\Clusters\Ticketing\Concerns\HasClusterSubNavigation;
use Modules\Ticketing\Filament\Clusters\Ticketing\Concerns\ListsReservasiByEntity;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiKeretas\ReservasiKeretaResource;
use Modules\Ticketing\Models\TicketingTiketKereta;

class ListReservasiKeretas extends ListRecords
{
    use HasClusterSubNavigation;
    use ListsReservasiByEntity;

    protected static string $resource = ReservasiKeretaResource::class;

    protected function getTableQuery(): Builder
    {
        return ReservasiKeretaResource::getReservasiKeretaListQuery();
    }

    protected function getReservasiAnchorModel(): string
    {
        return TicketingTiketKereta::class;
    }

    protected function getReservasiEagerLoads(): array
    {
        return [
            'ticketingPemesanan.ticketingKategoriPemesanan',
            'ticketingPemesanan.ticketingUnitKerja',
            'ticketingPemesanan.ticketingPembayaran',
            'ticketingVendor',
            'ticketingKereta',
            'ticketingBerangkatStasiun',
            'ticketingTibaStasiun',
        ];
    }

    protected function getReservasiExportColumns(): array
    {
        return array_merge($this->reservasiBaseExportColumns(), [
            'penumpang' => 'Penumpang',
            'ticketingKereta.nama_kereta' => 'Kereta',
            'kode_booking_kereta' => 'Kode Booking Kereta',
            'ticketingBerangkatStasiun.kode_stasiun' => 'Kode Stasiun Berangkat',
            'ticketingBerangkatStasiun.nama_stasiun' => 'Stasiun Berangkat',
            'ticketingTibaStasiun.kode_stasiun' => 'Kode Stasiun Tiba',
            'ticketingTibaStasiun.nama_stasiun' => 'Stasiun Tiba',
            'jadwal_berangkat_kereta' => 'Jadwal Berangkat (Kereta)',
            'jadwal_tiba_kereta' => 'Jadwal Tiba (Kereta)',
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->exportReservasiAction(),
            CreateAction::make()
                ->label('+ Tambah')
                ->button()
                ->createAnother(false),
        ];
    }
}
