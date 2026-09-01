<?php

namespace Modules\Ticketing\Http\Controllers;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Modules\Ticketing\Models\TicketingPemesanan;

class PrintInvoiceController extends Controller
{
    public function printInvoicePesawat(Request $request)
    {
        $request->validate([
            'id_pemesanan' => 'required',
            'id_penumpang' => 'required',
        ]);

        $pemesanan = TicketingPemesanan::find($request->input('id_pemesanan'));

        if (! $pemesanan) {
            return abort(404);
        }

        $tiket = $pemesanan->ticketingTiketPesawat;

        if (! $tiket) {
            return abort(404);
        }

        $penumpangTerpilih = $this->resolusiPenumpang($tiket->ticketingPenumpang, explode(',', $request->id_penumpang), $pemesanan->ticketingPembayaran?->id);

        $hargaSatuan = $this->hargaSatuan($pemesanan->harga_jual, $tiket->ticketingPenumpang->count());
        $hargaTotal = $hargaSatuan * count($penumpangTerpilih);

        $rutePulang = $nomerPenerbanganPulang = $kodeBookingPulang = $jadwalBerangkatPulang = '';

        if ($pemesanan->pulang_pergi == 1) {
            $detail = json_decode((string) $tiket->detail_pulang_pergi, true) ?: [];

            $bandaraBerangkatPulang = $tiket->ticketingBerangkatBandara::find($detail['bandara_berangkat_id_pulang'] ?? null);
            $bandaraTibaPulang = $tiket->ticketingTibaBandara::find($detail['bandara_tiba_id_pulang'] ?? null);

            $rutePulang = ($bandaraBerangkatPulang?->kode_bandara ?? '-').' - '.($bandaraTibaPulang?->kode_bandara ?? '-');
            $nomerPenerbanganPulang = $detail['nomor_penerbangan_pulang'] ?? '';
            $kodeBookingPulang = $detail['kode_booking_pesawat_pulang'] ?? '';
            $jadwalBerangkatPulang = ($detail['tanggal_keberangkatan_pulang'] ?? '').' '.($detail['jam_keberangkatan_pulang'] ?? '');
        }

        return $this->streamInvoice('pesawat', $pemesanan, [
            'nomer_penerbangan' => $tiket->nomor_penerbangan,
            'kode_booking' => $tiket->kode_booking_pesawat,
            'rute_pesawat' => $tiket->ticketingBerangkatBandara->kode_bandara.' - '.$tiket->ticketingTibaBandara->kode_bandara,
            'jadwal_berangkat_pesawat' => $tiket->jadwal_berangkat_pesawat,
            'pulang_pergi' => $pemesanan->pulang_pergi,
            'rute_pesawat_pulang' => $rutePulang,
            'nomer_penerbangan_pulang' => $nomerPenerbanganPulang,
            'kode_booking_pulang' => $kodeBookingPulang,
            'jadwal_berangkat_pesawat_pulang' => $jadwalBerangkatPulang,
        ], $hargaSatuan, $hargaTotal, $penumpangTerpilih);
    }

    public function printInvoiceKereta(Request $request)
    {
        $request->validate([
            'id_pemesanan' => 'required',
            'id_penumpang' => 'required',
        ]);

        $pemesanan = TicketingPemesanan::find($request->input('id_pemesanan'));

        if (! $pemesanan) {
            return abort(404);
        }

        $tiket = $pemesanan->ticketingTiketKereta;

        if (! $tiket) {
            return abort(404);
        }

        $penumpangTerpilih = $this->resolusiPenumpang($tiket->ticketingPenumpang, explode(',', $request->id_penumpang));

        $hargaSatuan = $this->hargaSatuan($pemesanan->harga_jual, $tiket->ticketingPenumpang->count());
        $hargaTotal = $hargaSatuan * count($penumpangTerpilih);

        return $this->streamInvoice('kereta', $pemesanan, [
            'kode_booking' => $tiket->kode_booking_kereta,
            'rute_kereta' => $tiket->ticketingBerangkatStasiun->nama_stasiun.' - '.$tiket->ticketingTibaStasiun->nama_stasiun,
            'jadwal_berangkat_kereta' => $tiket->jadwal_berangkat_kereta,
        ], $hargaSatuan, $hargaTotal, $penumpangTerpilih);
    }

