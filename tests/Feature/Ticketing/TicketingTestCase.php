<?php

namespace Tests\Feature\Ticketing;

use Illuminate\Support\Facades\DB;
use App\Models\User;
use Modules\Ticketing\Models\TicketingKategoriPemesanan;
use Modules\Ticketing\Models\TicketingKereta;
use Modules\Ticketing\Models\TicketingPembayaran;
use Modules\Ticketing\Models\TicketingPemesanan;
use Modules\Ticketing\Models\TicketingPenumpang;
use Modules\Ticketing\Models\TicketingStasiun;
use Modules\Ticketing\Models\TicketingTiketKereta;
use Modules\Ticketing\Models\TicketingUnitKerja;
use Modules\Ticketing\Models\TicketingVendor;
use Tests\TestCase;

/**
 * Base test untuk modul Ticketing.
 *
 * GUARD KESELAMATAN: setiap test yang mewarisi class ini akan DILEWATI (skip)
 * bila koneksi database bukan SQLite. Ini mencegah test tidak sengaja
 * menulis/menghapus data pada DB MySQL live (lihat .env).
 */
abstract class TicketingTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $driver = DB::connection()->getDriverName();

        if ($driver !== 'sqlite') {
            // SKIP (bukan fail) supaya `phpunit` bawaan — yang memakai koneksi dari
            // .env (MySQL produksi) — tidak ikut menjalankan test ini dan TIDAK
            // menyentuh database produksi sama sekali.
            $this->markTestSkipped(
                'Test Ticketing dilewati: koneksi aktif bukan sqlite '
                ."(driver: {$driver}). Jalankan dengan: "
                .'php.exe vendor/bin/phpunit -c phpunit-ticketing.xml'
            );
        }
    }

    /**
     * Migration yang sengaja DILEWATI saat test.
     *
     * `2026_08_13_000001_add_indexes_to_ticketing_tables` membaca
     * `information_schema.statistics` (khusus MySQL) sehingga tidak bisa
     * dijalankan di SQLite. Index bukan bagian dari logika bisnis, jadi aman
     * dilewati — dan ini sekaligus tercatat sebagai temuan portabilitas.
     */
    protected array $skipMigrations = [
        '2026_08_13_000001_add_indexes_to_ticketing_tables.php',
    ];

    /**
     * Menjalankan migration core + modul Ticketing.
     *
     * `coolsam/modules` tidak otomatis me-load migration modul saat test,
     * jadi kita kirim daftar file migration secara eksplisit.
     */
    protected function loadTicketingMigrations(): void
    {
        $paths = [];

        $dirs = [
            'database/migrations' => database_path('migrations'),
            'Modules/Ticketing/database/migrations' => module_path('Ticketing', 'database/migrations'),
        ];

        foreach ($dirs as $relative => $dir) {
            foreach (glob($dir.'/*_*.php') as $file) {
                if (in_array(basename($file), $this->skipMigrations, true)) {
                    continue;
                }

                // Migrator menempelkan base_path ke setiap path, jadi pakai path relatif.
                $paths[] = $relative.'/'.basename($file);
            }
        }

        $this->artisan('migrate', [
            '--path' => $paths,
            '--force' => true,
        ])->run();
    }

    /**
     * Membuat satu skenario reservasi KERETA yang lengkap di sqlite in-memory.
     *
     * Dipakai bersama oleh beberapa test. Semua nilai sengaja eksplisit supaya
     * asersi mudah dibaca.
     *
     * @return array{pemesanan: \Modules\Ticketing\Models\TicketingPemesanan, tiket: \Modules\Ticketing\Models\TicketingTiketKereta, pembayaran: \Modules\Ticketing\Models\TicketingPembayaran, penumpang: \Illuminate\Support\Collection<int, \Modules\Ticketing\Models\TicketingPenumpang>}
     */
    protected function ticketingScenario(array $pemesananOverrides = []): array
    {
        // ticketing_pembayaran_penumpang.user_id = NOT NULL, jadi butuh user nyata.
        $user = User::create([
            'name' => 'Tester Ticketing',
            'username' => 'tester_ticketing',
            'email' => 'tester.ticketing@example.test',
            'password' => 'secret',
        ]);

        $unitKerja = TicketingUnitKerja::create(['nama_unit_kerja' => 'Unit Kerja Test']);

        $kategori = TicketingKategoriPemesanan::create(['nama_kategori' => 'Ticket']);

        $vendor = TicketingVendor::create(['nama_vendor' => 'Vendor Kereta', 'jenis_vendor' => 2]);

        $kereta = TicketingKereta::create(['nama_kereta' => 'Argo Bromo']);

        $stasiunBerangkat = TicketingStasiun::create(['nama_stasiun' => 'Gubeng', 'kode_stasiun' => 'SGU']);
        $stasiunTiba = TicketingStasiun::create(['nama_stasiun' => 'Pasar Turi', 'kode_stasiun' => 'PTR']);

        $pemesanan = TicketingPemesanan::create(array_merge([
            'invoice' => '260810001',
            'nama_customer' => 'Pemesan Test',
            'tckt_kategori_pemesanan_id' => $kategori->id,
            'tckt_unit_kerja_id' => $unitKerja->id,
            'status_pemesanan' => 'Confirmed',
            'pulang_pergi' => 0,
            'tanggal_pemesanan' => '2026-08-10',
            'harga_beli' => 800000,
            'harga_publish' => 50000,
            'harga_jual' => 1000000,
        ], $pemesananOverrides));

        $tiket = TicketingTiketKereta::create([
            'tckt_pemesanan_id' => $pemesanan->id,
            'tckt_vendor_id' => $vendor->id,
            'tckt_kereta_id' => $kereta->id,
            'tckt_stasiun_berangkat_id' => $stasiunBerangkat->id,
            'tckt_stasiun_tiba_id' => $stasiunTiba->id,
            'kode_booking_kereta' => 'BK-'.uniqid(),
            'jadwal_berangkat_kereta' => '2026-08-15 08:00:00',
            'jadwal_tiba_kereta' => '2026-08-15 16:00:00',
        ]);

        $penumpang = collect([
            TicketingPenumpang::create(['nama_penumpang' => 'Penumpang Satu', 'jenis_kelamin' => 0]),
            TicketingPenumpang::create(['nama_penumpang' => 'Penumpang Dua', 'jenis_kelamin' => 1]),
        ]);

        $tiket->ticketingPenumpang()->attach($penumpang->pluck('id')->all());

        $pembayaran = TicketingPembayaran::create([
            'tckt_unit_kerja_id' => $unitKerja->id,
            'tckt_pemesanan_id' => $pemesanan->id,
            'nama_pembayar' => 'Pembayar Test',
        ]);

        return compact('pemesanan', 'tiket', 'pembayaran', 'penumpang', 'user');
    }

    /**
     * Membaca isi file di dalam modul Ticketing (path relatif terhadap folder modul).
     */
    protected function moduleFile(string $relativePath): string
    {
        $path = module_path('Ticketing', $relativePath);

        $this->assertFileExists($path, "File modul tidak ditemukan: {$path}");

        return file_get_contents($path);
    }
}
