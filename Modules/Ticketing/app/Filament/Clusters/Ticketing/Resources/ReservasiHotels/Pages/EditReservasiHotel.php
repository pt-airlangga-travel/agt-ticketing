<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiHotels\Pages;

use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Modules\Ticketing\Filament\Clusters\Ticketing\Concerns\HasClusterSubNavigation;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiHotels\ReservasiHotelResource;
use Modules\Ticketing\Models\TicketingPemesanan;
use Modules\Ticketing\Services\ReservasiHotelService;

class EditReservasiHotel extends EditRecord
{
    use HasClusterSubNavigation;

    protected static string $resource = ReservasiHotelResource::class;

    protected static ?string $title = 'Edit Reservasi Hotel';

    protected static ?string $breadcrumb = 'Edit Reservasi Hotel';

    public function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var TicketingPemesanan $record */
        return app(ReservasiHotelService::class)->update($record, $data);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var TicketingPemesanan $record */
        $record = $this->record;
        $kamar = $record->ticketingKamarHotel;
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

        if ($kamar) {
            $data['vendor_id'] = $kamar->tckt_vendor_id;
            $data['hotel_id'] = $kamar->tckt_hotel_id;
            $data['tipe_kamar'] = $kamar->tipe_kamar;
            $data['lama_menginap'] = $kamar->lama_menginap;
            $data['jumlah_kamar'] = $kamar->jumlah_kamar;
            $data['include_breakfast'] = (bool) $kamar->include_breakfast;
            $data['jadwal_checkin'] = $kamar->jadwal_checkin;
            $data['jadwal_checkout'] = $kamar->jadwal_checkout;
            $data['zona_waktu'] = $kamar->zona_waktu;
        }

        return $data;
    }
}