    public function printInvoiceHotel(Request $request)
    {
        $request->validate([
            'id_pemesanan' => 'required',
            'id_penumpang' => 'required',
        ]);

        $pemesanan = TicketingPemesanan::find($request->input('id_pemesanan'));

        if (! $pemesanan) {
            return abort(404);
        }

        $kamarHotel = $pemesanan->ticketingKamarHotel;

        if (! $kamarHotel) {
            return abort(404);
        }

        $penumpangTerpilih = $this->resolusiPenumpang($kamarHotel->ticketingPenumpang, explode(',', $request->id_penumpang));

        $hargaSatuan = $this->hargaSatuan($pemesanan->harga_jual, $kamarHotel->ticketingPenumpang->count());
        $hargaTotal = $hargaSatuan * count($penumpangTerpilih);

        return $this->streamInvoice('hotel', $pemesanan, [
            'nama_hotel' => $kamarHotel->ticketingHotel->nama_hotel,
            'check_in' => date('d-m-Y H:i', strtotime($kamarHotel->jadwal_checkin)),
            'check_out' => date('d-m-Y H:i', strtotime($kamarHotel->jadwal_checkout)),
            'jumlah_kamar' => $kamarHotel->jumlah_kamar,
        ], $hargaSatuan, $hargaTotal, $penumpangTerpilih);
    }

    public function printInvoiceDokumen(Request $request)
    {
        $request->validate([
            'id_pemesanan' => 'required',
            'id_penumpang' => 'required',
        ]);

        $pemesanan = TicketingPemesanan::find($request->input('id_pemesanan'));

        if (! $pemesanan) {
            return abort(404);
        }

        $dokumen = $pemesanan->ticketingDokumen;

        if (! $dokumen) {
            return abort(404);
        }

        $penumpangTerpilih = $this->resolusiPenumpang($dokumen->ticketingPenumpang, explode(',', $request->id_penumpang));

        $hargaSatuan = $this->hargaSatuan($pemesanan->harga_jual, $dokumen->ticketingPenumpang->count());
        $hargaTotal = $hargaSatuan * count($penumpangTerpilih);

        return $this->streamInvoice('dokumen', $pemesanan, [
            'jenis_dokumen' => $dokumen->jenis_dokumen,
            'keterangan' => $dokumen->keterangan,
        ], $hargaSatuan, $hargaTotal, $penumpangTerpilih);
    }

    protected function resolusiPenumpang($penumpangs, array $idTerpilih, ?int $idPembayaran = null): array
    {
        $hasil = [];

        $penumpangs = $penumpangs->loadMissing([
            'ticketingPembayaranPenumpang.ticketingPembayar',
            'ticketingPembayaranPenumpang.ticketingUnitKerja',
        ]);

        foreach ($idTerpilih as $id) {
            foreach ($penumpangs as $penumpang) {
                if ($penumpang->id == $id) {
                    $nama = $penumpang->jenis_kelamin == 1
                        ? 'Ms '.$penumpang->nama_penumpang
                        : 'Mr '.$penumpang->nama_penumpang;

                    $pembayaranPenumpang = $penumpang->ticketingPembayaranPenumpang
                        ->filter(fn ($row) => $idPembayaran === null || $row->tckt_pembayaran_id == $idPembayaran)
                        ->first();

                    $namaPembayar = $pembayaranPenumpang?->ticketingPembayar?->nama_pembayar
                        ?? $pembayaranPenumpang?->nama_pembayar;
                    $unitKerjaPembayar = $pembayaranPenumpang?->ticketingUnitKerja?->nama_unit_kerja;

                    $hasil[] = [
                        'id' => $penumpang->id,
                        'nama' => $nama,
                        'pembayar' => $namaPembayar ?? '',
                        'unit_kerja_pembayar' => $unitKerjaPembayar ?? '',
                    ];
                }
            }
        }

        return $hasil;
    }

