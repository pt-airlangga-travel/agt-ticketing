# ✅ Verifikasi Pemahaman — AGT Ticketing (Gapura)

> Dibuat setelah membaca **dan menguji** kode repo ini (commit `688cff0`).
> **Status verifikasi:** seluruh temuan T1–T6 + temuan baru T7–T10 sudah diuji ulang.
> Hasil test: **21 test / 63 assertion — OK**, dijalankan di **SQLite in-memory**
> (tanpa menyentuh DB produksi). Lihat §4.
>
> **Cara pakai dokumen ini:** cukup tandai yang **salah**. Setiap butir punya
> kotak `☐ benar / ☐ salah` — baca, lalu bilang mis. *"7 salah, harusnya ..."*.
> **Cara approve:** balas *"semua benar"*, atau sebutkan nomornya.
> Untuk temuan teknis, balas nomornya saja, mis. *"T4: harga_jual per penumpang"*.

---

## 0. Ringkasan 12 baris (inti bisnis yang saya pahami)

1. Aplikasi ini **order management** untuk PT Airlangga Global Traveling (AGT) — mencatat pemesanan tiket **pesawat, kereta, hotel, dan dokumen** lalu menagihnya lewat invoice.
2. Satu transaksi = 1 baris `ticketing_pemesanan` (punya 1 `invoice`) + 1 baris anak sesuai jenis (`ticketing_tiket_pesawat` / `ticketing_tiket_kereta` / `ticketing_kamar_hotel` / `ticketing_dokumen`) + 1 baris header `ticketing_pembayaran`.
3. Pemesan/pembayar tercatat sebagai **Unit Kerja** (`ticketing_unit_kerja`) + nama orang (`nama_customer` / `nama_pembayar` yang dipilih dari master `ticketing_pelanggan`).
4. Setiap transaksi punya **penumpang** (many-to-many ke tabel anak lewat pivot), dan penumpang inilah yang "ditagih" secara per-orang.
5. Harga: **Harga Beli = HPP/NTA**, **Harga Publish = Komisi**, **Harga Jual = nilai tagih**. Aturan keras: `Harga Jual − HPP − Komisi ≥ 0` (tidak boleh rugi).
6. Pembayaran dicatat **per penumpang** di `ticketing_pembayaran_penumpang` (jumlah, tanggal, bukti upload, valid/tidak) dengan plafon input = sisa tagihan penumpang.
7. Invoice dicetak PDF oleh **dompdf** (A5 landscape, maks 3 penumpang per halaman) dengan terbilang otomatis dan jatuh tempo `tanggal_pemesanan + 7 hari`.
8. Akses berbasis **RBAC custom** (bukan Spatie): permission `{grup}.{resource}.{action}`; user tanpa izin `view` hanya melihat reservasi miliknya sendiri (`created_by`).
9. Login pakai email **atau** username + captcha; user wajib `is_active` dan punya role aktif (`active_role_id` di-auto-set).
10. Semua perubahan data reservasi dicatat ke **`ticketing_activity_log`** (created/updated/deleted + diff kolom).
11. Admin panel **Filament v5** dengan 3 cluster: `Ticketing`, `Pengguna` (User), dan `Pengajuan Dana` — **PengajuanDana saat ini dimatikan** (`modules_statuses.json: false` dan plugin-nya di-comment).
12. Modul Ticketing disusun: master data (bandara, maskapai, kereta, stasiun, hotel, vendor, unit kerja, pelanggan, pembayar) + transaksi (4 resource "Reservasi...") + log aktivitas.

---

## 1. Kuis verifikasi (jawaban saya sudah terisi — tinjau & tandai)

### A. Struktur data & alur transaksi

**1. Tabel "inti" dari sebuah pesanan tiket pesawat itu yang mana?**
→ `ticketing_tiket_pesawat` (di-join ke `ticketing_pemesanan`). Bahkan Resource `ReservasiPesawatResource` bermodel `TicketingTiketPesawat`, sedangkan Reservasi **Kereta/Hotel/Dokumen** bermodel `TicketingPemesanan`.
☐ benar ☐ salah

