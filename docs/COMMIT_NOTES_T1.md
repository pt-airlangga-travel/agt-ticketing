# Rekomendasi commit

Perubahan sesi ini **belum di-commit** — sengaja, menunggu keputusan Anda.
Berikut cara memisahkannya agar riwayat git tetap bersih dan mudah di-revert.

## 1) Perbaikan bug (kode produksi)

```
fix(ticketing): pakai DBSchema::hasColumn() di RiwayatPembayaranRelationManager

Empat RelationManager Riwayat Pembayaran (pesawat/kereta/hotel/dokumen)
memanggil Schema::hasColumn() padahal yang di-import adalah
Filament\Schemas\Schema (tidak punya method itu), sehingga tombol
"Tambah Pembayaran" melempar BadMethodCallException.

Diubah 12 titik (4 file x 3 kolom) menjadi DBSchema::hasColumn().
```

File:

- `Modules/Ticketing/app/Filament/Clusters/Ticketing/Resources/ReservasiPesawats/RelationManagers/RiwayatPembayaranRelationManager.php`
- `Modules/Ticketing/app/Filament/Clusters/Ticketing/Resources/ReservasiKeretas/RelationManagers/RiwayatPembayaranRelationManager.php`
- `Modules/Ticketing/app/Filament/Clusters/Ticketing/Resources/ReservasiHotels/RelationManagers/RiwayatPembayaranRelationManager.php`
- `Modules/Ticketing/app/Filament/Clusters/Ticketing/Resources/ReservasiDokumens/RelationManagers/RiwayatPembayaranRelationManager.php`

## 2) Test + dokumen (tidak menyentuh kode produksi)

```
test(ticketing): tambah harness SQLite in-memory + test verifikasi T1-T10

- phpunit-ticketing.xml memaksa DB_CONNECTION=sqlite / :memory: supaya test
  modul Ticketing tidak pernah menyentuh DB MySQL produksi (.env repo ini
  menunjuk host produksi).
- tests/Feature/Ticketing/* : guard skip, skenario reservasi, test penjaga T1,
  test pengunci T2-T7, dan bukti skema bisa dibangun di SQLite.
- tests/Unit/Ticketing/* : konvensi slug username/role.
- docs/VERIFIKASI_PEMAHAMAN_AGT_TICKETING.md : peta business logic + temuan
  T1-T10 (T1 selesai, sisanya menunggu keputusan pemilik produk).
```

## Verifikasi

```bash
php.exe vendor/bin/phpunit -c phpunit-ticketing.xml   # 22 test, 74 assertion - OK
php.exe vendor/bin/phpunit                             # test Ticketing di-skip, tanpa kegagalan baru
```

## Catatan

- Jangan jalankan `php artisan test` polos untuk test yang menyentuh DB sebelum
  baris `DB_CONNECTION=sqlite` + `DB_DATABASE=:memory:` diaktifkan di `phpunit.xml`
  (baris 25-26, saat ini masih dikomentari).
- T4 (arti `harga_jual`) sengaja belum disentuh karena menyangkut nilai uang dan
  masih menunggu konfirmasi pemilik produk.