    protected function hargaSatuan(int $hargaJual, int $jumlahPenumpang): int
    {
        return $jumlahPenumpang > 0 ? (int) round($hargaJual / $jumlahPenumpang) : 0;
    }

    protected function streamInvoice(string $view, TicketingPemesanan $pemesanan, array $data, int $hargaSatuan, int $hargaTotal, array $penumpangTerpilih)
    {
        $pdf = Pdf::loadView('ticketing::invoice.'.$view, array_merge($data, [
            'nama_pemesan' => $pemesanan->nama_customer,
            'invoice' => $pemesanan->invoice,
            'logo_agt' => $this->convertBase64(public_path('img/logo-agt.png')),
            'logo_unair' => $this->convertBase64(public_path('img/logo-ua.png')),
            'nama_pembayar' => $pemesanan->ticketingPembayaran?->nama_pembayar ?? '-',
            'unit_kerja_pemesan' => $pemesanan->ticketingUnitKerja?->nama_unit_kerja ?? '-',
            'unit_kerja_pembayar' => $pemesanan->ticketingPembayaran?->ticketingUnitKerja?->nama_unit_kerja ?? '-',
            'tanggal_pemesanan' => date('d-m-Y', strtotime($pemesanan->tanggal_pemesanan)),
            'jatuh_tempo' => date('d-m-Y', strtotime($pemesanan->tanggal_pemesanan.' + 7 days')),
            'terbilang' => strtoupper($this->terbilang($hargaTotal)).' RUPIAH',
            'harga_satuan' => $hargaSatuan,
            'harga_total' => $hargaTotal,
            'penumpangs' => $penumpangTerpilih,
        ]))
            ->setOptions(['defaultFont' => 'sans', 'isRemoteEnabled' => true])
            ->setPaper('a5', 'landscape');

        return $pdf->stream('invoice-'.$view.'-'.$pemesanan->invoice.'.pdf');
    }

    protected function convertBase64(string $path): string
    {
        $data = file_get_contents($path);
        $type = pathinfo($path, PATHINFO_EXTENSION);

        return 'data:image/'.$type.';base64,'.base64_encode($data);
    }

    public function terbilang($angka)
    {
        $angka = floatval($angka);
        $bilangan = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];
        if ($angka < 12) {
            return $bilangan[$angka];
        } elseif ($angka < 20) {
            return $bilangan[$angka - 10].' belas';
        } elseif ($angka < 100) {
            $hasil_bagi = (int) ($angka / 10);
            $hasil_mod = $angka % 10;

            return trim($bilangan[$hasil_bagi].' puluh '.$bilangan[$hasil_mod]);
        } elseif ($angka < 200) {
            return 'seratus '.$this->terbilang($angka - 100);
        } elseif ($angka < 1000) {
            $hasil_bagi = (int) ($angka / 100);
            $hasil_mod = $angka % 100;

            return trim($bilangan[$hasil_bagi].' ratus '.$this->terbilang($hasil_mod));
        } elseif ($angka < 2000) {
            return 'seribu '.$this->terbilang($angka - 1000);
        } elseif ($angka < 1000000) {
            $hasil_bagi = (int) ($angka / 1000);
            $hasil_mod = $angka % 1000;

            return trim($this->terbilang($hasil_bagi).' ribu '.$this->terbilang($hasil_mod));
        } elseif ($angka < 1000000000) {
            $hasil_bagi = (int) ($angka / 1000000);
            $hasil_mod = $angka % 1000000;

            return trim($this->terbilang($hasil_bagi).' juta '.$this->terbilang($hasil_mod));
        } elseif ($angka < 1000000000000) {
            $hasil_bagi = (int) ($angka / 1000000000);
            $hasil_mod = fmod($angka, 1000000000);

            return trim($this->terbilang($hasil_bagi).' milyar '.$this->terbilang($hasil_mod));
        } elseif ($angka < 1000000000000000) {
            $hasil_bagi = (int) ($angka / 1000000000000);
            $hasil_mod = fmod($angka, 1000000000000);

            return trim($this->terbilang($hasil_bagi).' triliun '.$this->terbilang($hasil_mod));
        }

        return 'angka';
    }
}