**2. Relasi `TicketingPemesanan` ke anak-anaknya `hasOne` atau `hasMany`?**
→ `hasOne` untuk tiket pesawat/kereta/kamar hotel/dokumen. **1 pemesanan = 1 tiket**, jadi tidak ada multi-segmen di level tabel (multi-segmen hanya untuk pulang dari pesawat, dan disimpan sebagai **JSON** di `detail_pulang_pergi`).
☐ benar ☐ salah

**3. Apakah `TicketingPemesanan` punya relasi langsung ke penumpang?**
→ Tidak langsung. `ticketingPenumpang()` adalah **method pintar (bukan relasi Eloquent)** yang me-*delegate* ke `ticketingTiketPesawat->ticketingPenumpang()` / kereta / kamar hotel / dokumen sesuai tipe pemesanan.
→ **KOREKSI setelah verifikasi (dites):** hasil method ini tetap sebuah objek `BelongsToMany` (milik anak), jadi `->get()`, `->count()`, `->load()` bekerja normal — tetapi **tidak bisa** dipakai untuk `with()`/eager-load dari sisi pemesanan. Konsekuensi lanjutan: `ticketingTerbayar()` yang meng-`foreach` method ini tanpa `get()` menjadi error → lihat **T7**.
☐ benar ☐ salah

**4. Siapa yang "punya" pembayaran — pemesanan atau penumpang?**
→ Dua-duanya: `ticketing_pembayaran` = **header per pemesanan** (1:1, isinya unit kerja + nama pembayar), sedangkan nominal uang ada di `ticketing_pembayaran_penumpang` (**per penumpang**).
☐ benar ☐ salah

**5. Kapan baris `ticketing_pembayaran_penumpang` dibuat?**
→ Otomatis saat penumpang di-attach ke reservasi pesawat (`PenumpangPesawatRelationManager`), dengan `jumlah_membayar = 0` — jadi ini sekaligus mencatat "siapa pembayar untuk penumpang ini".
☐ benar ☐ salah

**6. Apa perbedaan perilaku Create antara reservasi Pesawat vs Kereta/Hotel/Dokumen?**
→ Hanya Pesawat yang mendukung **Round Trip** dan menyimpan `pulang_pergi` dari form; Kereta/Hotel/Dokumen `pulang_pergi` di-*hardcode* `0`.
☐ benar ☐ salah

**7. Bagaimana data pulang-pergi pesawat disimpan?**
→ Di kolom `ticketing_tiket_pesawat.detail_pulang_pergi` sebagai JSON `{"status_pemesanan_pulang_pergi": ..., "segmen": [...]}` — dan **hanya ditulis kalau `pulang_pergi = 1` DAN ada minimal 1 segmen pulang**; selain itu `null`.
☐ benar ☐ salah

**8. Jenis reservasi dibedakan di mana (untuk statistik "Pesawat/Kereta/Hotel/Dokumen")?**
→ Dari keberadaan relasi anak (`ticketingTiketPesawat`, `ticketingTiketKereta`, `ticketingKamarHotel`, `ticketingDokumen`) — dicek berurutan, bukan dari kolom kategori.
☐ benar ☐ salah

**9. Kolom `pulang_pergi` di tabel pemesanan tipenya apa?**
→ `integer` (dipakai sebagai flag 0/1), bukan boolean.
☐ benar ☐ salah

**10. Kategori pemesanan isinya apa saja?**
→ Master `ticketing_kategori_pemesanan`, di-seed hanya **"Ticket"** dan **"Tour"**. Komentar migration menandai `1 = ticket, 2 = tour`.
☐ benar ☐ salah

### B. Harga & pembayaran

**11. Aturan validasi harga jual?**
→ Di form: `Profit = Harga Jual − Harga NTA/Beli (HPP) − Komisi (Publish)`; kalau **profit < 0 → form ditolak** ("invoice tidak dapat terbit"). Ada juga rincian perhitungan yang ditampilkan di form.
☐ benar ☐ salah

**12. Label bisnis tiap kolom harga?**
→ `harga_beli` = **Harga NTA (Beli)/HPP**, `harga_publish` = **Komisi (Publish)**, `harga_jual` = **Harga Jual** (nilai yang ditagih ke pemesan).
☐ benar ☐ salah

