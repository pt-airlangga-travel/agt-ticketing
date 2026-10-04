<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class MigrateV1ToV2 extends Command
{
    protected $signature = 'migrate:v1-to-v2 {--connection=mysql_v1 : Koneksi ke database V1 yang disetup di config/database.php}';
    protected $description = 'Migrasi data dari DB V1 (agt) ke DB V2 (inticket)';

    public function handle()
    {
        $v1 = $this->option('connection');
        
        try {
            DB::connection($v1)->getPdo();
        } catch (\Exception $e) {
            $this->error("Koneksi '{$v1}' gagal! Pastikan Anda sudah menambahkan konfigurasi DB lama di config/database.php");
            return;
        }

        $this->info('Memulai migrasi V1 ke V2...');

        // Lock (Mulai Transaksi)
        DB::beginTransaction();

        try {
            // $this->migrateUsers($v1);
            $this->migrateMasterData($v1);
            $this->migrateTransaksi($v1);

            // Jika sukses semua, eksekusi Commit
            DB::commit();
            $this->info('Migrasi selesai dan data berhasil di-commit!');
            
        } catch (\Exception $e) {
            // Jika ada 1 saja yang error, batalkan semua perubahan (Rollback)
            DB::rollBack();
            
            $this->error('Gagal! Terjadi kesalahan, seluruh proses migrasi di-rollback.');
            $this->error('Error: ' . $e->getMessage());
            $this->error('File: ' . $e->getFile() . ' baris ' . $e->getLine());
        }
    }

    private function migrateUsers($v1)
    {
        $this->line('Migrasi Users...');
        $users = DB::connection($v1)->table('users')->get();
        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(
                ['id' => $user->id],
                [
                    'name' => $user->name,
                    'username' => $user->username ?? Str::slug($user->name), 
                    'email' => ($user->username ?? Str::slug($user->name)) , // V1 tidak punya email, kita generate dummy
                    'password' => $user->password,
                    'is_active' => true,
                    'active_role_id' => 1, // Beri role default
                    'created_at' => $user->created_at ?? now(),
                    'updated_at' => $user->updated_at ?? now(),
                ]
            );
        }
    }

    private function migrateMasterData($v1)
    {
        $this->line('Migrasi Master Data (Bandara, Maskapai, Stasiun, dll)...');

        $masters = [
            'bandaras' => 'ticketing_bandara',
            'stasiuns' => 'ticketing_stasiun',
            'maskapais' => 'ticketing_maskapai',
            'keretas' => 'ticketing_kereta',
            'vendors' => 'ticketing_vendor',
            'hotels' => 'ticketing_hotel',
            'unit_kerjas' => 'ticketing_unit_kerja',
            'kategori_pemesanans' => 'ticketing_kategori_pemesanan'
        ];

        foreach ($masters as $oldTable => $newTable) {
            $records = DB::connection($v1)->table($oldTable)->get();
            foreach ($records as $row) {
                $data = (array) $row;
                
                // Hapus deleted_at, ganti jadi is_active
                if (array_key_exists('deleted_at', $data)) {
                    $data['is_active'] = is_null($data['deleted_at']);
                    unset($data['deleted_at']);
                }

                DB::table($newTable)->updateOrInsert(['id' => $data['id']], $data);
            }
        }
    }

    private function migrateTransaksi($v1)
    {
        $this->line('Migrasi Transaksi (Pemesanan, Tiket, Pembayaran)...');
        $defaultUserId = DB::table('users')->first()->id ?? 1;

        // 1. Pemesanan
        $pemesanans = DB::connection($v1)->table('pemesanans')->get();
        foreach ($pemesanans as $p) {
            DB::table('ticketing_pemesanan')->updateOrInsert(
                ['id' => $p->id],
                [
                    'invoice' => $p->invoice,
                    'nama_customer' => $p->nama_customer,
                    'tckt_kategori_pemesanan_id' => $p->kategori_pemesanan_id,
                    'tckt_unit_kerja_id' => $p->unit_kerja_id,
                    'status_pemesanan' => $p->status_pemesanan,
                    'pulang_pergi' => $p->pulang_pergi,
                    'tanggal_pemesanan' => $p->tanggal_pemesanan,
                    'harga_beli' => $p->harga_beli,
                    'harga_publish' => $p->harga_publish,
                    'harga_jual' => $p->harga_jual,
                    'created_by' => $defaultUserId,
                    'created_at' => $p->created_at,
                    'updated_at' => $p->updated_at,
                ]
            );
        }

        // 2. Tiket Pesawat
        $pesawats = DB::connection($v1)->table('tiket_pesawats')->get();
        foreach ($pesawats as $tp) {
            // V1 nyimpen 'jam_tiba' cuma jamnya (08:00:00). V2 mintanya full Datetime.
            // Kita gabung tanggal dari jadwal_berangkat dengan jam_tiba.
            $tgl_berangkat = explode(' ', $tp->jadwal_berangkat_pesawat)[0];
            $jam_tiba_val = $tp->jam_tiba ?? '00:00:00';
            $jadwal_tiba_full = (strlen($jam_tiba_val) <= 8) ? $tgl_berangkat . ' ' . $jam_tiba_val : $jam_tiba_val;

            DB::table('ticketing_tiket_pesawat')->updateOrInsert(
                ['id' => $tp->id],
                [
                    'tckt_pemesanan_id' => $tp->pemesanan_id,
                    'tckt_maskapai_id' => $tp->maskapai_id,
                    'tckt_vendor_id' => $tp->vendor_id,
                    'tckt_bandara_berangkat_id' => $tp->bandara_berangkat_id,
                    'tckt_bandara_tiba_id' => $tp->bandara_tiba_id,
                    'nomor_ticket' => $tp->nomor_ticket ?? null,
                    'nomor_penerbangan' => $tp->nomor_penerbangan ?? null,
                    'kode_booking_pesawat' => $tp->kode_booking_pesawat ?? null,
                    'kelas' => $tp->kelas ?? null,
                    'jenis_penerbangan' => 'Domestik',
                    'jadwal_berangkat_pesawat' => $tp->jadwal_berangkat_pesawat,
                    'jadwal_tiba_pesawat' => $jadwal_tiba_full,
                    'detail_pulang_pergi' => $tp->detail_pulang_pergi ?? null,
                    'zona_waktu' => 'WIB',
                    'zona_waktu_kedatangan' => 'WIB',
                    'created_at' => $tp->created_at,
                    'updated_at' => $tp->updated_at,
                ]
            );
        }
        $this->line('Migrasi Tiket Pesawat Selesai.');

        // 3. Tiket Kereta
        $keretas = DB::connection($v1)->table('tiket_keretas')->get();
        foreach ($keretas as $tk) {
            DB::table('ticketing_tiket_kereta')->updateOrInsert(
                ['id' => $tk->id],
                [
                    'tckt_pemesanan_id' => $tk->pemesanan_id,
                    'tckt_kereta_id' => $tk->kereta_id,
                    'tckt_vendor_id' => $tk->vendor_id,
                    'tckt_stasiun_berangkat_id' => $tk->stasiun_berangkat_id,
                    'tckt_stasiun_tiba_id' => $tk->stasiun_tiba_id,
                    'kode_booking_kereta' => $tk->kode_booking_kereta ?? null,
                    'jadwal_berangkat_kereta' => $tk->jadwal_berangkat_kereta,
                    'jadwal_tiba_kereta' => $tk->jadwal_tiba_kereta ?? $tk->jadwal_berangkat_kereta,
                    'zona_waktu' => 'WIB',
                    'zona_waktu_kedatangan' => 'WIB',
                    'created_at' => $tk->created_at,
                    'updated_at' => $tk->updated_at,
                ]
            );
        }
        $this->line('Migrasi Tiket Kereta Selesai.');

        // 4. Kamar Hotel
        $hotels = DB::connection($v1)->table('kamar_hotels')->get();
        foreach ($hotels as $h) {
            DB::table('ticketing_kamar_hotel')->updateOrInsert(
                ['id' => $h->id],
                [
                    'tckt_pemesanan_id' => $h->pemesanan_id,
                    'tckt_hotel_id' => $h->hotel_id,
                    'tckt_vendor_id' => $h->vendor_id,
                    'jumlah_kamar' => $h->jumlah_kamar ?? null,
                    'lama_menginap' => $h->lama_menginap ?? null,
                    'tipe_kamar' => $h->tipe_kamar ?? null,
                    'jadwal_checkin' => $h->jadwal_checkin,
                    'jadwal_checkout' => $h->jadwal_checkout,
                    'include_breakfast' => $h->include_breakfast ?? null,
                    'zona_waktu' => 'WIB',
                    'created_at' => $h->created_at,
                    'updated_at' => $h->updated_at,
                ]
            );
        }
        $this->line('Migrasi Kamar Hotel Selesai.');

        // 5. Dokumen
        $dokumens = DB::connection($v1)->table('dokumens')->get();
        foreach ($dokumens as $d) {
            DB::table('ticketing_dokumen')->updateOrInsert(
                ['id' => $d->id],
                [
                    'tckt_pemesanan_id' => $d->pemesanan_id,
                    'tckt_vendor_id' => $d->vendor_id,
                    'jenis_dokumen' => $d->jenis_dokumen ?? 'Lainnya',
                    'keterangan' => $d->keterangan ?? null,
                    'created_at' => $d->created_at,
                    'updated_at' => $d->updated_at,
                ]
            );
        }
        $this->line('Migrasi Dokumen Selesai.');

        // 6. Penumpang
        $penumpangs = DB::connection($v1)->table('penumpangs')->get();
        foreach ($penumpangs as $p) {
            DB::table('ticketing_penumpang')->updateOrInsert(
                ['id' => $p->id],
                (array) $p
            );
        }
        
        // 7. Pivot Penumpang & Layanans
        $pivots = [
            ['old' => 'penumpang_tiket_pesawat', 'new' => 'ticketing_penumpang_tiket_pesawat', 'fk' => 'tckt_tiket_pesawat_id', 'old_fk' => 'tiket_pesawat_id'],
            ['old' => 'penumpang_tiket_kereta', 'new' => 'ticketing_penumpang_tiket_kereta', 'fk' => 'tckt_tiket_kereta_id', 'old_fk' => 'tiket_kereta_id'],
            ['old' => 'kamar_hotel_penumpang', 'new' => 'ticketing_kamar_hotel_penumpang', 'fk' => 'tckt_kamar_hotel_id', 'old_fk' => 'kamar_hotel_id'],
            ['old' => 'penumpang_dokumen', 'new' => 'ticketing_penumpang_dokumen', 'fk' => 'tckt_dokumen_id', 'old_fk' => 'dokumen_id'],
        ];

        foreach ($pivots as $pivot) {
            $records = DB::connection($v1)->table($pivot['old'])->get();
            foreach ($records as $r) {
                DB::table($pivot['new'])->updateOrInsert(
                    ['id' => $r->id],
                    [
                        'tckt_penumpang_id' => $r->penumpang_id,
                        $pivot['fk'] => $r->{$pivot['old_fk']},
                        'created_at' => $r->created_at ?? now(),
                        'updated_at' => $r->updated_at ?? now(),
                    ]
                );
            }
        }
        $this->line('Migrasi Pivot Penumpang Selesai.');

        // 8. Pembayaran
        $pembayarans = DB::connection($v1)->table('pembayarans')->get();
        foreach ($pembayarans as $p) {
            DB::table('ticketing_pembayaran')->updateOrInsert(
                ['id' => $p->id],
                [
                    'tckt_pemesanan_id' => $p->pemesanan_id,
                    'tckt_unit_kerja_id' => $p->unit_kerja_id,
                    'nama_pembayar' => $p->nama_pembayar,
                    'created_at' => $p->created_at,
                    'updated_at' => $p->updated_at,
                ]
            );
        }

        $pembayaran_penumpangs = DB::connection($v1)->table('pembayaran_penumpang')->get();
        foreach ($pembayaran_penumpangs as $pp) {
            DB::table('ticketing_pembayaran_penumpang')->updateOrInsert(
                ['id' => $pp->id],
                [
                    'tckt_pembayaran_id' => $pp->pembayaran_id,
                    'tckt_penumpang_id' => $pp->penumpang_id,
                    'jumlah_membayar' => $pp->jumlah_membayar ?? null,
                    'user_id' => $pp->user_id ?? null,
                    'bukti_pembayaran' => $pp->bukti_pembayaran ?? null,
                    'tgl_membayar' => $pp->tgl_membayar ?? null,
                    'status_bukti_bayar' => $pp->status_bukti_bayar ?? null,
                    'created_at' => $pp->created_at,
                    'updated_at' => $pp->updated_at,
                ]
            );
        }
        $this->line('Migrasi Pembayaran Selesai.');
    }
}
