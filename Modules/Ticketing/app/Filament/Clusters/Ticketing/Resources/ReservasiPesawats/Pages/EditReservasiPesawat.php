<?php

namespace Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiPesawats\Pages;

use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Modules\Ticketing\Filament\Clusters\Ticketing\Concerns\HasClusterSubNavigation;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\ReservasiPesawats\ReservasiPesawatResource;
use Modules\Ticketing\Models\TicketingTiketPesawat;
use Modules\Ticketing\Services\ReservasiPesawatService;

class EditReservasiPesawat extends EditRecord
{
    use HasClusterSubNavigation;

    protected static string $resource = ReservasiPesawatResource::class;

    protected static ?string $title = 'Edit Reservasi Pesawat';

    protected static ?string $breadcrumb = 'Edit Reservasi Pesawat';

    public function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var TicketingTiketPesawat $record */
        return app(ReservasiPesawatService::class)->update($record, $data);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var TicketingTiketPesawat $record */
        $record = $this->record;
        $pemesanan = $record->ticketingPemesanan;
        $tiket = $record;

        $data['nama_customer'] = $pemesanan?->nama_customer;
        $data['unit_kerja_pemesan'] = $pemesanan?->tckt_unit_kerja_id;
        $data['status_pemesanan'] = $pemesanan?->status_pemesanan;
        $data['lunas'] = (bool) ($pemesanan?->lunas ?? false);
        $data['kategori_pemesanan_id'] = $pemesanan?->tckt_kategori_pemesanan_id;
        $data['tanggal_pemesanan'] = $pemesanan?->tanggal_pemesanan;
        $data['harga_beli'] = $pemesanan?->harga_beli;
        $data['harga_publish'] = $pemesanan?->harga_publish;
        $data['harga_jual'] = $pemesanan?->harga_jual;
        $data['pulang_pergi'] = $pemesanan?->pulang_pergi;
        $data['nama_pembayar'] = $pemesanan?->ticketingPembayaran?->nama_pembayar;
        $data['unit_kerja_pembayar'] = $pemesanan?->ticketingPembayaran?->tckt_unit_kerja_id;

        if ($tiket) {
            $data['maskapai_id'] = $tiket->tckt_maskapai_id;
            $data['vendor_id'] = $tiket->tckt_vendor_id;
            $data['bandara_berangkat_id'] = $tiket->tckt_bandara_berangkat_id;
            $data['bandara_tiba_id'] = $tiket->tckt_bandara_tiba_id;
            $data['nomor_ticket'] = $tiket->nomor_ticket;
            $data['nomor_penerbangan'] = $tiket->nomor_penerbangan;
            $data['kode_booking_pesawat'] = $tiket->kode_booking_pesawat;
            $data['kelas'] = $tiket->kelas;
            $data['jadwal_berangkat_pesawat'] = $tiket->jadwal_berangkat_pesawat;
            $data['jadwal_tiba_pesawat'] = $tiket->jadwal_tiba_pesawat;
            $data['zona_waktu'] = $tiket->zona_waktu;
            $data['zona_waktu_kedatangan'] = $tiket->zona_waktu_kedatangan;

            $detailPulangPergi = json_decode((string) $tiket->detail_pulang_pergi, true) ?: [];
            $data['status_pemesanan_pulang_pergi'] = $detailPulangPergi['status_pemesanan_pulang_pergi'] ?? null;
            $data['detail_pulang_pergi'] = ['segmen' => $this->normalizeSegmenPulang($detailPulangPergi)];
        }

        return $data;
    }

    private function normalizeSegmenPulang(array $detail): array
    {
        if (empty($detail)) {
            return [];
        }

        if (isset($detail['segmen']) && is_array($detail['segmen'])) {
            return array_values($detail['segmen']);
        }

        if (array_is_list($detail) && isset($detail[0]) && is_array($detail[0])) {
            return $detail;
        }

        if (isset($detail['maskapai_id_pulang']) || isset($detail['nomor_ticket_pulang']) || isset($detail['kode_booking_pesawat_pulang'])) {
            $jadwalBerangkat = null;
            if (! empty($detail['tanggal_keberangkatan_pulang'])) {
                $jam = ! empty($detail['jam_keberangkatan_pulang']) ? $detail['jam_keberangkatan_pulang'] : '00:00';
                $jadwalBerangkat = trim($detail['tanggal_keberangkatan_pulang'] . ' ' . $jam);
            }

            $jadwalTiba = null;
            $tglTiba = $detail['tanggal_tiba_pulang'] ?? ($detail['tanggal_keberangkatan_pulang'] ?? null);
            if (! empty($tglTiba)) {
                $jam = ! empty($detail['jam_tiba_pulang']) ? $detail['jam_tiba_pulang'] : '00:00';
                $jadwalTiba = trim($tglTiba . ' ' . $jam);
            }

            return [
                [
                    'maskapai_id' => $detail['maskapai_id_pulang'] ?? null,
                    'nomor_ticket' => $detail['nomor_ticket_pulang'] ?? null,
                    'nomor_penerbangan' => $detail['nomor_penerbangan_pulang'] ?? null,
                    'kode_booking_pesawat' => $detail['kode_booking_pesawat_pulang'] ?? null,
                    'bandara_berangkat_id' => $detail['bandara_berangkat_id_pulang'] ?? null,
                    'jadwal_berangkat_pesawat' => $jadwalBerangkat,
                    'bandara_tiba_id' => $detail['bandara_tiba_id_pulang'] ?? null,
                    'jadwal_tiba_pesawat' => $jadwalTiba,
                    'kelas' => $detail['kelas_pulang'] ?? null,
                    'jenis_penerbangan' => $detail['jenis_penerbangan_pulang'] ?? 'Domestik',
                    'vendor_id' => $detail['vendor_id_pulang'] ?? null,
                ],
            ];
        }

        return [];
    }
}
