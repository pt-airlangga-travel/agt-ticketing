<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiDokumens\Pages;

use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Modules\Ticketing\Filament\Clusters\Ticketing\Concerns\HasClusterSubNavigation;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiDokumens\ReservasiDokumenResource;
use Modules\Ticketing\Models\TicketingPemesanan;
use Modules\Ticketing\Services\ReservasiDokumenService;

class EditReservasiDokumen extends EditRecord
{
    use HasClusterSubNavigation;

    protected static string $resource = ReservasiDokumenResource::class;

    protected static ?string $title = 'Edit Reservasi Dokumen';

    protected static ?string $breadcrumb = 'Edit Reservasi Dokumen';

    public function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var TicketingPemesanan $record */
        return app(ReservasiDokumenService::class)->update($record, $data);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var TicketingPemesanan $record */
        $record = $this->record;
        $dokumen = $record->ticketingDokumen;
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

        if ($dokumen) {
            $data['vendor_id'] = $dokumen->tckt_vendor_id;
            $data['jenis_dokumen'] = $dokumen->jenis_dokumen;
            $data['keterangan'] = $dokumen->keterangan;
        }

        return $data;
    }
}
