<?php

namespace Tests\Feature\Ticketing;

use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingActivityLogs\TicketingActivityLogResource;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingBandaras\TicketingBandaraResource;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingHotels\TicketingHotelResource;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingKeretas\TicketingKeretaResource;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingMaskapais\TicketingMaskapaiResource;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingPelanggans\TicketingPelangganResource;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingPembayars\TicketingPembayarResource;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingPenumpangs\TicketingPenumpangResource;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingStasiuns\TicketingStasiunResource;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingUnitKerjas\TicketingUnitKerjaResource;
use Modules\Ticketing\Filament\Clusters\Ticketing\Resources\TicketingVendors\TicketingVendorResource;
use Modules\Ticketing\Models\TicketingKategoriPemesanan;
use Modules\Ticketing\Models\TicketingKereta;
use Modules\Ticketing\Models\TicketingMaskapai;
use Modules\Ticketing\Models\TicketingPelanggan;
use Modules\Ticketing\Models\TicketingPembayar;
use Modules\Ticketing\Models\TicketingPemesanan;
use Modules\Ticketing\Models\TicketingUnitKerja;
use Modules\Ticketing\Models\TicketingVendor;
use Modules\Ticketing\Services\ReservasiDokumenService;
use Modules\Ticketing\Services\ReservasiHotelService;
use Modules\Ticketing\Services\ReservasiKeretaService;
use Modules\Ticketing\Services\ReservasiPesawatService;

