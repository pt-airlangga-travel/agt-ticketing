<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiHotels\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Modules\Ticketing\Filament\Clusters\Ticketing\Concerns\HasClusterSubNavigation;
use Modules\Ticketing\Filament\Clusters\Ticketing\Concerns\ListsReservasiByEntity;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiHotels\ReservasiHotelResource;
use Modules\Ticketing\Models\TicketingKamarHotel;

class ListReservasiHotels extends ListRecords
{
    use HasClusterSubNavigation;
    use ListsReservasiByEntity;

    protected static string $resource = ReservasiHotelResource::class;

    protected function getTableQuery(): Builder
    {
        return ReservasiHotelResource::getReservasiHotelListQuery();
    }

    protected function getReservasiAnchorModel(): string
    {
        return TicketingKamarHotel::class;
    }

    protected function getReservasiEagerLoads(): array
    {
        return [
            'ticketingPemesanan.ticketingKategoriPemesanan',
            'ticketingPemesanan.ticketingUnitKerja',
            'ticketingPemesanan.ticketingPembayaran',
            'ticketingVendor',
            'ticketingHotel',
        ];
    }

    protected function getReservasiExportColumns(): array
    {
        return array_merge($this->reservasiBaseExportColumns(), [
            'penumpang' => 'Penumpang',
            'ticketingHotel.nama_hotel' => 'Hotel',
            'jumlah_kamar' => 'Jumlah Kamar',
            'lama_menginap' => 'Lama Menginap',
            'tipe_kamar' => 'Tipe Kamar',
            'jadwal_checkin' => 'Jadwal Check-in',
            'jadwal_checkout' => 'Jadwal Check-out',
            'include_breakfast' => 'Termasuk Sarapan',
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