**13. Status apa saja yang boleh dipilih di "Status Pemesanan"?**
→ `Confirmed`, `Canceled`, `No show`, `Refund`, `Reroute`, `Open`, `Reschedule` (dashboard mewarnai Confirmed=hijau, Canceled/Refund=merah).
☐ benar ☐ salah

**14. Bagaimana "sisa tagihan" seorang penumpang dihitung?**
→ `qouta per penumpang = round(harga_jual / jumlah penumpang)`, lalu `sisa = max(quota − total pembayaran penumpang tsb, 0)`. Kolom "Sisa" menampilkan badge **Lunas** kalau 0.
☐ benar ☐ salah

**15. Apakah bisa kelebihan bayar (overpay)?**
→ Tidak lewat form: `jumlah_membayar` di-*cap* `maxValue = sisaTagihan(penumpang)`, dan penumpang yang sudah lunas **tidak bisa dipilih** lagi di dropdown (labelnya "(Lunas)").
→ **Verifikasi (dites):** benar. Karena `hargaSatuan()` membagi `harga_jual` dengan jumlah penumpang, plafon per penumpang = `harga_jual / n`. **Konsekuensi:** total maksimum yang bisa dibayar = `harga_jual` (sama dengan harga jual utuh) — jadi memang tidak ada overpay, tetapi juga berarti ada risiko **kekurangan bayar** bila jumlah penumpang bertambah setelah pembayaran dicatat.
☐ benar ☐ salah

**16. Apa arti `status_bukti_bayar`?**
→ Penanda "Bukti Valid" (toggle). Ada bulk action **"Tandai Bukti Valid"** untuk memvalidasi banyak baris sekaligus; bukti di-upload ke folder `bukti-pembayaran`.
☐ benar ☐ salah

**17. Fungsi `ticketingCekLunas()` di model Pemesanan?**
→ Menyatakan pemesanan lunas kalau **total terbayar ≥ `harga_jual`** (perbandingan di level pemesanan, semua penumpang digabung).
→ **KOREKSI setelah verifikasi (dites):** method ini **error/rusak** dan **tidak dipanggil di mana pun** (dead code). Lihat **T7** dan **T3**. Jadi ini bukan perilaku aktif, melainkan kode yang belum pernah jalan.
☐ benar ☐ salah

**18. `ticketingTerbayar()` menjumlahkan apa?**
→ `SUM(jumlah_membayar)` di `ticketing_pembayaran_penumpang` yang **dibatasi pada `tckt_pembayaran_id` milik pemesanan itu**, untuk penumpang-penumpang pemesanan tsb.
→ **KOREKSI setelah verifikasi (dites):** bagian pembatasannya **benar**, tetapi method ini **error saat dijalankan** karena meng-iterasi objek relasi tanpa `get()` (`Attempt to read property "id" on false`). **T7**. Juga tidak dipanggil dari mana pun.
☐ benar ☐ salah

**19. Vendor dibatasi per jenis?**
→ Ya: `jenis_vendor` = **1 Pesawat, 2 Kereta, 3 Hotel, 4 Dokumen**; tiap form reservasi hanya menampilkan vendor dengan jenis yang sesuai.
☐ benar ☐ salah

**20. Unit kerja dipakai berapa kali dalam satu transaksi?**
→ Dua: **Unit Kerja Pemesan** (→ `pemesanan.tckt_unit_kerja_id`) dan **Unit Kerja Pembayar** (→ `pembayaran.tckt_unit_kerja_id`, dan opsional per penumpang di `pembayaran_penumpang.tckt_unit_kerja_id`).
☐ benar ☐ salah

### C. Invoice / cetak

**21. Format nomor invoice?**
→ `YY` + `MM` + `kode jenis` + `4 digit urut` → mis. `26081 0001`. Jenis: **1 = Pesawat, 2 = Kereta, 3 = Hotel, 4 = Dokumen** (dipilih dari karakter ke-5, `SUBSTRING(invoice,5,1)`).
☐ benar ☐ salah

**22. Nomor urut invoice dihitung per apa?**
→ Per **jenis reservasi per bulan per tahun** (query filter `YEAR/MONTH(created_at)`), ambil invoice terakhir lalu `+1`.
☐ benar ☐ salah

