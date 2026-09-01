<?php

namespace Modules\Ticketing\Services;

use Illuminate\Support\Facades\DB;
use Modules\Ticketing\Models\TicketingDokumen;
use Modules\Ticketing\Models\TicketingPembayaran;
use Modules\Ticketing\Models\TicketingPemesanan;

class ReservasiDokumenService extends ReservasiService
{
    public function create(array $data): TicketingPemesanan
    {
        return DB::transaction(function () use ($data) {
            $pemesanan = TicketingPemesanan::create([
                'invoice' => $this->generateInvoiceNumber('4'),
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

            TicketingDokumen::create([
                'tckt_pemesanan_id' => $pemesanan->id,
                'tckt_vendor_id' => $data['vendor_id'] ?? null,
                'jenis_dokumen' => $data['jenis_dokumen'] ?? null,
                'keterangan' => $data['keterangan'] ?? null,
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

            $dokumen = $pemesanan->ticketingDokumen;
            $dataDokumen = [
                'tckt_vendor_id' => $data['vendor_id'] ?? $dokumen?->tckt_vendor_id,
                'jenis_dokumen' => $data['jenis_dokumen'] ?? $dokumen?->jenis_dokumen,
                'keterangan' => $data['keterangan'] ?? $dokumen?->keterangan,
            ];
            if ($dokumen) {
                $dokumen->update($dataDokumen);
            } else {
                $dataDokumen['tckt_pemesanan_id'] = $pemesanan->id;
                TicketingDokumen::create($dataDokumen);
            }

            return $pemesanan;
        });
    }
}
