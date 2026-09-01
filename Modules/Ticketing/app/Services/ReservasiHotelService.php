<?php

namespace Modules\Ticketing\Services;

use Illuminate\Support\Facades\DB;
use Modules\Ticketing\Models\TicketingKamarHotel;
use Modules\Ticketing\Models\TicketingPembayaran;
use Modules\Ticketing\Models\TicketingPemesanan;

class ReservasiHotelService extends ReservasiService
{
    public function create(array $data): TicketingPemesanan
    {
        return DB::transaction(function () use ($data) {
            $pemesanan = TicketingPemesanan::create([
                'invoice' => $this->generateInvoiceNumber('3'),
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

            TicketingKamarHotel::create([
                'tckt_pemesanan_id' => $pemesanan->id,
                'tckt_vendor_id' => $data['vendor_id'] ?? null,
                'tckt_hotel_id' => $data['hotel_id'] ?? null,
                'jumlah_kamar' => $data['jumlah_kamar'] ?? 1,
                'lama_menginap' => $data['lama_menginap'] ?? 1,
                'tipe_kamar' => $data['tipe_kamar'] ?? null,
                'jadwal_checkin' => $this->normalizeDateTime($data['jadwal_checkin'] ?? null),
                'jadwal_checkout' => $this->normalizeDateTime($data['jadwal_checkout'] ?? null),
                'zona_waktu' => $data['zona_waktu'] ?? null,
                'include_breakfast' => isset($data['include_breakfast']) && $data['include_breakfast'] ? 1 : 0,
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

            $kamar = $pemesanan->ticketingKamarHotel;
            $dataKamar = [
                'tckt_vendor_id' => $data['vendor_id'] ?? $kamar?->tckt_vendor_id,
                'tckt_hotel_id' => $data['hotel_id'] ?? $kamar?->tckt_hotel_id,
                'jumlah_kamar' => $data['jumlah_kamar'] ?? $kamar?->jumlah_kamar,
                'lama_menginap' => $data['lama_menginap'] ?? $kamar?->lama_menginap,
                'tipe_kamar' => $data['tipe_kamar'] ?? $kamar?->tipe_kamar,
                'jadwal_checkin' => $this->normalizeDateTime($data['jadwal_checkin'] ?? null)
                    ?? $kamar?->jadwal_checkin,
                'jadwal_checkout' => $this->normalizeDateTime($data['jadwal_checkout'] ?? null)
                    ?? $kamar?->jadwal_checkout,
                'zona_waktu' => $data['zona_waktu'] ?? $kamar?->zona_waktu,
                'include_breakfast' => isset($data['include_breakfast']) && $data['include_breakfast'] ? 1 : 0,
            ];
            if ($kamar) {
                $kamar->update($dataKamar);
            } else {
                $dataKamar['tckt_pemesanan_id'] = $pemesanan->id;
                TicketingKamarHotel::create($dataKamar);
            }

            return $pemesanan;
        });
    }
}
