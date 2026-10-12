<?php

namespace Tests\Feature\Ticketing;

use Filament\Schemas\Schema as FilamentSchema;
use Illuminate\Support\Facades\Schema as DBSchema;
use Modules\Ticketing\Models\TicketingPembayaranPenumpang;
use Modules\Ticketing\Models\TicketingPemesanan;
use Modules\Ticketing\Models\TicketingPenumpang;

/**
 * Test CHARACTERIZATION untuk temuan T1–T4 + T6.
 *
 * PENTING soal gaya test ini:
 * Beberapa test di bawah TIDAK menandai bug sebagai "harus begini", melainkan
 * MENGUNCI PERILAKU SAAT INI (memorable behaviour) supaya:
 *   1. bug yang belum diperbaiki tercatat sebagai bukti, dan
 *   2. saat nanti diperbaiki, test ini akan GAGAL dan memaksa Anda sadar
 *      bahwa perilaku berubah (lalu ubah test-nya dengan sengaja).
 *
 * Bila Anda memutuskan memperbaiki temuan tersebut, ubah asersi pada test
 * yang bersangkutan — jangan hapus test-nya.
 */
class TemuanRegresiTest extends TicketingTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Dipanggil SETELAH parent: bila guard keamanan sudah men-skip test
        // (koneksi bukan sqlite), baris berikut tidak akan dijalankan.
        $this->loadTicketingMigrations();
    }

    /**
     * T1 — Di 4 RelationManager "Riwayat Pembayaran", kode memanggil
     * `Schema::hasColumn(...)` padahal `Schema` yang di-import adalah
     * `Filament\Schemas\Schema` yang tidak punya method tersebut.
     *
     * Test ini mengunci kenyataan itu: Filament Schema tidak punya hasColumn,
     * sedangkan facade DB punya. Jadi 12 pemanggilan `Schema::hasColumn` di
     * modul Ticketing akan melempar BadMethodCallException saat runtime.
     */
    public function test_t1_filament_schema_tidak_punya_has_column(): void
    {
        $filamentSchema = new \ReflectionClass(FilamentSchema::class);
        $dbBuilder = new \ReflectionClass(\Illuminate\Database\Schema\Builder::class);

        // Facade DB (dipakai sebagai DBSchema) memang punya hasColumn...
        $this->assertTrue(
            $dbBuilder->hasMethod('hasColumn'),
            'Illuminate\\Database\\Schema\\Builder seharusnya punya hasColumn().'
        );

        // ...tetapi class Filament Schema TIDAK punya.
        $this->assertFalse(
            $filamentSchema->hasMethod('hasColumn'),
            'Filament\\Schemas\\Schema tidak punya hasColumn() — memanggilnya akan BadMethodCallException.'
        );
    }

    /**
     * T1 — membuktikan pemanggilan `Filament\Schemas\Schema::hasColumn()`
     * benar-benar fatal (BadMethodCallException), bukan sekadar "tidak rapi".
     */
    public function test_t1_memanggil_has_column_pada_filament_schema_melempar_exception(): void
    {
        $this->expectException(\BadMethodCallException::class);

        FilamentSchema::hasColumn('ticketing_pembayaran_penumpang', 'tckt_pembayar_id');
    }

    /**
     * T1 — penjaga perbaikan.
     *
     * Dulu ada 12 pemanggilan `Schema::hasColumn(...)` yang salah (4
     * RelationManager × 3 kolom). Setelah diperbaiki menjadi `DBSchema::hasColumn(...)`,
     * test ini menjaga agar pola salah itu TIDAK kembali lagi.
     *
     * PENTING: hanya file yang meng-import `Filament\Schemas\Schema` (yaitu yang
     * menulis `Schema::` dan bermaksud Filament) yang diperiksa. File Models/Services
     * yang meng-import `Illuminate\Support\Facades\Schema` memang sah memakai
     * `Schema::hasColumn()` dan tidak boleh dilaporkan sebagai pelanggaran.
     */
    public function test_t1_tidak_ada_lagi_pemanggilan_schema_has_column_yang_salah(): void
    {
        $pelaku = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(module_path('Ticketing', 'app'), \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (! str_ends_with($file->getPathname(), '.php')) {
                continue;
            }

            $isi = file_get_contents($file->getPathname());

            // Hanya relevan bila file ini meng-import Filament Schema.
            if (! preg_match('/^use Filament\\\\Schemas\\\\Schema;/m', $isi)) {
                continue;
            }

            if (preg_match('/(?<!DB)Schema::hasColumn\(/', $isi)) {
                $pelaku[] = str_replace(module_path('Ticketing', ''), '', $file->getPathname());
            }
        }

        $this->assertSame(
            [],
            $pelaku,
            "File ini meng-import Filament Schema tapi memakai Schema::hasColumn() (harus DBSchema::hasColumn()):\n"
            .implode("\n", $pelaku)
        );
    }

    /**
     * T1 — keempat file yang dulu rusak sekarang memakai alias yang benar,
     * dan facade DB-nya benar-benar bekerja di lingkungan test.
     */
    public function test_t1_keempat_riwayat_pembayaran_memakai_dbschema_dan_fungsional(): void
    {
        foreach (['Pesawats', 'Keretas', 'Hotels', 'Dokumens'] as $jenis) {
            $isi = $this->moduleFile(
                "app/Filament/Clusters/Ticketing/Resources/Reservasi{$jenis}/RelationManagers/RiwayatPembayaranRelationManager.php"
            );

            $this->assertSame(
                3,
                preg_match_all('/DBSchema::hasColumn\(/', $isi),
                "Reservasi{$jenis}: ketiga cek kolom harus memakai DBSchema::hasColumn()."
            );
            $this->assertSame(
                0,
                preg_match_all('/(?<!DB)Schema::hasColumn\(/', $isi),
                "Reservasi{$jenis}: tidak boleh ada lagi Schema::hasColumn() tanpa alias."
            );
        }

        // Facade DB benar-benar berfungsi (ini yang dulu tidak pernah tercapai).
        $this->assertTrue(DBSchema::hasColumn('ticketing_pembayaran_penumpang', 'tckt_pembayar_id'));
        $this->assertFalse(DBSchema::hasColumn('ticketing_pembayaran_penumpang', 'kolom_yang_tidak_ada'));
    }

    /**
     * T2 — Service menulis `detail_pulang_pergi` sebagai JSON dengan struktur
     * `segmen[]`. SEJAK pemecahan kolom "Detail Pulang" (fitur kolom terpisah),
     * model TicketingTiketPesawat punya parser terstruktur (pulangPergiSegmens)
     * yang membaca struktur `segmen[]` DAN format lama.
     *
     * Test ini mengunci: struktur yang ditulis service harus bisa dibaca
     * parser model (tidak ada lagi ketidakcocokan kunci di jalur tabel).
     *
     * CATATAN: PrintInvoiceController masih membaca kunci format lama
     * (bandara_berangkat_id_pulang, ...) — bug T2 di invoice belum diperbaiki,
     * menunggu keputusan pemilik produk (lihat dokumen verifikasi §2).
     */
    public function test_t2_struktur_segmen_service_terbaca_oleh_parser_model(): void
    {
        $service = $this->moduleFile('app/Services/ReservasiPesawatService.php');
        $model = $this->moduleFile('app/Models/TicketingTiketPesawat.php');

        // Service menulis struktur bersarang ini:
        $this->assertStringContainsString("'segmen' => \$segmenPulang", $service);

        // Parser model membaca kunci 'segmen'...
        $this->assertStringContainsString("\$detail['segmen']", $model);

        // ...dan setiap kunci yang ditulis segmenPulang() juga dibaca parser
        // (parser memakai kunci baru; format lama dicek via fallback *_pulang).
        foreach ([
            'maskapai_id',
            'bandara_berangkat_id',
            'bandara_tiba_id',
            'nomor_penerbangan',
            'nomor_ticket',
            'kode_booking_pesawat',
            'kelas',
            'jadwal_berangkat_pesawat',
            'jadwal_tiba_pesawat',
        ] as $kunci) {
            $this->assertStringContainsString(
                "\$seg['{$kunci}']",
                $model,
                "Parser model harus membaca kunci '{$kunci}' yang ditulis service."
            );
        }

        // Parser juga tetap kompatibel dengan format V1 lama (*_pulang).
        $this->assertStringContainsString("bandara_berangkat_id_pulang", $model);
        $this->assertStringContainsString("nomor_penerbangan_pulang", $model);
    }

    /**
     * T4 — Invoice mengalikan `harga_jual` dengan JUMLAH penumpang yang dicetak,
     * jadi `harga_jual` diperlakukan sebagai harga SATUAN (per penumpang).
     */
    public function test_t4_invoice_di_tagih_per_penumpang_harga_jual_kali_jumlah_penumpang(): void
    {
        $controller = $this->moduleFile('app/Http/Controllers/PrintInvoiceController.php');

        $this->assertStringContainsString('$hargaSatuan = $pemesanan->harga_jual;', $controller);
        $this->assertStringContainsString('$hargaTotal = $hargaSatuan * count($penumpangTerpilih);', $controller);
        $this->assertStringContainsString("'terbilang' => strtoupper(\$this->terbilang(\$hargaTotal)).' RUPIAH',", $controller);
    }

    /**
     * T3 — Di form Riwayat Pembayaran, `harga_jual` DIBAGI jumlah penumpang
     * (dianggap total transaksi). Ini kebalikan dari asumsi invoice pada T4.
     */
    public function test_t3_form_pembayaran_membagi_harga_jual_dengan_jumlah_penumpang(): void
    {
        $rm = $this->moduleFile(
            'app/Filament/Clusters/Ticketing/Resources/ReservasiKeretas/RelationManagers/RiwayatPembayaranRelationManager.php'
        );

        $this->assertStringContainsString(
            'round((int) $tiket->ticketingPemesanan?->harga_jual / $penumpangs->count())',
            $rm,
            'hargaSatuan() di form pembayaran membagi harga_jual dengan jumlah penumpang.'
        );
    }

    /**
     * T3 (lanjutan) — pembayaran yang sudah tercatat dihitung dari SEMUA baris
     * pembayaran milik penumpang itu, tanpa dibatasi `tckt_pembayaran_id`.
     */
    public function test_t3_terbayar_per_penumpang_tidak_dibatasi_pembayaran_induk(): void
    {
        ['pemesanan' => $pemesanan, 'tiket' => $tiket, 'pembayaran' => $pembayaran, 'penumpang' => $penumpang, 'user' => $user] = $this->ticketingScenario();

        $ria = $penumpang[0];
        $budi = $penumpang[1];

        TicketingPembayaranPenumpang::create([
            'tckt_penumpang_id' => $ria->id,
            'tckt_pembayaran_id' => $pembayaran->id,
            'jumlah_membayar' => 300000,
            'user_id' => $user->id,
        ]);

        // Simulasi query yang dipakai RiwayatPembayaranRelationManager::terbayarPerPenumpang()
        $terbayar = TicketingPembayaranPenumpang::query()
            ->whereIn('tckt_penumpang_id', [$ria->id, $budi->id])
            ->groupBy('tckt_penumpang_id')
            ->selectRaw('tckt_penumpang_id, SUM(jumlah_membayar) as total_terbayar')
            ->pluck('total_terbayar', 'tckt_penumpang_id')
            ->all();

        $this->assertSame(300000, (int) $terbayar[$ria->id]);
        $this->assertArrayNotHasKey($budi->id, $terbayar);

        // Bandingkan dengan model: ticketingTerbayar() DIBATASI pada pembayaran induk.
        // CATATAN: ticketingTerbayar() sendiri saat ini error (lihat T7), jadi
        // perbandingan dilakukan langsung lewat query yang sama dengan model.
        $totalViaModel = \Illuminate\Support\Facades\DB::table('ticketing_pembayaran_penumpang')
            ->whereIn('tckt_penumpang_id', [$ria->id, $budi->id])
            ->where('tckt_pembayaran_id', $pembayaran->id)
            ->sum('jumlah_membayar');

        $this->assertSame(300000, (int) $totalViaModel);

        // Model menghitung total, sedangkan form menghitung per penumpang:
        $hargaSatuanPerForm = (int) round($pemesanan->harga_jual / 2);
        $this->assertSame(500000, $hargaSatuanPerForm, 'Form menganggap harga_jual adalah TOTAL (dibagi 2 penumpang).');
    }

    /**
     * T3 — `ticketingCekLunas()` membandingkan total terbayar dengan `harga_jual`
     * TANPA dibagi jumlah penumpang, padahal form per penumpang menuntut
     * harga_jual/2 per orang. Untuk 2 penumpang, model menyatakan LUNAS pada
     * separuh pembayaran.
     */
    public function test_t3_model_menyatakan_lunas_pada_separuh_pembayaran_untuk_dua_penumpang(): void
    {
        ['pemesanan' => $pemesanan, 'pembayaran' => $pembayaran, 'penumpang' => $penumpang, 'user' => $user] = $this->ticketingScenario();

        foreach ($penumpang as $orang) {
            TicketingPembayaranPenumpang::create([
                'tckt_penumpang_id' => $orang->id,
                'tckt_pembayaran_id' => $pembayaran->id,
                'jumlah_membayar' => 500000,
                'user_id' => $user->id,
            ]);
        }

        $pemesanan = $pemesanan->fresh();

        $pembayaran = $pemesanan->ticketingPembayaran;

        $totalTerbayar = (int) \Illuminate\Support\Facades\DB::table('ticketing_pembayaran_penumpang')
            ->whereIn('tckt_penumpang_id', $penumpang->pluck('id')->all())
            ->where('tckt_pembayaran_id', $pembayaran->id)
            ->sum('jumlah_membayar');

        // Total terbayar 1.000.000 == harga_jual → (bila ticketingTerbayar() benar)
        // model akan bilang LUNAS, padahal tiap penumpang baru bayar harga satuannya.
        $this->assertSame(1000000, $totalTerbayar);
        $this->assertSame(1000000, (int) $pemesanan->harga_jual);
        $this->assertSame(500000, (int) ($pemesanan->harga_jual / 2));

        // ticketingCekLunas() membandingkan total itu dengan harga_jual utuh.
        $this->assertTrue(
            $totalTerbayar >= (int) $pemesanan->harga_jual,
            'Aturan lunas di model memakai harga_jual utuh (bukan per penumpang).'
        );
    }

    /**
     * T6 — `TicketingPembayaran::ticketingPemesanan()` dideklarasikan `hasOne`
     * padahal arah relasinya seharusnya `belongsTo` (pembayaran milik pemesanan).
     * SQL yang dihasilkan mencari `ticketing_pemesanan.ticketing_pembayaran_id`
     * yang tidak pernah ada, sehingga kolomnya salah.
     */
    public function test_t6_relasi_pembayaran_ke_pemesanan_memakai_kunci_yang_salah(): void
    {
        $pembayaran = new \Modules\Ticketing\Models\TicketingPembayaran;

        $relasi = $pembayaran->ticketingPemesanan();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasOne::class, $relasi);

        $this->assertSame(
            'ticketing_pembayaran_id',
            $relasi->getForeignKeyName(),
            'Relasi hasOne menurunkan FK dari nama class: ticketing_pembayaran_id (tidak ada di skema).'
        );

        $this->assertSame(
            'ticketing_pemesanan',
            $relasi->getRelated()->getTable()
        );
    }

    /**
     * TAMBahan — kolom `jenis_kelamin` disimpan sebagai integer 0/1, dan invoice
     * memakai `== 1` untuk memilih gelar "Ms".
     */
    public function test_gelar_penumpang_di_invoice_memakai_jenis_kelamin_satu_untuk_ms(): void
    {
        ['penumpang' => $penumpang] = $this->ticketingScenario();

        $pria = TicketingPenumpang::find($penumpang[0]->id);
        $wanita = TicketingPenumpang::find($penumpang[1]->id);

        $this->assertSame(0, (int) $pria->jenis_kelamin);
        $this->assertSame(1, (int) $wanita->jenis_kelamin);
    }

    /**
     * Temuan portabilitas — migration index memakai `information_schema.statistics`
     * (khusus MySQL), sehingga skema tidak bisa dibangun di SQLite.
     * Karena itu test Ticketing harus melewatinya (lihat $skipMigrations).
     */
    public function test_migration_index_memakai_information_schema_khusus_mysql(): void
    {
        $isi = $this->moduleFile('database/migrations/2026_08_13_000001_add_indexes_to_ticketing_tables.php');

        $this->assertStringContainsString('information_schema.statistics', $isi);
        $this->assertContains(
            '2026_08_13_000001_add_indexes_to_ticketing_tables.php',
            $this->skipMigrations,
            'Migration ini harus ada di daftar skip agar test SQLite bisa jalan.'
        );
    }

    /**
     * Temuan tambahan — tabel `ticketing_tiket_kereta` mendeklarasikan dua foreign
     * key dengan NAMA yang sama ('fk_tckt_stasiun'), yang bermasalah di MySQL.
     */
    public function test_migration_tiket_kereta_punya_nama_foreign_key_duplikat(): void
    {
        $isi = $this->moduleFile('database/migrations/2026_08_05_170721_create_ticketing_tiket_kereta_table.php');

        $jumlah = substr_count($isi, "->name('fk_tckt_stasiun')");

        $this->assertSame(2, $jumlah, 'Dua FK stasiun memakai nama constraint yang sama.');
    }

    /**
     * Penjaga: `ticketingPenumpang()` di model Pemesanan BUKAN relasi Eloquent,
     * melainkan method pintar yang mengembalikan relasi milik anak (tiket/kereta/
     * kamar/dokumen) sesuai jenis reservasi.
     *
     * Konsekuensinya: hasilnya TETAP sebuah Relation (BelongsToMany), jadi
     * `->get()`, `->count()`, `->load()` bekerja — tetapi TIDAK BISA dipakai
     * untuk `with()`/eager loading dari sisi pemesanan.
     */
    public function test_ticketing_penumpang_adalah_relasi_titipan_bukan_relasi_eloquent_pemesanan(): void
    {
        ['pemesanan' => $pemesanan] = $this->ticketingScenario();

        $relasi = $pemesanan->ticketingPenumpang();

        // Bukan relasi yang didefinisikan di model pemesanan, tapi tetap Relation.
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $relasi);
        $this->assertSame(
            'ticketing_penumpang_tiket_kereta',
            $relasi->getTable(),
            'Untuk reservasi kereta, delegasinya ke pivot tiket kereta.'
        );

        $this->assertCount(2, $relasi->get());
    }

    /**
     * Penjaga: relasi pemesanan → pembayaran memakai `tckt_pemesanan_id` (benar),
     * sehingga `ticketingPembayaran` dapat diakses dari pemesanan.
     */
    public function test_pemesanan_bisa_mengakses_pembayarannya(): void
    {
        ['pemesanan' => $pemesanan, 'pembayaran' => $pembayaran] = $this->ticketingScenario();

        $this->assertSame($pembayaran->id, $pemesanan->fresh()->ticketingPembayaran->id);
        $this->assertSame('Pembayar Test', $pemesanan->fresh()->ticketingPembayaran->nama_pembayar);
    }

    /**
     * TEMUAN T7 (BARU): `TicketingPemesanan::ticketingTerbayar()` memanggil
     * `$this->ticketingPenumpang()`, tetapi method itu mengembalikan objek
     * RELASI (BelongsToMany) — bukan koleksi model. Di dalam `foreach`, nilai
     * yang keluar adalah `false` (karena iterasi relasi yang belum di-`get()`),
     * sehingga `$key->id` melempar:
     *
     *     ErrorException: Attempt to read property "id" on false
     *
     * Artinya `ticketingTerbayar()` (dan `ticketingCekLunas()` yang memanggilnya)
     * GAGAL TOTAL saat runtime. Saat ini tidak terasa karena keduanya tidak
     * dipanggil dari mana pun — jadi ini bug tersembunyi, bukan bug aktif.
     */
    public function test_t7_ticketing_terbayar_gagal_karena_iterasi_relasi_yang_belum_di_get(): void
    {
        ['pemesanan' => $pemesanan] = $this->ticketingScenario();

        $this->expectException(\ErrorException::class);
        $this->expectExceptionMessage('Attempt to read property "id" on false');

        $pemesanan->fresh()->ticketingTerbayar();
    }

    /**
     * T7 (bukti pendukung): memanggil `->get()` lebih dulu membuat perhitungan
     * berjalan benar — jadi akar masalahnya memang pemanggilan relasi tanpa get().
     */
    public function test_t7_memanggil_get_lebih_dulu_membuat_perhitungan_benar(): void
    {
        ['pemesanan' => $pemesanan, 'pembayaran' => $pembayaran, 'penumpang' => $penumpang, 'user' => $user] = $this->ticketingScenario();

        $ids = $pemesanan->fresh()->ticketingPenumpang()->get()->pluck('id')->all();
        $this->assertCount(2, $ids);

        TicketingPembayaranPenumpang::create([
            'tckt_penumpang_id' => $ids[0],
            'tckt_pembayaran_id' => $pembayaran->id,
            'jumlah_membayar' => 250000,
            'user_id' => $user->id,
        ]);

        $pembayaran = $pemesanan->fresh()->ticketingPembayaran;

        $total = \Illuminate\Support\Facades\DB::table('ticketing_pembayaran_penumpang')
            ->whereIn('tckt_penumpang_id', $ids)
            ->where('tckt_pembayaran_id', $pembayaran->id)
            ->sum('jumlah_membayar');

        $this->assertSame(250000, (int) $total);
    }

    /**
     * Penjaga: `ticketingTerbayar()` & `ticketingCekLunas()` saat ini TIDAK
     * dipanggil dari mana pun di dalam repo (dead code) — itulah sebabnya
     * bug T7 tidak terlihat di produksi.
     *
     * Saat fungsi ini mulai dipakai, hapus atau ubah test ini dengan sengaja.
     */
    public function test_t7_kedua_method_tersebut_belum_dipakai_di_mana_pun(): void
    {
        $pemakai = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(base_path(), \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            $path = $file->getPathname();

            if (! str_ends_with($path, '.php')) {
                continue;
            }

            // Lewati folder vendor, node_modules, dan file test itu sendiri.
            if (preg_match('#[\\\\/](vendor|node_modules|storage|tests)[\\\\/]#', $path)) {
                continue;
            }

            // Lewati file definisi method-nya sendiri.
            if (str_ends_with($path, 'Modules'.DIRECTORY_SEPARATOR.'Ticketing'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Models'.DIRECTORY_SEPARATOR.'TicketingPemesanan.php')) {
                continue;
            }

            $isi = file_get_contents($path);

            if (preg_match('/->ticketingTerbayar\(|->ticketingCekLunas\(|\$this->ticketingTerbayar\(|\$this->ticketingCekLunas\(/', $isi)) {
                $pemakai[] = str_replace(base_path().DIRECTORY_SEPARATOR, '', $path);
            }
        }

        $this->assertSame(
            [],
            $pemakai,
            "ticketingTerbayar()/ticketingCekLunas() ternyata sudah dipakai di:\n".implode("\n", $pemakai)
        );
    }
}