class MasterDataNavigasiDanFormRelationTest extends TicketingTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadTicketingMigrations();
    }

    public function test_urutan_dan_label_sidebar_master_data(): void
    {
        // 1. Nama Pemesan
        $this->assertSame(1, TicketingPelangganResource::getNavigationSort());
        $this->assertSame('Nama Pemesan', TicketingPelangganResource::getNavigationLabel());

        // 2. Nama Penumpang
        $this->assertSame(2, TicketingPenumpangResource::getNavigationSort());
        $this->assertSame('Nama Penumpang', TicketingPenumpangResource::getNavigationLabel());

        // 3. Nama Pembayar
        $this->assertSame(3, TicketingPembayarResource::getNavigationSort());
        $this->assertSame('Nama Pembayar', TicketingPembayarResource::getNavigationLabel());

        // Master resources lain berurutan tanpa bentrok
        $this->assertSame(4, TicketingMaskapaiResource::getNavigationSort());
        $this->assertSame(5, TicketingBandaraResource::getNavigationSort());
        $this->assertSame(6, TicketingKeretaResource::getNavigationSort());
        $this->assertSame(7, TicketingStasiunResource::getNavigationSort());
        $this->assertSame(8, TicketingHotelResource::getNavigationSort());
        $this->assertSame(9, TicketingVendorResource::getNavigationSort());
        $this->assertSame(10, TicketingUnitKerjaResource::getNavigationSort());
        $this->assertSame(11, TicketingActivityLogResource::getNavigationSort());
    }

    public function test_form_invoice_tidak_memiliki_section_pembayar_dan_pemesan_berasal_dari_master_pelanggan(): void
    {
        $formFiles = [
            'ReservasiPesawats/Schemas/ReservasiPesawatForm.php',
            'ReservasiKeretas/Schemas/ReservasiKeretaForm.php',
            'ReservasiHotels/Schemas/ReservasiHotelForm.php',
            'ReservasiDokumens/Schemas/ReservasiDokumenForm.php',
        ];

        foreach ($formFiles as $formFile) {
            $path = module_path('Ticketing', "app/Filament/Clusters/Ticketing/Resources/{$formFile}");
            $this->assertFileExists($path);
            $content = file_get_contents($path);

            $this->assertStringNotContainsString("Section::make('Pembayar')", $content);
        }

        $partialsPath = module_path('Ticketing', 'app/Filament/Clusters/Ticketing/ReservasiFormPartials.php');
        $partialsContent = file_get_contents($partialsPath);

        $this->assertStringContainsString("TicketingPelanggan::query()", $partialsContent);
        $this->assertStringContainsString("createOptionForm", $partialsContent);
        $this->assertStringContainsString("TicketingPelanggan::firstOrCreate", $partialsContent);
    }

    public function test_keempat_service_reservasi_membuat_pembayaran_dengan_fallback_aman_saat_pembayar_invoice_kosong(): void
    {
        $unit = TicketingUnitKerja::create([
            'nama_unit_kerja' => 'Divisi Keuangan',
            'is_active' => true,
        ]);

        $kategori = TicketingKategoriPemesanan::create([
            'nama_kategori' => 'Dinas',
        ]);

        $vendorPesawat = TicketingVendor::create(['nama_vendor' => 'Vendor Pesawat', 'jenis_vendor' => 1]);
        $vendorKereta = TicketingVendor::create(['nama_vendor' => 'Vendor Kereta', 'jenis_vendor' => 2]);
        $vendorHotel = TicketingVendor::create(['nama_vendor' => 'Vendor Hotel', 'jenis_vendor' => 3]);
        $vendorDokumen = TicketingVendor::create(['nama_vendor' => 'Vendor Dokumen', 'jenis_vendor' => 4]);

        $maskapai = TicketingMaskapai::create(['nama_maskapai' => 'Garuda Indonesia']);
        $bandara1 = \Modules\Ticketing\Models\TicketingBandara::create(['nama_bandara' => 'Soekarno Hatta', 'kode_bandara' => 'CGK']);
        $bandara2 = \Modules\Ticketing\Models\TicketingBandara::create(['nama_bandara' => 'Juanda', 'kode_bandara' => 'SUB']);

        $kereta = TicketingKereta::create(['nama_kereta' => 'Argo Bromo']);
        $stasiun1 = \Modules\Ticketing\Models\TicketingStasiun::create(['nama_stasiun' => 'Gambir', 'kode_stasiun' => 'GMR']);
        $stasiun2 = \Modules\Ticketing\Models\TicketingStasiun::create(['nama_stasiun' => 'Pasar Turi', 'kode_stasiun' => 'SBI']);

        $hotel = \Modules\Ticketing\Models\TicketingHotel::create(['nama_hotel' => 'Hotel Indonesia']);

        $baseData = [
            'nama_customer' => 'PT Mandiri Sukses',
            'unit_kerja_pemesan' => $unit->id,
            'kategori_pemesanan_id' => $kategori->id,
            'status_pemesanan' => 'Confirmed',
            'tanggal_pemesanan' => now()->toDateString(),
            'harga_beli' => 1000000,
            'harga_publish' => 1100000,
            'harga_jual' => 1200000,
            // Perhatikan: TIDAK ADA nama_pembayar & unit_kerja_pembayar
        ];

        // 1. Pesawat
        $pesawatService = app(ReservasiPesawatService::class);
        $tiketPesawat = $pesawatService->create(array_merge($baseData, [
            'maskapai_id' => $maskapai->id,
            'vendor_id' => $vendorPesawat->id,
            'bandara_berangkat_id' => $bandara1->id,
            'bandara_tiba_id' => $bandara2->id,
            'nomor_ticket' => '123-456789',
            'nomor_penerbangan' => 'GA-123',
            'kode_booking_pesawat' => 'BOOK123',
            'kelas' => 'Economy',
            'jadwal_berangkat_pesawat' => '2026-10-25 10:00:00',
            'jadwal_tiba_pesawat' => '2026-10-25 12:00:00',
        ]));
        $pembayaranPesawat = $tiketPesawat->ticketingPemesanan->ticketingPembayaran;
        $this->assertNotNull($pembayaranPesawat);
        $this->assertSame($unit->id, $pembayaranPesawat->tckt_unit_kerja_id);
        $this->assertSame('PT Mandiri Sukses', $pembayaranPesawat->nama_pembayar);

        // 2. Kereta
        $keretaService = app(ReservasiKeretaService::class);
        $pemesananKereta = $keretaService->create(array_merge($baseData, [
            'vendor_id' => $vendorKereta->id,
            'kereta_id' => $kereta->id,
            'stasiun_berangkat_id' => $stasiun1->id,
            'stasiun_tiba_id' => $stasiun2->id,
            'kode_booking_kereta' => 'KAI123',
            'jadwal_berangkat_kereta' => '2026-10-25 08:00:00',
            'jadwal_tiba_kereta' => '2026-10-25 16:00:00',
        ]));
        $pembayaranKereta = $pemesananKereta->ticketingPembayaran;
        $this->assertNotNull($pembayaranKereta);
        $this->assertSame($unit->id, $pembayaranKereta->tckt_unit_kerja_id);
        $this->assertSame('PT Mandiri Sukses', $pembayaranKereta->nama_pembayar);

        // 3. Hotel
        $hotelService = app(ReservasiHotelService::class);
        $pemesananHotel = $hotelService->create(array_merge($baseData, [
            'vendor_id' => $vendorHotel->id,
            'hotel_id' => $hotel->id,
            'tipe_kamar' => 'Deluxe',
            'jadwal_checkin' => '2026-10-25 14:00:00',
            'jadwal_checkout' => '2026-10-26 12:00:00',
        ]));
        $pembayaranHotel = $pemesananHotel->ticketingPembayaran;
        $this->assertNotNull($pembayaranHotel);
        $this->assertSame($unit->id, $pembayaranHotel->tckt_unit_kerja_id);
        $this->assertSame('PT Mandiri Sukses', $pembayaranHotel->nama_pembayar);

        // 4. Dokumen
        $dokumenService = app(ReservasiDokumenService::class);
        $pemesananDokumen = $dokumenService->create(array_merge($baseData, [
            'vendor_id' => $vendorDokumen->id,
            'jenis_dokumen' => 'Paspor',
            'keterangan' => 'Pengurusan Paspor',
        ]));
        $pembayaranDokumen = $pemesananDokumen->ticketingPembayaran;
        $this->assertNotNull($pembayaranDokumen);
        $this->assertSame($unit->id, $pembayaranDokumen->tckt_unit_kerja_id);
        $this->assertSame('PT Mandiri Sukses', $pembayaranDokumen->nama_pembayar);
    }

    public function test_keempat_relation_manager_penumpang_tersinkron_dengan_master_penumpang_dan_pembayar(): void
    {
        $relationManagers = [
            'ReservasiPesawats/RelationManagers/PenumpangPesawatRelationManager.php',
            'ReservasiKeretas/RelationManagers/PenumpangKeretaRelationManager.php',
            'ReservasiHotels/RelationManagers/PenumpangHotelRelationManager.php',
            'ReservasiDokumens/RelationManagers/PemilikDokumenRelationManager.php',
        ];

        foreach ($relationManagers as $file) {
            $path = module_path('Ticketing', "app/Filament/Clusters/Ticketing/Resources/{$file}");
            $this->assertFileExists($path);
            $content = file_get_contents($path);

            // Kolom master pembayar terhubung ke TicketingPembayar
            $this->assertStringContainsString("TicketingPembayar::query()", $content);
            $this->assertStringContainsString("TicketingPembayar::firstOrCreate", $content);
            $this->assertStringContainsString("tckt_pembayar_id", $content);

            // Kolom master penumpang terhubung ke TicketingPenumpang
            $this->assertStringContainsString("TicketingPenumpang::query()", $content);
            $this->assertStringContainsString("TicketingPenumpang::create", $content);

            // Kolom tabel menampilkan pembayar dan unit kerja
            $this->assertStringContainsString("TextColumn::make('pembayar')", $content);
            $this->assertStringContainsString("TextColumn::make('unit_kerja')", $content);

            // Icon kolom lunas tetap ada
            $this->assertStringContainsString("IconColumn::make('lunas')", $content);
        }
    }

    public function test_keempat_riwayat_pembayaran_kolom_nama_bisa_di_sort(): void
    {
        $riwayatManagers = [
            'ReservasiPesawats/RelationManagers/RiwayatPembayaranRelationManager.php',
            'ReservasiKeretas/RelationManagers/RiwayatPembayaranRelationManager.php',
            'ReservasiHotels/RelationManagers/RiwayatPembayaranRelationManager.php',
            'ReservasiDokumens/RelationManagers/RiwayatPembayaranRelationManager.php',
        ];

        foreach ($riwayatManagers as $file) {
            $path = module_path('Ticketing', "app/Filament/Clusters/Ticketing/Resources/{$file}");
            $this->assertFileExists($path);
            $content = file_get_contents($path);

            // Kolom nama penumpang / pemilik sortable
            $this->assertStringContainsString("TextColumn::make('ticketingPenumpang.nama_penumpang')", $content);

            // Kolom nama pembayar ada dan sortable
            $this->assertStringContainsString("TextColumn::make('ticketingPembayar.nama_pembayar')", $content);
            $this->assertStringContainsString("->label('Nama Pembayar')", $content);

            // Pastikan kedua kolom memiliki sortable()
            preg_match("/TextColumn::make\('ticketingPenumpang\.nama_penumpang'\)(.+?)(?=TextColumn::make)/s", $content, $penumpangCol);
            $this->assertNotEmpty($penumpangCol);
            $this->assertStringContainsString('->sortable()', $penumpangCol[1]);

            preg_match("/TextColumn::make\('ticketingPembayar\.nama_pembayar'\)(.+?)(?=TextColumn::make)/s", $content, $pembayarCol);
            $this->assertNotEmpty($pembayarCol);
            $this->assertStringContainsString('->sortable()', $pembayarCol[1]);
        }
    }

    public function test_keempat_riwayat_pembayaran_memiliki_export_excel_bulk_action(): void
    {
        $riwayatManagers = [
            'ReservasiPesawats/RelationManagers/RiwayatPembayaranRelationManager.php',
            'ReservasiKeretas/RelationManagers/RiwayatPembayaranRelationManager.php',
            'ReservasiHotels/RelationManagers/RiwayatPembayaranRelationManager.php',
            'ReservasiDokumens/RelationManagers/RiwayatPembayaranRelationManager.php',
        ];

        foreach ($riwayatManagers as $file) {
            $path = module_path('Ticketing', "app/Filament/Clusters/Ticketing/Resources/{$file}");
            $this->assertFileExists($path);
            $content = file_get_contents($path);

            $this->assertStringContainsString('HasExportRiwayatPembayaranBulkAction', $content);
            $this->assertStringContainsString('$this->exportRiwayatPembayaranBulkAction()', $content);
        }

        $traitPath = module_path('Ticketing', 'app/Filament/Clusters/Ticketing/Concerns/HasExportRiwayatPembayaranBulkAction.php');
        $this->assertFileExists($traitPath);
        $traitContent = file_get_contents($traitPath);

        $this->assertStringContainsString("BulkAction::make('export_excel')", $traitContent);
        $this->assertStringContainsString("->label('Export Excel')", $traitContent);
        $this->assertStringContainsString("response()->streamDownload", $traitContent);
        $this->assertStringContainsString("Writer::class", $traitContent);
    }

    public function test_kolom_jumlah_dan_total_riwayat_pembayaran_plain_angka(): void
    {
        $riwayatManagers = [
            'ReservasiPesawats/RelationManagers/RiwayatPembayaranRelationManager.php',
            'ReservasiKeretas/RelationManagers/RiwayatPembayaranRelationManager.php',
            'ReservasiHotels/RelationManagers/RiwayatPembayaranRelationManager.php',
            'ReservasiDokumens/RelationManagers/RiwayatPembayaranRelationManager.php',
        ];

        foreach ($riwayatManagers as $file) {
            $path = module_path('Ticketing', "app/Filament/Clusters/Ticketing/Resources/{$file}");
            $this->assertFileExists($path);
            $content = file_get_contents($path);

            // Kolom jumlah_membayar tidak memakai formatStateUsing dengan 'Rp '
            preg_match("/TextColumn::make\('jumlah_membayar'\)(.+?)(?=TextColumn::make)/s", $content, $jumlahCol);
            $this->assertNotEmpty($jumlahCol);
            $this->assertStringNotContainsString("'Rp '", $jumlahCol[1]);

            // Kolom total_per_penumpang tidak memakai formatStateUsing dengan 'Rp '
            preg_match("/TextColumn::make\('total_per_penumpang'\)(.+?)(?=TextColumn::make)/s", $content, $totalCol);
            $this->assertNotEmpty($totalCol);
            $this->assertStringNotContainsString("'Rp '", $totalCol[1]);
        }
    }
}
