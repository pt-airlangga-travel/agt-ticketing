<?php

namespace Tests\Feature\Ticketing;

use Illuminate\Support\Facades\DB;
use Modules\Ticketing\Models\TicketingPemesanan;
use Modules\Ticketing\Models\TicketingPembayaranPenumpang;

class RiwayatPembayaranLunasChecklistTest extends TicketingTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadTicketingMigrations();
    }

    public function test_kolom_lunas_dan_lunas_penumpang_ids_ada_pada_tabel_ticketing_pemesanan(): void
    {
        $schema = DB::connection()->getSchemaBuilder();
        $this->assertTrue(
            $schema->hasColumn('ticketing_pemesanan', 'lunas'),
            'Kolom lunas harus ada di tabel ticketing_pemesanan.'
        );
        $this->assertTrue(
            $schema->hasColumn('ticketing_pemesanan', 'lunas_penumpang_ids'),
            'Kolom lunas_penumpang_ids harus ada di tabel ticketing_pemesanan.'
        );
    }

    public function test_keempat_penumpang_relation_manager_memiliki_icon_column_lunas(): void
    {
        $files = [
            'ReservasiPesawats/RelationManagers/PenumpangPesawatRelationManager.php',
            'ReservasiKeretas/RelationManagers/PenumpangKeretaRelationManager.php',
            'ReservasiHotels/RelationManagers/PenumpangHotelRelationManager.php',
            'ReservasiDokumens/RelationManagers/PemilikDokumenRelationManager.php',
        ];

        foreach ($files as $file) {
            $path = module_path('Ticketing', "app/Filament/Clusters/Ticketing/Resources/{$file}");
            $this->assertFileExists($path);
            $content = file_get_contents($path);

            $this->assertStringContainsString("IconColumn::make('lunas')", $content);
            $this->assertStringNotContainsString("ToggleColumn::make('lunas')", $content);
            $this->assertStringContainsString("isPenumpangLunas", $content);
            $this->assertStringContainsString("setPenumpangLunas", $content);
        }
    }

    public function test_keempat_riwayat_pembayaran_memiliki_action_status_lunas_penumpang_dan_tidak_ada_toggle_di_baris_cicilan(): void
    {
        foreach (['Pesawats', 'Keretas', 'Hotels', 'Dokumens'] as $jenis) {
            $path = module_path(
                'Ticketing',
                "app/Filament/Clusters/Ticketing/Resources/Reservasi{$jenis}/RelationManagers/RiwayatPembayaranRelationManager.php"
            );
            $this->assertFileExists($path);
            $content = file_get_contents($path);

            // Header action modal checklist penumpang lunas
            $this->assertStringContainsString("Action::make('statusLunasPenumpang')", $content);
            $this->assertStringContainsString("CheckboxList::make('lunas_penumpang_ids')", $content);

            // Baris tabel cicilan pembayaran TIDAK berisi toggle lunas (agar tidak rancu per transaksi)
            $this->assertStringNotContainsString("ToggleColumn::make('lunas')", $content);
            $this->assertStringNotContainsString("Toggle::make('lunas')", $content);
            $this->assertStringNotContainsString("mutateFormDataBeforeSave", $content);
        }
    }

    public function test_status_lunas_per_orang_berlaku_meskipun_dicicil_beberapa_kali(): void
    {
        ['pemesanan' => $pemesanan, 'pembayaran' => $pembayaran, 'penumpang' => $penumpang, 'user' => $user] = $this->ticketingScenario();

        $ria = $penumpang[0];
        $budi = $penumpang[1];

        // Ria mencicil 3 kali pembayaran
        for ($i = 0; $i < 3; $i++) {
            TicketingPembayaranPenumpang::create([
                'tckt_penumpang_id' => $ria->id,
                'tckt_pembayaran_id' => $pembayaran->id,
                'jumlah_membayar' => 50000,
                'user_id' => $user->id,
            ]);
        }

        // Budi belum bayar sama sekali
        $this->assertFalse($pemesanan->isPenumpangLunas($ria->id));
        $this->assertFalse($pemesanan->isPenumpangLunas($budi->id));

        // Tandai Ria Lunas (1x flag untuk seluruh cicilannya)
        $pemesanan->setPenumpangLunas($ria->id, true);
        $pemesanan->refresh();

        $this->assertTrue($pemesanan->isPenumpangLunas($ria->id));
        $this->assertFalse($pemesanan->isPenumpangLunas($budi->id));
        $this->assertFalse($pemesanan->isSemuaPenumpangLunas());

        // Tandai Budi juga Lunas
        $pemesanan->setPenumpangLunas($budi->id, true);
        $pemesanan->refresh();

        $this->assertTrue($pemesanan->isPenumpangLunas($budi->id));
        $this->assertTrue($pemesanan->isSemuaPenumpangLunas());

        // Batalkan lunas Ria
        $pemesanan->setPenumpangLunas($ria->id, false);
        $pemesanan->refresh();

        $this->assertFalse($pemesanan->isPenumpangLunas($ria->id));
        $this->assertTrue($pemesanan->isPenumpangLunas($budi->id));
        $this->assertFalse($pemesanan->isSemuaPenumpangLunas());
    }

    public function test_keempat_tabel_utama_memiliki_icon_column_lunas_searchable_dan_select_filter(): void
    {
        $tables = [
            'ReservasiPesawats/Tables/ReservasiPesawatsTable.php',
            'ReservasiKeretas/Tables/ReservasiKeretasTable.php',
            'ReservasiHotels/Tables/ReservasiHotelsTable.php',
            'ReservasiDokumens/Tables/ReservasiDokumensTable.php',
        ];

        foreach ($tables as $tableFile) {
            $path = module_path('Ticketing', "app/Filament/Clusters/Ticketing/Resources/{$tableFile}");
            $this->assertFileExists($path);
            $content = file_get_contents($path);

            $this->assertStringContainsString("IconColumn::make('lunas')", $content);
            $this->assertStringContainsString("listWithLineBreaks()", $content);
            $this->assertStringContainsString("isPenumpangLunas", $content);
            $this->assertStringContainsString("searchable(isIndividual: true", $content);
            $this->assertStringContainsString("SelectFilter::make('lunas')", $content);
            $this->assertStringNotContainsString("TextColumn::make('lunas_status')", $content);
        }
    }

    public function test_export_excel_menggunakan_prinsip_wysiwyg(): void
    {
        $path = module_path('Ticketing', 'app/Filament/Clusters/Ticketing/Concerns/ListsReservasiByEntity.php');
        $this->assertFileExists($path);
        $content = file_get_contents($path);

        $this->assertStringContainsString('getVisibleColumns()', $content);
        $this->assertStringContainsString('exportCellValue', $content);
        $this->assertStringContainsString("IconColumn", $content);
        $this->assertStringContainsString("'Lunas' : 'Belum Lunas'", $content);
    }

    public function test_searching_individual_per_pax_menyaring_penumpang_yang_tampil(): void
    {
        ['pemesanan' => $pemesanan, 'penumpang' => $penumpang, 'tiket' => $tiket] = $this->ticketingScenario();

        $satu = $penumpang[0];
        $dua = $penumpang[1];

        // Tandai Penumpang Satu lunas, Penumpang Dua belum lunas
        $pemesanan->setPenumpangLunas($satu->id, true);
        $pemesanan->refresh();

        // 1. Tanpa search: semua penumpang tampil
        $all = \Modules\Ticketing\Filament\Clusters\Ticketing\Concerns\ReservasiToggleableColumns::getFilteredPenumpangsForTable($tiket, null);
        $this->assertCount(2, $all);

        // 2. Search nama "Satu": hanya Penumpang Satu yang tampil
        $lwNamaSatu = new class(['ticketingPenumpang.nama_penumpang' => 'Satu']) {
            public function __construct(protected array $searches) {}
            public function getTableColumnSearches(): array { return $this->searches; }
        };
        $filtered = \Modules\Ticketing\Filament\Clusters\Ticketing\Concerns\ReservasiToggleableColumns::getFilteredPenumpangsForTable($tiket, $lwNamaSatu);
        $this->assertCount(1, $filtered);
        $this->assertSame((int) $satu->id, (int) $filtered->first()->id);

        // 3. Search status "lunas": hanya Penumpang Satu yang tampil
        $lwLunas = new class(['lunas' => 'lunas']) {
            public function __construct(protected array $searches) {}
            public function getTableColumnSearches(): array { return $this->searches; }
        };
        $filteredLunas = \Modules\Ticketing\Filament\Clusters\Ticketing\Concerns\ReservasiToggleableColumns::getFilteredPenumpangsForTable($tiket, $lwLunas);
        $this->assertCount(1, $filteredLunas);
        $this->assertSame((int) $satu->id, (int) $filteredLunas->first()->id);

        // 4. Search status "belum": hanya Penumpang Dua yang tampil
        $lwBelum = new class(['lunas' => 'belum']) {
            public function __construct(protected array $searches) {}
            public function getTableColumnSearches(): array { return $this->searches; }
        };
        $filteredBelum = \Modules\Ticketing\Filament\Clusters\Ticketing\Concerns\ReservasiToggleableColumns::getFilteredPenumpangsForTable($tiket, $lwBelum);
        $this->assertCount(1, $filteredBelum);
        $this->assertSame((int) $dua->id, (int) $filteredBelum->first()->id);
    }

    public function test_keempat_tabel_menyaring_penumpang_secara_individual_per_kepala(): void
    {
        $tables = [
            'ReservasiPesawats/Tables/ReservasiPesawatsTable.php',
            'ReservasiKeretas/Tables/ReservasiKeretasTable.php',
            'ReservasiHotels/Tables/ReservasiHotelsTable.php',
            'ReservasiDokumens/Tables/ReservasiDokumensTable.php',
        ];

        foreach ($tables as $tableFile) {
            $path = module_path('Ticketing', "app/Filament/Clusters/Ticketing/Resources/{$tableFile}");
            $content = file_get_contents($path);

            // Seluruh tabel harus memanggil getFilteredPenumpangsForTable pada kolom penumpang
            $this->assertStringContainsString("self::getFilteredPenumpangsForTable(\$record, \$livewire)", $content);
            $this->assertStringContainsString("(\$record, \$livewire = null)", $content);
        }
    }
}

