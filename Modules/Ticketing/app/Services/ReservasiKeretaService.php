<?php

namespace Modules\Ticketing\Services;

use Illuminate\Support\Facades\DB;
use Modules\Ticketing\Models\TicketingPembayaran;
use Modules\Ticketing\Models\TicketingPemesanan;
use Modules\Ticketing\Models\TicketingTiketKereta;

class ReservasiKeretaService extends ReservasiService
{
    public function create(array $data): TicketingPemesanan
    {
        return DB::transaction(function () use ($data) {
            $pemesanan = TicketingPemesanan::create([
                'invoice' => $this->generateInvoiceNumber('2'),
                'nama_customer' => $data['nama_customer'] ?? null,
                'tckt_kategori_pemesanan_id' => $data['kategori_pemesanan_id'] ?? null,
                'tckt_unit_kerja_id' => $data['unit_kerja_pemesan'] ?? null,
                'status_pemesanan' => $data['status_pemesanan'] ?? null,
                'pulang_pergi' => 0,
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

            TicketingTiketKereta::create([
                'tckt_pemesanan_id' => $pemesanan->id,
                'tckt_vendor_id' => $data['vendor_id'] ?? null,
                'tckt_kereta_id' => $data['kereta_id'] ?? null,
                'tckt_stasiun_berangkat_id' => $data['stasiun_berangkat_id'] ?? null,
                'tckt_stasiun_tiba_id' => $data['stasiun_tiba_id'] ?? null,
                'kode_booking_kereta' => $data['kode_booking_kereta'] ?? null,
                'jadwal_berangkat_kereta' => $this->normalizeDateTime($data['jadwal_berangkat_kereta'] ?? null),
                'jadwal_tiba_kereta' => $this->normalizeDateTime($data['jadwal_tiba_kereta'] ?? null),
                'zona_waktu' => $data['zona_waktu'] ?? null,
                'zona_waktu_kedatangan' => $data['zona_waktu_kedatangan'] ?? null,
            ]);

            return $pemesanan;
        });
    }

    public function update(TicketingPemesanan $pemesanan, array $data): TicketingPemesanan
    {
        return DB::transaction(function () use ($pemesanan, $data) {
            $pemesanan->update([
                'nama_customer' => $data['nama_customer'] ?? $pemesanan->nama_customer,
                'tckt_kategori_pemesanan_id' => $data['kategori_pemesanan_id'] ?? $pemesanan->tckt_kategori_pemesanan_id,
                'tckt_unit_kerja_id' => $data['unit_kerja_pemesan'] ?? $pemesanan->tckt_unit_kerja_id,
                'status_pemesanan' => $data['status_pemesanan'] ?? $pemesanan->status_pemesanan,
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

            $tiket = $pemesanan->ticketingTiketKereta;
            $dataTiket = [
                'tckt_vendor_id' => $data['vendor_id'] ?? $tiket?->tckt_vendor_id,
                'tckt_kereta_id' => $data['kereta_id'] ?? $tiket?->tckt_kereta_id,
                'tckt_stasiun_berangkat_id' => $data['stasiun_berangkat_id'] ?? $tiket?->tckt_stasiun_berangkat_id,
                'tckt_stasiun_tiba_id' => $data['stasiun_tiba_id'] ?? $tiket?->tckt_stasiun_tiba_id,
                'kode_booking_kereta' => $data['kode_booking_kereta'] ?? $tiket?->kode_booking_kereta,
                'jadwal_berangkat_kereta' => $this->normalizeDateTime($data['jadwal_berangkat_kereta'] ?? null)
                    ?? $tiket?->jadwal_berangkat_kereta,
                'jadwal_tiba_kereta' => $this->normalizeDateTime($data['jadwal_tiba_kereta'] ?? null)
                    ?? $tiket?->jadwal_tiba_kereta,
                'zona_waktu' => $data['zona_waktu'] ?? $tiket?->zona_waktu,
                'zona_waktu_kedatangan' => $data['zona_waktu_kedatangan'] ?? $tiket?->zona_waktu_kedatangan,
            ];
            if ($tiket) {
                $tiket->update($dataTiket);
            } else {
                $dataTiket['tckt_pemesanan_id'] = $pemesanan->id;
                TicketingTiketKereta::create($dataTiket);
            }

            return $pemesanan;
        });
    }
}