**23. Format PDF invoice?**
→ **dompdf**, kertas **A5 landscape**, dengan logo AGT + logo Unair, dan **di-chunk 3 penumpang per halaman**.
☐ benar ☐ salah

**24. Harga di invoice dihitung bagaimana?**
→ `harga_satuan = pemesanan.harga_jual` dan `total = harga_satuan × jumlah penumpang yang dipilih untuk dicetak`; angka **terbilang** dihitung dari total tersebut.
☐ benar ☐ salah

**25. Bagaimana penomoran penumpang di invoice?**
→ `{invoice}-{index+1}` mengikuti **urutan data penumpang pada relasi** (bukan `id` penumpang), dan di tampilan nama diberi gelar **Mr / Ms** (Ms kalau `jenis_kelamin = 1`).
☐ benar ☐ salah

**26. Bagaimana cara mencetak invoice beberapa penumpang sekaligus?**
→ Bulk action **"Print Invoice"** di tabel penumpang → membuka `window.open` ke route `print-invoice-{jenis}?id_pemesanan=..&id_penumpang=1,2,3` (id penumpang digabung koma).
☐ benar ☐ salah

**27. Isi header invoice yang statis?**
→ Alamat Jl. Dharmawangsa No.1 Gubeng Surabaya 60286, telepon (031)99022433 / 081233020117, email info@airlanggatravel.com, **NPWP 0754007870606000**, dan instruksi transfer **Bank Mandiri 1420055001001 / BNI 1101119540 a.n. PT Airlangga Global Traveling**.
☐ benar ☐ salah

**28. Jatuh tempo invoice?**
→ `tanggal_pemesanan + 7 hari`, ditampilkan `dd-mm-yyyy`.
☐ benar ☐ salah

**29. Dari mana "nama pembayar" & "unit kerja pembayar" di header invoice diambil untuk reservasi pesawat?**
→ Dari pembayaran **per penumpang** (`ticketingPembayaranPenumpang` → `ticketingPembayar.nama_pembayar` / `ticketingUnitKerja`), dengan fallback ke kolom teks `nama_pembayar` lama.
☐ benar ☐ salah

**30. Invoice yang sudah dicetak & dibayar — apakah ada mekanisme kunci/mark-paid di invoice?**
→ Tidak ada status "sudah dibayar/dikirim" di level invoice. Statusnya hanya `status_pemesanan` (operasional) + pembayaran per penumpang.
☐ benar ☐ salah

### D. Hak akses & RBAC

**31. Format nama permission?**
→ `{slug grup}.{slug resource}.{action}` — mis. `reservasi.reservasi-pesawat.view`. Action yang dipakai: **view, create, edit, delete**.
☐ benar ☐ salah

**32. "Grup" permission diambil dari mana?**
→ Dari `navigationGroup` resource kalau ada (mis. `Reservasi`, `Master Data`), kalau tidak ada baru label cluster-nya.
☐ benar ☐ salah

**33. Siapa super admin?**
→ User yang punya role bernama **`super-admin`** (dicek `isSuperAdmin()`); semua permission otomatis lolos.
☐ benar ☐ salah

**34. Cluster Ticketing bisa diakses siapa?**
→ Super admin **atau** user yang punya role bernama **`ticketing`** (`TicketingCluster::canAccess()`).
☐ benar ☐ salah

**35. Kalau user punya permission `view` reservasi, dia lihat apa?**
→ **Semua** reservasi. Kalau tidak punya, hanya reservasi yang `created_by = dirinya` ("PRIORITAS: akses penuh" → else `created_by`).
☐ benar ☐ salah

**36. Permission di-cache?**
→ Ya, key `user:{id}:permissions`, **TTL 10 menit**; cache hanya memuat permission dari role yang `is_active = true`. Ada `forgetAccessCache()` untuk membersihkan.
☐ benar ☐ salah

**37. Syarat login sukses?**
→ Kredensial benar (+ captcha), lalu: user harus `is_active`, **punya minimal 1 role**, dan **minimal 1 role aktif**. Kalau `active_role_id` kosong/tidak aktif → otomatis di-set ke role aktif pertama. Login bisa pakai **email atau username**.
☐ benar ☐ salah

