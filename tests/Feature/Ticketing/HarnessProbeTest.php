<?php

namespace Tests\Feature\Ticketing;

use Illuminate\Support\Facades\DB;

/**
 * Probe: memastikan harness test Ticketing benar-benar berjalan di SQLite
 * in-memory dan seluruh migration (core + modul) dapat dijalankan.
 */
class HarnessProbeTest extends TicketingTestCase
{
    public function test_it_runs_on_sqlite_in_memory(): void
    {
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
    }

    public function test_ticketing_migrations_can_run(): void
    {
        $this->loadTicketingMigrations();
        foreach ([
            'ticketing_pemesanan',
            'ticketing_tiket_pesawat',
            'ticketing_tiket_kereta',
            'ticketing_kamar_hotel',
            'ticketing_dokumen',
            'ticketing_pembayaran',
            'ticketing_pembayaran_penumpang',
            'ticketing_penumpang',
            'ticketing_penumpang_tiket_kereta',
            'ticketing_activity_log',
        ] as $table) {
            $this->assertTrue(
                DB::connection()->getSchemaBuilder()->hasTable($table),
                "Tabel {$table} tidak terbentuk setelah migrate."
            );
        }
    }
}
