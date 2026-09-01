<?php

namespace Modules\Ticketing\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Ticketing\Models\TicketingPembayaran;
use Modules\Ticketing\Models\TicketingPemesanan;
use Modules\Ticketing\Models\TicketingTiketPesawat;

class ReservasiPesawatService extends ReservasiService
{
    public function create(array $data): TicketingTiketPesawat
    {
        return DB::transaction(function () use ($data) {
            $segmenPulang = $this->segmenPulang($data['detail_pulang_pergi']['segmen'] ?? []);

            $pemesanan = TicketingPemesanan::create([
                'invoice' => $this->generateInvoiceNumber('1'),
                'nama_customer' => $data['nama_customer'] ?? null,
                'tckt_kategori_pemesanan_id' => $data['kategori_pemesanan_id'] ?? null,
                'tckt_unit_kerja_id' => $data['unit_kerja_pemesan'] ?? null,
                'status_pemesanan' => $data['status_pemesanan'] ?? null,
                'pulang_pergi' => (int) ($data['pulang_pergi'] ?? 0),
                'tanggal_pemesanan' => $this->toDate($data['tanggal_pemesanan'] ?? null),
                'harga_beli' => $this->toInteger($data['harga_beli'] ?? null),
                'harga_publish' => $this->toInteger($data['harga_publish'] ?? null),
                'harga_jual' => $this->toInteger($data['harga_jual'] ?? null),
            ]);

            TicketingPembayaran::create([
                'tckt_unit_kerja_id' => $data['unit_kerja_pembayar'] ?? null,
                'tckt_pemesanan_id' => $pemesanan->id,
                'nama_pembayar' => $data['nama_pembayar'] ?? null,
            ]);

            $tiketData = [
                'tckt_pemesanan_id' => $pemesanan->id,
                'tckt_maskapai_id' => $data['maskapai_id'] ?? null,
                'tckt_vendor_id' => $data['vendor_id'] ?? null,
                'tckt_bandara_berangkat_id' => $data['bandara_berangkat_id'] ?? null,
                'tckt_bandara_tiba_id' => $data['bandara_tiba_id'] ?? null,
                'nomor_ticket' => $data['nomor_ticket'] ?? null,
                'nomor_penerbangan' => $data['nomor_penerbangan'] ?? null,
                'kode_booking_pesawat' => $data['kode_booking_pesawat'] ?? null,
                'kelas' => $data['kelas'] ?? null,
                'jenis_penerbangan' => $data['jenis_penerbangan'] ?? null,
                'jadwal_berangkat_pesawat' => $this->normalizeDateTime($data['jadwal_berangkat_pesawat'] ?? null),
                'jadwal_tiba_pesawat' => $this->normalizeDateTime($data['jadwal_tiba_pesawat'] ?? null),
                'zona_waktu' => $data['zona_waktu'] ?? null,
                'zona_waktu_kedatangan' => $data['zona_waktu_kedatangan'] ?? null,
                'detail_pulang_pergi' => $this->detailPulangPergi($data, $segmenPulang),
            ];

            $tiket = TicketingTiketPesawat::create($this->filterTiketColumns($tiketData));

            return $tiket;
        });
    }