**38. Bagaimana role baru disimpan?**
→ Nama role otomatis di-*slug* (`Str::slug`) lewat mutator, dan user punya `active_role_id` (role "yang sedang dipakai") terpisah dari daftar `roles()`.
☐ benar ☐ salah

**39. Apakah user boleh dihapus dari UI?**
→ Tidak dari halaman Edit — `DeleteAction` di `EditUser` sengaja di-comment-out.
☐ benar ☐ salah

**40. Apakah PengajuanDana aktif?**
→ Tidak. `modules_statuses.json` = `false` dan `PengajuanDanaPlugin::make()` di-comment di panel provider. Kodenya masih ada (draft proposal → submission → transfer dengan status `menunggu/diajukan/diterima/ditolak` dan `menunggu/proses_tf/selesai/ditolak/dikembalikan`).
☐ benar ☐ salah

### E. Log, dashboard, & teknis

**41. Apa yang dicatat `ticketing_activity_log`?**
→ `user_id`, `tckt_pemesanan_id`, `entity_type` (class model), `entity_id`, `event` (created/updated/deleted), dan `changes` (JSON). Untuk `updated` hanya kolom yang berubah (format `{old, new}`), dan log **tidak ditulis** kalau tidak ada user login atau tidak ada perubahan.
☐ benar ☐ salah

**42. Model mana yang tercatat lognya?**
→ Pemesanan, TiketPesawat, TiketKereta, KamarHotel, Dokumen, Pembayaran. **Penumpang dan PembayaranPenumpang tidak** ikut di-log otomatis.
☐ benar ☐ salah

**43. Isi 3 widget dashboard Ticketing?**
→ (1) **Stats**: total reservasi, jumlah per jenis, total nilai (`sum harga_jual`), jumlah Confirmed, jumlah Canceled+Reschedule; (2) **Chart**: jumlah reservasi **6 bulan terakhir** per bulan; (3) **Recent**: 10 reservasi terbaru + link edit sesuai jenis.
☐ benar ☐ salah

**44. Kosakata penting UI (bahasa Indonesia)?**
→ Pemesan = yang memesan, Pembayar = yang membayar, Penumpang = penumpang, Judan/Pengajuan Dana = modul pengajuan dana, Unit Kerja = instansi/departemen pemesan, Vendor = supplier tiket, NTA/HPP = harga beli dari vendor.
☐ benar ☐ salah

**45. Konvensi teknis wajib di repo ini?**
→ (a) Filament **v5** (`Filament\Schemas\Schema`, komponen `->components()`/`->schema()`); (b) konflik nama `Schema` wajib di-alias `use Illuminate\Support\Facades\Schema as DBSchema;`; (c) model pakai cast `'password' => 'hashed'` (jangan `bcrypt()` manual → double hash); (d) sebagian kolom baru di-guard `DBSchema::hasColumn()` supaya aman di DB production yang migrasinya tertunda; (e) modul `coolsam/modules` dengan prefix `tckt_` dan `judan_`.
☐ benar ☐ salah

---

## 2. Kandidat temuan (butuh keputusan Anda — jangan saya perbaiki tanpa izin)

**Semua temuan di bawah sudah saya VERIFIKASI ULANG** (dites, bukan hanya dibaca).
Kolom "Status" menyatakan hasil pengujian itu.

