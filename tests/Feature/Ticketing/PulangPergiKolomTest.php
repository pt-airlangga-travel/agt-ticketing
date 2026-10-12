<?php

namespace Tests\Feature\Ticketing;

use Carbon\Carbon;
use Modules\Ticketing\Models\TicketingBandara;
use Modules\Ticketing\Models\TicketingKategoriPemesanan;
use Modules\Ticketing\Models\TicketingMaskapai;
use Modules\Ticketing\Models\TicketingPemesanan;
use Modules\Ticketing\Models\TicketingTiketPesawat;
use Modules\Ticketing\Models\TicketingUnitKerja;

/**
 * Test untuk pemecahan kolom "Detail Pulang" jadi kolom-kolom terpisah
 * (pulangPergiSegmens / pulangPergiStatus di model + kolom tabel baru).
 *
 * Semua berjalan di sqlite in-memory — tidak pernah menyentuh DB produksi.
 */
class PulangPergiKolomTest extends TicketingTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadTicketingMigrations();
    }

    /**
     * Membuat tiket pesawat dengan detail_pulang_pergi sesuai kebutuhan test.
     */
    protected function tiketPesawat(array $detailPulangPergi, int $pulangPergi = 1): TicketingTiketPesawat
    {
        static $counter = 0;
        $counter++;

        $unitKerja = TicketingUnitKerja::create(['nama_unit_kerja' => 'Unit Kerja Test']);
        $kategori = TicketingKategoriPemesanan::create(['nama_kategori' => 'Ticket']);

        $pemesanan = TicketingPemesanan::create([
            'invoice' => '26101000' . str_pad((string) $counter, 2, '0', STR_PAD_LEFT),
            'nama_customer' => 'Customer Test',
            'tckt_kategori_pemesanan_id' => $kategori->id,
            'tckt_unit_kerja_id' => $unitKerja->id,
            'status_pemesanan' => 'Confirmed',
            'pulang_pergi' => $pulangPergi,
            'tanggal_pemesanan' => '2026-10-10',
            'harga_beli' => 500000,
            'harga_publish' => 50000,
            'harga_jual' => 600000,
        ]);

        $maskapai = TicketingMaskapai::create(['nama_maskapai' => 'Garuda']);
        $vendor = \Modules\Ticketing\Models\TicketingVendor::create(['nama_vendor' => 'Vendor Test', 'jenis_vendor' => 1]);
        $bandaraA = TicketingBandara::create(['nama_bandara' => 'Soekarno Hatta', 'kode_bandara' => 'CGK']);
        $bandaraB = TicketingBandara::create(['nama_bandara' => 'Juanda', 'kode_bandara' => 'SUB']);

        return TicketingTiketPesawat::create([
            'tckt_pemesanan_id' => $pemesanan->id,
            'tckt_maskapai_id' => $maskapai->id,
            'tckt_vendor_id' => $vendor->id,
            'tckt_bandara_berangkat_id' => $bandaraA->id,
            'tckt_bandara_tiba_id' => $bandaraB->id,
            'nomor_ticket' => 'TICKET-001',
            'nomor_penerbangan' => 'GA-100',
            'kode_booking_pesawat' => 'BK-DEPART',
            'kelas' => 'Ekonomi',
            'jadwal_berangkat_pesawat' => '2026-10-15 08:00:00',
            'jadwal_tiba_pesawat' => '2026-10-15 10:00:00',
            'detail_pulang_pergi' => $detailPulangPergi === [] ? null : json_encode($detailPulangPergi),
        ]);
    }

    public function test_segmen_terurai_lengkap_satu_segmen(): void
    {
        $tiket = $this->tiketPesawat([
            'status_pemesanan_pulang_pergi' => 'Confirmed',
            'segmen' => [[
                'maskapai_id' => 1,
                'nomor_penerbangan' => 'GA-101',
                'nomor_ticket' => 'TICKET-RETURN',
                'kode_booking_pesawat' => 'BK-RETURN',
                'kelas' => 'Bisnis',
                'bandara_berangkat_id' => 2, // SUB
                'bandara_tiba_id' => 1,      // CGK
                'jadwal_berangkat_pesawat' => '2026-10-20 09:30:00',
                'jadwal_tiba_pesawat' => '2026-10-20 11:45:00',
            ]],
        ]);

        $segmens = $tiket->pulangPergiSegmens();

        $this->assertCount(1, $segmens);
        $this->assertSame('Garuda', $segmens[0]['maskapai']);
        $this->assertSame('GA-101', $segmens[0]['nomor_penerbangan']);
        $this->assertSame('TICKET-RETURN', $segmens[0]['nomor_ticket']);
        $this->assertSame('BK-RETURN', $segmens[0]['kode_booking']);
        $this->assertSame('Bisnis', $segmens[0]['kelas']);
        $this->assertSame('SUB - CGK', $segmens[0]['rute']);
        $this->assertSame('20 Oct 2026 09:30', $segmens[0]['jadwal_berangkat']);
        $this->assertSame('20 Oct 2026 11:45', $segmens[0]['jadwal_tiba']);

        $this->assertSame('Confirmed', $tiket->pulangPergiStatus());
    }

    public function test_multi_segmen_urut_dan_terpisah(): void
    {
        $tiket = $this->tiketPesawat([
            'status_pemesanan_pulang_pergi' => 'Confirmed',
            'segmen' => [
                [
                    'maskapai_id' => 1,
                    'nomor_penerbangan' => 'GA-201',
                    'bandara_berangkat_id' => 1,
                    'bandara_tiba_id' => 2,
                    'jadwal_berangkat_pesawat' => '2026-10-20 06:00:00',
                ],
                [
                    'maskapai_id' => 1,
                    'nomor_penerbangan' => 'GA-202',
                    'bandara_berangkat_id' => 2,
                    'bandara_tiba_id' => 1,
                    'jadwal_berangkat_pesawat' => '2026-10-20 15:00:00',
                ],
            ],
        ]);

        $segmens = $tiket->pulangPergiSegmens();

        $this->assertCount(2, $segmens);
        $this->assertSame('GA-201', $segmens[0]['nomor_penerbangan']);
        $this->assertSame('GA-202', $segmens[1]['nomor_penerbangan']);
        $this->assertSame('CGK - SUB', $segmens[0]['rute']);
        $this->assertSame('SUB - CGK', $segmens[1]['rute']);

        // Format lama tetap utuh (backward compat accessor).
        $this->assertStringContainsString('Segmen 1:', $tiket->formatted_detail_pulang_pergi);
        $this->assertStringContainsString('Segmen 2:', $tiket->formatted_detail_pulang_pergi);
    }

    public function test_bukan_round_trip_mengembalikan_kosong(): void
    {
        // pulang_pergi = 0 di pemesanan → segmens kosong walau JSON terisi.
        $tiket = $this->tiketPesawat([
            'status_pemesanan_pulang_pergi' => 'Confirmed',
            'segmen' => [[
                'maskapai_id' => 1,
                'nomor_penerbangan' => 'GA-999',
                'bandara_berangkat_id' => 1,
                'bandara_tiba_id' => 2,
            ]],
        ], pulangPergi: 0);

        $this->assertSame([], $tiket->pulangPergiSegmens());
        $this->assertSame('-', $tiket->formatted_detail_pulang_pergi);
    }

    public function test_detail_null_atau_json_rusak_aman(): void
    {
        $tiket = $this->tiketPesawat([]);
        $this->assertSame([], $tiket->pulangPergiSegmens());
        $this->assertNull($tiket->pulangPergiStatus());

        $tiket->detail_pulang_pergi = 'bukan-json{{{';
        $this->assertSame([], $tiket->pulangPergiSegmens());
        $this->assertNull($tiket->pulangPergiStatus());
    }

    public function test_format_json_lama_tanpa_kunci_segmen_tetap_terbaca(): void
    {
        // Data V1 lama: JSON flat tanpa 'segmen'.
        $tiket = $this->tiketPesawat([
            'maskapai_id' => 1,
            'nomor_penerbangan' => 'GA-OLD',
            'bandara_berangkat_id' => 1,
            'bandara_tiba_id' => 2,
            'jadwal_berangkat_pesawat' => '2026-10-25 07:00:00',
        ]);

        $segmens = $tiket->pulangPergiSegmens();

        $this->assertCount(1, $segmens);
        $this->assertSame('GA-OLD', $segmens[0]['nomor_penerbangan']);
        $this->assertSame('CGK - SUB', $segmens[0]['rute']);
        $this->assertSame('25 Oct 2026 07:00', $segmens[0]['jadwal_berangkat']);
    }

    public function test_format_tanggal_lama_tanggal_pulang_terpisah_tetap_terbaca(): void
    {
        // Varian kunci lama: tanggal/jam dipisah (tanggal_keberangkatan_pulang).
        $tiket = $this->tiketPesawat([
            'segmen' => [[
                'maskapai_id' => 1,
                'nomor_penerbangan' => 'GA-OLD2',
                'bandara_berangkat_id' => 1,
                'bandara_tiba_id' => 2,
                'tanggal_keberangkatan_pulang' => '2026-10-25',
                'jam_keberangkatan_pulang' => '07:30',
            ]],
        ]);

        $segmens = $tiket->pulangPergiSegmens();

        $this->assertSame('25 Oct 2026 07:30', $segmens[0]['jadwal_berangkat']);
    }

    public function test_kolom_tabel_baru_terdaftar_di_reservasi_pesawats_table(): void
    {
        $isi = file_get_contents(module_path('Ticketing', 'app/Filament/Clusters/Ticketing/Resources/ReservasiPesawats/Tables/ReservasiPesawatsTable.php'));

        foreach ([
            'pulang_pergi_status',
            'pulang_pergi_maskapai',
            'pulang_pergi_nomor_penerbangan',
            'pulang_pergi_rute',
            'pulang_pergi_jadwal_berangkat',
            'pulang_pergi_jadwal_tiba',
            'pulang_pergi_nomor_ticket',
            'pulang_pergi_kode_booking',
            'pulang_pergi_kelas',
        ] as $kolom) {
            $this->assertStringContainsString("TextColumn::make('{$kolom}')", $isi, "Kolom {$kolom} harus terdaftar.");
        }

        // Kolom gabungan lama sudah dihapus dari tabel.
        $this->assertStringNotContainsString(
            "TextColumn::make('detail_pulang_pergi')",
            $isi,
            'Kolom lama detail_pulang_pergi sudah dihapus dari tabel.'
        );
    }

    public function test_export_excel_mengisi_nilai_kolom_pulang(): void
    {
        // Kolom pecahan "Pulang" diexport lewat exportPulangPergiValue()
        // (bukan data_get) — harus terisi dari parser model.
        $isi = file_get_contents(module_path('Ticketing', 'app/Filament/Clusters/Ticketing/Concerns/ListsReservasiByEntity.php'));

        $this->assertStringContainsString(
            "str_starts_with(\$name, 'pulang_pergi_')",
            $isi,
            'Export harus punya cabang khusus untuk kolom pulang_pergi_*.'
        );
        $this->assertStringContainsString('exportPulangPergiValue', $isi);
        $this->assertStringContainsString('pulangPergiSegmens()', $isi);
    }

    public function test_export_pulang_pergi_value_untuk_record_nyata(): void
    {
        // Simulasi logika exportPulangPergiValue() terhadap record nyata.
        $tiket = $this->tiketPesawat([
            'status_pemesanan_pulang_pergi' => 'Confirmed',
            'segmen' => [[
                'maskapai_id' => 1,
                'nomor_penerbangan' => 'GA-101',
                'nomor_ticket' => 'TICKET-RETURN',
                'kode_booking_pesawat' => 'BK-RETURN',
                'kelas' => 'Bisnis',
                'bandara_berangkat_id' => 2,
                'bandara_tiba_id' => 1,
                'jadwal_berangkat_pesawat' => '2026-10-20 09:30:00',
                'jadwal_tiba_pesawat' => '2026-10-20 11:45:00',
            ]],
        ]);

        // Replika persis logika exportPulangPergiValue untuk field maskapai:
        $values = collect($tiket->pulangPergiSegmens())
            ->pluck('maskapai')
            ->filter(fn ($v) => filled($v))
            ->values()
            ->all();

        $this->assertSame(['Garuda'], $values);
        // Hasil akhir di Excel: scalar (string), bukan array.
        $this->assertSame('Garuda', $values ? implode(', ', $values) : '-');

        // Untuk nomor penerbangan:
        $values = collect($tiket->pulangPergiSegmens())
            ->pluck('nomor_penerbangan')
            ->filter(fn ($v) => filled($v))
            ->values()
            ->all();
        $this->assertSame(['GA-101'], $values);
        $this->assertSame('GA-101', $values ? implode(', ', $values) : '-');

        // Multi-segmen → digabung koma dalam satu string.
        $tiketMulti = $this->tiketPesawat([
            'status_pemesanan_pulang_pergi' => 'Confirmed',
            'segmen' => [
                ['maskapai_id' => 1, 'nomor_penerbangan' => 'GA-201', 'bandara_berangkat_id' => 1, 'bandara_tiba_id' => 2],
                ['maskapai_id' => 1, 'nomor_penerbangan' => 'GA-202', 'bandara_berangkat_id' => 2, 'bandara_tiba_id' => 1],
            ],
        ]);
        $values = collect($tiketMulti->pulangPergiSegmens())
            ->pluck('nomor_penerbangan')
            ->filter(fn ($v) => filled($v))
            ->values()
            ->all();
        $this->assertSame('GA-201, GA-202', $values ? implode(', ', $values) : '-');

        // Status pulang:
        $this->assertSame('Confirmed', $tiket->pulangPergiStatus() ?? '-');

        // Record bukan round-trip → semua kolom pulang berisi '-'.
        $tiketSekaliJalan = $this->tiketPesawat([], pulangPergi: 0);
        $values = collect($tiketSekaliJalan->pulangPergiSegmens())
            ->pluck('maskapai')
            ->filter(fn ($v) => filled($v))
            ->values()
            ->all();
        $this->assertSame('-', $values ? implode(', ', $values) : '-', 'Bukan round-trip → "-" di Excel.');
    }
}