    public function update(TicketingTiketPesawat $tiket, array $data): TicketingTiketPesawat
    {
        return DB::transaction(function () use ($tiket, $data) {
            $segmenPulang = $this->segmenPulang($data['detail_pulang_pergi']['segmen'] ?? []);

            $pemesanan = $tiket->ticketingPemesanan;

            $pemesanan->update([
                'nama_customer' => $data['nama_customer'] ?? $pemesanan->nama_customer,
                'tckt_kategori_pemesanan_id' => $data['kategori_pemesanan_id'] ?? $pemesanan->tckt_kategori_pemesanan_id,
                'tckt_unit_kerja_id' => $data['unit_kerja_pemesan'] ?? $pemesanan->tckt_unit_kerja_id,
                'status_pemesanan' => $data['status_pemesanan'] ?? $pemesanan->status_pemesanan,
                'pulang_pergi' => (int) ($data['pulang_pergi'] ?? $pemesanan->pulang_pergi),
                'tanggal_pemesanan' => $this->toDate($data['tanggal_pemesanan'] ?? $pemesanan->tanggal_pemesanan),
                'harga_beli' => $this->toInteger($data['harga_beli'] ?? $pemesanan->harga_beli),
                'harga_publish' => $this->toInteger($data['harga_publish'] ?? $pemesanan->harga_publish),
                'harga_jual' => $this->toInteger($data['harga_jual'] ?? $pemesanan->harga_jual),
            ]);

            $pembayaran = $pemesanan->ticketingPembayaran;
            $dataPembayaran = [
                'tckt_unit_kerja_id' => $data['unit_kerja_pembayar'] ?? $pembayaran?->tckt_unit_kerja_id,
                'nama_pembayar' => $data['nama_pembayar'] ?? $pembayaran?->nama_pembayar,
            ];
            if ($pembayaran) {
                $pembayaran->update($dataPembayaran);
            } else {
                $dataPembayaran['tckt_pemesanan_id'] = $pemesanan->id;
                TicketingPembayaran::create($dataPembayaran);
            }

            $tiketData = [
                'tckt_maskapai_id' => $data['maskapai_id'] ?? $tiket->tckt_maskapai_id,
                'tckt_vendor_id' => $data['vendor_id'] ?? $tiket->tckt_vendor_id,
                'tckt_bandara_berangkat_id' => $data['bandara_berangkat_id'] ?? $tiket->tckt_bandara_berangkat_id,
                'tckt_bandara_tiba_id' => $data['bandara_tiba_id'] ?? $tiket->tckt_bandara_tiba_id,
                'nomor_ticket' => $data['nomor_ticket'] ?? $tiket->nomor_ticket,
                'nomor_penerbangan' => $data['nomor_penerbangan'] ?? $tiket->nomor_penerbangan,
                'kode_booking_pesawat' => $data['kode_booking_pesawat'] ?? $tiket->kode_booking_pesawat,
                'kelas' => $data['kelas'] ?? $tiket->kelas,
                'jenis_penerbangan' => $data['jenis_penerbangan'] ?? $tiket->jenis_penerbangan,
                'jadwal_berangkat_pesawat' => $this->normalizeDateTime($data['jadwal_berangkat_pesawat'] ?? null)
                    ?? $tiket->jadwal_berangkat_pesawat,
                'jadwal_tiba_pesawat' => $this->normalizeDateTime($data['jadwal_tiba_pesawat'] ?? null)
                    ?? $tiket->jadwal_tiba_pesawat,
                'zona_waktu' => $data['zona_waktu'] ?? $tiket->zona_waktu,
                'zona_waktu_kedatangan' => $data['zona_waktu_kedatangan'] ?? $tiket->zona_waktu_kedatangan,
                'detail_pulang_pergi' => $this->detailPulangPergi($data, $segmenPulang),
            ];

            $tiket->update($this->filterTiketColumns($tiketData));

            return $tiket;
        });
    }

    private function segmenPulang(mixed $rows): array
    {
        if (! is_array($rows)) {
            return [];
        }

        return collect($rows)
            ->filter()
            ->values()
            ->map(fn (array $row) => [
                'maskapai_id' => $row['maskapai_id'] ?? null,
                'vendor_id' => $row['vendor_id'] ?? null,
                'bandara_berangkat_id' => $row['bandara_berangkat_id'] ?? null,
                'bandara_tiba_id' => $row['bandara_tiba_id'] ?? null,
                'nomor_ticket' => $row['nomor_ticket'] ?? null,
                'nomor_penerbangan' => $row['nomor_penerbangan'] ?? null,
                'kode_booking_pesawat' => $row['kode_booking_pesawat'] ?? null,
                'kelas' => $row['kelas'] ?? null,
                'jenis_penerbangan' => $row['jenis_penerbangan'] ?? null,
                'jadwal_berangkat_pesawat' => $this->normalizeDateTime($row['jadwal_berangkat_pesawat'] ?? null),
                'jadwal_tiba_pesawat' => $this->normalizeDateTime($row['jadwal_tiba_pesawat'] ?? null),
                'zona_waktu' => $row['zona_waktu'] ?? null,
                'zona_waktu_kedatangan' => $row['zona_waktu_kedatangan'] ?? null,
            ])
            ->all();
    }

    private function detailPulangPergi(array $data, array $segmenPulang): ?string
    {
        if ((int) ($data['pulang_pergi'] ?? 0) !== 1 || empty($segmenPulang)) {
            return null;
        }

        return json_encode([
            'status_pemesanan_pulang_pergi' => $data['status_pemesanan_pulang_pergi'] ?? null,
            'segmen' => $segmenPulang,
        ]);
    }

    /**
     * Filter kolom yang belum ada di DB (mis. migrasi tertunda di production).
     */
    private function filterTiketColumns(array $data): array
    {
        // Daftar kolom yang mungkin belum ada jika migrasi tertunda
        $maybeMissing = ['jenis_penerbangan'];

        foreach ($maybeMissing as $col) {
            if (array_key_exists($col, $data) && ! Schema::hasColumn('ticketing_tiket_pesawat', $col)) {
                unset($data[$col]);
            }
        }

        return $data;
    }
}