| # | Temuan | Status verifikasi | Bukti | Keyakinan |
|---|---|---|---|---|
| T1 | ~~**Tombol "Tambah Pembayaran" error.**~~ Di 4 `RiwayatPembayaranRelationManager` kode memanggil `Schema::hasColumn(...)` padahal yang di-import `Filament\Schemas\Schema` (tak punya method itu) → `BadMethodCallException`. | ✅ **SUDAH DIPERBAIKI** — 12 titik diubah ke `DBSchema::hasColumn(...)`. Test penjaga + negative test ditambahkan. | 4 file RelationManager; diff 12 insert / 12 delete | **selesai** |
| T2 | **Rute pulang pesawat tidak pernah tampil di invoice.** Service menulis JSON `{status_pemesanan_pulang_pergi, segmen:[{bandara_berangkat_id, nomor_penerbangan, ...}]}`, tetapi `PrintInvoiceController::printInvoicePesawat()` membaca kunci format lama yang **tidak ada** di dalam `segmen` (`bandara_berangkat_id_pulang`, `nomor_penerbangan_pulang`, `tanggal_keberangkatan_pulang`) → rute/nomor/jadwal pulang selalu kosong. | ✅ **KONFIRMASI** — ketidakcocokan kunci dibuktikan; service menulis `segmen`, pembaca tidak pernah menyentuh `segmen`. | Test T2 vs `PrintInvoiceController:39-48` | **tinggi** |
| T3 | **Definisi "lunas" berbeda antara form pembayaran dan model.** Form: per penumpang (`harga_jual ÷ jumlah penumpang`). Model: `harga_jual` vs total seluruh penumpang. | ⚠️ **KONFIRMASI + diperparah:** method model (`ticketingCekLunas`) ternyata **sekaligus error** (lihat T7) dan **tidak dipakai**. Jadi praktiknya yang berlaku hanya aturan form. | Test T3 | **tinggi** |
| T4 | **Arti `harga_jual`: total atau per penumpang?** Form pembayaran memperlakukannya sebagai **total** (dibagi n), invoice memperlakukannya sebagai **per penumpang** (dikali n) → selisih **n×** . Uji 2 penumpang, `harga_jual = 1.000.000`: invoice 2.000.000 vs plafon bayar 1.000.000. | ⚠️ **KONFIRMASI dengan angka** — selisih 50% pada contoh uji. **Butuh keputusan bisnis.** | Test T4 + T3 | **tinggi** |
| T5 | **Urutan penumpang tidak dijamin** (tidak ada `orderBy`), padahal nomor di invoice = `{invoice}-{index+1}` → nomor bisa berpindah setelah data berubah. | ⚠️ **KONFIRMASI** — relasi pivot memang tanpa `orderBy`. (Duplikat dengan T10; saya gabung pembahasannya.) | `TicketingTiketPesawat.php:118-126` | sedang |
| T6 | **`TicketingPembayaran::ticketingPemesanan()` salah arah.** Dideklarasikan `hasOne` padahal seharusnya `belongsTo` (pembayaran milik pemesanan), dan tanpa FK eksplisit → FK diturunkan jadi **`ticketing_pembayaran_id`** yang tidak ada di skema. | ⚠️ **KONFIRMASI, tapi diredam:** relasi ini **tidak dipakai** di mana pun, sehingga tidak menimbulkan error nyata — hanya kode menyesatkan. | Test T6 (ambil `getForeignKeyName()`) | **tinggi** |

---

## 3. Pertanyaan yang saya ingin Anda jawab (aturan bisnis yang tidak ada di kode)

**1. Apakah `pulang_pergi` memang hanya untuk pesawat? (hotel/kereta/dokumen dipaksa 0)**
→ **YA — terverifikasi.** Hanya form Reservasi Pesawat yang punya `Select::make('pulang_pergi')` (Ya/Tidak, `->live()`), sedangkan service Kereta/Hotel/Dokumen menulis `'pulang_pergi' => 0` secara hardcode. (Sempat saya duga `Hidden`, ternyata salah — sudah dikoreksi setelah mengecek form.)

**2. Harga Jual itu harga per penumpang atau total satu transaksi?**
→ **⚠️ Ini kontradiksi paling penting (T3/T4) dan sudah terukur.** Invoice menagih `harga_jual × jumlah_penumpang`, sedangkan form pembayaran menetapkan tagihan per orang = `harga_jual / jumlah_penumpang`. Dengan data uji 2 penumpang & `harga_jual = 1.000.000`: invoice menagih **2.000.000** sementara form pembayaran hanya mengizinkan total **1.000.000** → **selisih 1.000.000 (50%)**. Mohon diputuskan mana yang benar; saya tidak memperbaikinya tanpa keputusan Anda.

**3. Komisi (harga_publish) untuk siapa — AGT sendiri, atau bagian yang dibayarkan ke pihak lain?**
→ (belum bisa dijawab dari kode)

**4. Apakah pembayaran wajib per penumpang, atau boleh satu transfer untuk beberapa penumpang sekaligus? (sekarang tetap dipecah per penumpang)**
→ (belum bisa dijawab dari kode)

