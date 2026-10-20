<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiKeretas\Pages;

use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Modules\Ticketing\Filament\Clusters\Ticketing\Concerns\HasClusterSubNavigation;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiKeretas\ReservasiKeretaResource;
use Modules\Ticketing\Models\TicketingPemesanan;
use Modules\Ticketing\Services\ReservasiKeretaService;

class EditReservasiKereta extends EditRecord
{
    use HasClusterSubNavigation;

    protected static string $resource = ReservasiKeretaResource::class;

    protected static ?string $title = 'Edit Reservasi Kereta';

    protected static ?string $breadcrumb = 'Edit Reservasi Kereta';

    public function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var TicketingPemesanan $record */
        return app(ReservasiKeretaService::class)->update($record, $data);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var TicketingPemesanan $record */
        $record = $this->record;
        $tiket = $record->ticketingTiketKereta;
        $pembayaran = $record->ticketingPembayaran;

        $data['nama_customer'] = $record->nama_customer;
        $data['unit_kerja_pemesan'] = $record->tckt_unit_kerja_id;
        $data['status_pemesanan'] = $record->status_pemesanan;
        $data['lunas'] = (bool) ($record->lunas ?? false);
        $data['kategori_pemesanan_id'] = $record->tckt_kategori_pemesanan_id;
        $data['tanggal_pemesanan'] = $record->tanggal_pemesanan;
        $data['harga_beli'] = $record->harga_beli;
        $data['harga_publish'] = $record->harga_publish;
        $data['harga_jual'] = $record->harga_jual;

        $data['nama_pembayar'] = $pembayaran?->nama_pembayar;
        $data['unit_kerja_pembayar'] = $pembayaran?->tckt_unit_kerja_id;

        if ($tiket) {
            $data['vendor_id'] = $tiket->tckt_vendor_id;
            $data['kereta_id'] = $tiket->tckt_kereta_id;
            $data['stasiun_berangkat_id'] = $tiket->tckt_stasiun_berangkat_id;
            $data['stasiun_tiba_id'] = $tiket->tckt_stasiun_tiba_id;
            $data['kode_booking_kereta'] = $tiket->kode_booking_kereta;
            $data['jadwal_berangkat_kereta'] = $tiket->jadwal_berangkat_kereta;
            $data['jadwal_tiba_kereta'] = $tiket->jadwal_tiba_kereta;
            $data['zona_waktu'] = $tiket->zona_waktu;
            $data['zona_waktu_kedatangan'] = $tiket->zona_waktu_kedatangan;
        }

        return $data;
    }
}