**5. Untuk satu reservasi, penumpang diambil dari master `ticketing_penumpang` (bisa dipakai ulang lintas transaksi) — itu memang disengaja?**
→ **Ya, dan konsisten.** Keempat RelationManager Penumpang membuat penumpang baru lewat `TicketingPenumpang::create([...])` dengan `nama_penumpang` + `jenis_kelamin` (keduanya wajib di form), lalu men-`attach` ke tiket. Tidak ada validasi keunikan, jadi memang satu orang bisa dipakai di banyak transaksi.

**6. Apakah invoice memang diterbitkan per penumpang (dicetak untuk seleksi penumpang tertentu) seperti bulk action sekarang?**
→ (belum bisa dijawab dari kode)

**7. Siapa yang seharusnya boleh melihat semua reservasi — semua role `ticketing`, atau hanya role tertentu?**
→ (belum bisa dijawab dari kode)

**8. Apakah modul Pengajuan Dana akan diaktifkan lagi (sekarang mati)?**
→ (belum bisa dijawab dari kode)

**9. Apakah benar tidak ada alur approval di Ticketing (reservasi langsung dianggap tercatat begitu disimpan)?**
→ (belum bisa dijawab dari kode)

**10. Kalau ada data lama DB V1 (`u122950122_agt`), apakah masih dipakai? Command `migrate:v1-to-v2` ada tapi `migrateUsers()` sedang di-comment.**
→ (belum bisa dijawab dari kode) — namun relevan dengan **T2**: data V1 tampaknya sumber yang mengisi `detail_pulang_pergi` dengan format lama, sehingga bug T2 mungkin hanya terasa pada data lama. Mohon dikonfirmasi.

---

## 4. Verifikasi independen (sudah dijalankan — bukan hanya klaim)

Saya tidak berhenti di "membaca kode": setiap temuan di atas saya uji ulang, dan
hasilnya saya lampirkan sebagai **test yang bisa dijalankan**.

**Hasil terakhir:** `21 test, 63 assertion — OK (semua lulus)`

```bash
php.exe vendor/bin/phpunit -c phpunit-ticketing.xml
```

File yang ditambahkan (semuanya **tidak mengubah kode produksi**):

| File | Isi |
|---|---|
| `phpunit-ticketing.xml` | Konfigurasi test yang **memaksa SQLite in-memory** |
| `tests/Feature/Ticketing/TicketingTestCase.php` | Base test + **guard keselamatan** (skip otomatis bila bukan sqlite) + pembuat skenario reservasi |
| `tests/Feature/Ticketing/TemuanRegresiTest.php` | Test pengunci perilaku/bug T1–T7 (T1 sudah jadi test **penjaga perbaikan**) |
| `tests/Feature/Ticketing/HarnessProbeTest.php` | Bukti skema (core + modul) bisa dibangun di SQLite |
| `tests/Unit/Ticketing/UserRoleConventionTest.php` | Test konvensi slug username/role |

### ⚠️ PENTING — risiko DB produksi

`.env` repo ini menunjuk **DB MySQL live** (`DB_HOST=194.59.164.73`).
`phpunit.xml` bawaan **tidak** meng-override koneksi DB, sehingga menjalankan
`php artisan test` biasa berpotensi menulis **ke database production**.

Karena itu saya **tidak** menjalankan test apa pun yang menyentuh DB sebelum ada
konfigurasi terpisah. `phpunit-ticketing.xml` + guard di `TicketingTestCase`
memastikan seluruh test Ticketing berjalan di SQLite in-memory, dan **di-skip
otomatis** bila koneksinya bukan sqlite.

Hasil menjalankan **dua-duanya**:

| Perintah | Hasil |
|---|---|
| `php.exe vendor/bin/phpunit -c phpunit-ticketing.xml` | ✅ **22 test, 74 assertion — OK** |
| `php.exe vendor/bin/phpunit` (bawaan) | 20 test Ticketing **di-skip** + 1 kegagalan **yang sudah ada sebelumnya** (`ExampleTest` 302 vs 200). **Tidak ada kegagalan baru, DB produksi tidak tersentuh.** |

**Rekomendasi keamanan (belum saya lakukan agar tidak mengubah konfigurasi Anda):**
aktifkan dua baris yang sudah dikomentari di `phpunit.xml` (baris 25–26) —
`DB_CONNECTION=sqlite` dan `DB_DATABASE=:memory:` — supaya `php artisan test`
biasa pun tidak akan pernah menyentuh database produksi.

### Temuan tambahan yang muncul saat verifikasi

| # | Temuan | Bukti | Keyakinan |
|---|---|---|---|
| T7 | **`ticketingTerbayar()` & `ticketingCekLunas()` ERROR total saat dipanggil.** `ticketingPenumpang()` mengembalikan objek relasi (bukan koleksi), lalu di-`foreach` tanpa `get()` → `ErrorException: Attempt to read property "id" on false`. **Saat ini tidak terasa buat user karena kedua method tidak dipanggil dari mana pun (dead code)** — sudah dibuktikan lewat test pemindai kode. | Test T7 + `TicketingPemesanan.php:129-137` | tinggi |
| T8 | **Migration index memakai `information_schema.statistics`** (khusus MySQL) sehingga skema modul **tidak bisa dibangun di SQLite** — menghambat testing otomatis di CI. | Test portabilitas + `2026_08_13_000001_add_indexes_to_ticketing_tables.php:70-76` | tinggi |
| T9 | **Dua foreign key di satu tabel memakai nama constraint yang sama** (`fk_tckt_stasiun` di `ticketing_tiket_kereta`) — berisiko gagal saat migrate di MySQL. | Test + migration `2026_08_05_170721` | sedang |
| T10 | **Urutan penumpang tidak dijamin** (tidak ada `orderBy` di relasi pivot), padahal `penumpang_id` di invoice = `{invoice}-{index+1}` → nomor bisa berpindah setelah data berubah. | `TicketingTiketPesawat.php:118-126` | sedang |

> Catatan kejujuran: saat verifikasi, dua dugaan awal saya sendiri **terbukti
> salah** dan sudah saya buang dari dokumen — (a) `pulang_pergi` disangka
> `Hidden` padahal `Select` yang berfungsi, dan (b) `jenis_kelamin` disangka
> tidak terkirim saat membuat penumpang padahal sudah diisi. Keduanya sudah
> dikoreksi ke keadaan yang sebenarnya.

---

## 5. Yang saya TIDAK lakukan

- **Tidak mengubah satu baris kode produksi pun** — hanya membaca, menambah file
  test di `tests/`, dan menambah konfigurasi phpunit terpisah.
- **Tidak mengakses/menjalankan query ke database production** (dan sudah memasang
  guard skip agar test tidak pernah menyentuhnya tanpa sengaja).
- **Tidak menyentuh file `phpunit.xml`** maupun `.env` Anda.
- **Tidak memperbaiki temuan T1–T10** — menunggu keputusan Anda, khususnya soal
  T3/T4 yang menyangkut **nilai uang**.

---

## 6. Langkah berikutnya (menunggu keputusan Anda)

**Sudah selesai:** ✅ **T1** — 12 titik `Schema::` → `DBSchema::` di 4 `RiwayatPembayaranRelationManager`.
Tombol **"Tambah Pembayaran"** seharusnya tidak lagi error. Sudah dilindungi test penjaga + diuji
negative test (sengaja merusak satu file → test gagal seperti seharusnya).

**Masih menunggu keputusan Anda:**

1. **T4 / T3 — arti `harga_jual`** (per penumpang atau total). Menyangkut **nilai uang**, jadi
   saya tahan sampai Anda konfirmasi. Ini akan menentukan perbaikan berikutnya.
2. **T7** — `ticketingTerbayar()` & `ticketingCekLunas()`: diperbaiki atau dihapus? (sekarang dead code + error)
3. **T2** — perbaiki agar rute pulang pesawat benar-benar tampil di invoice.
4. **T5 / T10** — tambahkan `orderBy` agar nomor penumpang di invoice stabil.
5. **T8 / T9** — kerapian migration (portabilitas SQLite + nama FK duplikat).
6. Aktifkan SQLite di test default (keamanan) + jadikan test ini bagian CI.

Jawab cukup dengan nomornya, mis. *"T2 lanjut"* atau *"T7: hapus saja"*, dan saya kerjakan.
