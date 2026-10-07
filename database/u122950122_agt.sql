-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Waktu pembuatan: 03 Okt 2026 pada 15.02
-- Versi server: 11.8.9-MariaDB-log
-- Versi PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u122950122_agt`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `bandaras`
--

CREATE TABLE `bandaras` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_bandara` varchar(255) NOT NULL,
  `kode_bandara` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `dokumens`
--

CREATE TABLE `dokumens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `vendor_id` bigint(20) UNSIGNED NOT NULL,
  `pemesanan_id` bigint(20) UNSIGNED NOT NULL,
  `jenis_dokumen` varchar(255) NOT NULL,
  `keterangan` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `hotels`
--

CREATE TABLE `hotels` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_hotel` varchar(255) NOT NULL,
  `bintang` int(11) DEFAULT NULL,
  `kota` varchar(255) DEFAULT NULL,
  `telepon` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `kamar_hotels`
--

CREATE TABLE `kamar_hotels` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `hotel_id` bigint(20) UNSIGNED NOT NULL,
  `vendor_id` bigint(20) UNSIGNED NOT NULL,
  `pemesanan_id` bigint(20) UNSIGNED NOT NULL,
  `jumlah_kamar` int(11) NOT NULL,
  `lama_menginap` int(11) NOT NULL,
  `tipe_kamar` varchar(255) NOT NULL,
  `jadwal_checkin` datetime NOT NULL,
  `jadwal_checkout` datetime NOT NULL,
  `include_breakfast` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `kamar_hotel_penumpang`
--

CREATE TABLE `kamar_hotel_penumpang` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `penumpang_id` bigint(20) UNSIGNED NOT NULL,
  `kamar_hotel_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori_pemesanans`
--

CREATE TABLE `kategori_pemesanans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_kategori` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `keretas`
--

CREATE TABLE `keretas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_kereta` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `maskapais`
--

CREATE TABLE `maskapais` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_maskapai` varchar(255) NOT NULL,
  `logo_maskapai` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembayarans`
--

CREATE TABLE `pembayarans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `unit_kerja_id` bigint(20) UNSIGNED NOT NULL,
  `pemesanan_id` bigint(20) UNSIGNED NOT NULL,
  `nama_pembayar` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembayaran_penumpang`
--

CREATE TABLE `pembayaran_penumpang` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `penumpang_id` bigint(20) UNSIGNED NOT NULL,
  `pembayaran_id` bigint(20) UNSIGNED NOT NULL,
  `jumlah_membayar` int(11) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  `tgl_membayar` date DEFAULT NULL,
  `status_bukti_bayar` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pemesanans`
--

CREATE TABLE `pemesanans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `invoice` varchar(255) NOT NULL,
  `nama_customer` varchar(255) NOT NULL,
  `kategori_pemesanan_id` bigint(20) UNSIGNED NOT NULL,
  `unit_kerja_id` bigint(20) UNSIGNED NOT NULL,
  `status_pemesanan` varchar(255) NOT NULL,
  `pulang_pergi` int(11) NOT NULL,
  `tanggal_pemesanan` date NOT NULL,
  `harga_beli` bigint(20) NOT NULL,
  `harga_publish` bigint(20) NOT NULL,
  `harga_jual` bigint(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `penumpangs`
--

CREATE TABLE `penumpangs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_penumpang` varchar(255) NOT NULL,
  `jenis_kelamin` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `penumpang_dokumen`
--

CREATE TABLE `penumpang_dokumen` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `penumpang_id` bigint(20) UNSIGNED NOT NULL,
  `dokumen_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `penumpang_tiket_kereta`
--

CREATE TABLE `penumpang_tiket_kereta` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `penumpang_id` bigint(20) UNSIGNED NOT NULL,
  `tiket_kereta_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `penumpang_tiket_pesawat`
--

CREATE TABLE `penumpang_tiket_pesawat` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `penumpang_id` bigint(20) UNSIGNED NOT NULL,
  `tiket_pesawat_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `stasiuns`
--

CREATE TABLE `stasiuns` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_stasiun` varchar(255) NOT NULL,
  `kode_stasiun` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tiket_keretas`
--

CREATE TABLE `tiket_keretas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pemesanan_id` bigint(20) UNSIGNED NOT NULL,
  `vendor_id` bigint(20) UNSIGNED NOT NULL,
  `kereta_id` bigint(20) UNSIGNED NOT NULL,
  `stasiun_berangkat_id` bigint(20) UNSIGNED NOT NULL,
  `stasiun_tiba_id` bigint(20) UNSIGNED NOT NULL,
  `kode_booking_kereta` varchar(255) NOT NULL,
  `jadwal_berangkat_kereta` datetime NOT NULL,
  `jadwal_tiba_kereta` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tiket_pesawats`
--

CREATE TABLE `tiket_pesawats` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `maskapai_id` bigint(20) UNSIGNED NOT NULL,
  `vendor_id` bigint(20) UNSIGNED NOT NULL,
  `pemesanan_id` bigint(20) UNSIGNED NOT NULL,
  `bandara_berangkat_id` bigint(20) UNSIGNED NOT NULL,
  `bandara_tiba_id` bigint(20) UNSIGNED NOT NULL,
  `nomor_ticket` varchar(255) NOT NULL,
  `nomor_penerbangan` varchar(255) NOT NULL,
  `kode_booking_pesawat` varchar(255) NOT NULL,
  `kelas` varchar(255) NOT NULL,
  `jadwal_berangkat_pesawat` datetime NOT NULL,
  `jam_tiba` time NOT NULL,
  `detail_pulang_pergi` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `unit_kerjas`
--

CREATE TABLE `unit_kerjas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_unit_kerja` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `vendors`
--

CREATE TABLE `vendors` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_vendor` varchar(255) NOT NULL,
  `jenis_vendor` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `bandaras`
--
ALTER TABLE `bandaras`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `dokumens`
--
ALTER TABLE `dokumens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dokumens_vendor_id_foreign` (`vendor_id`),
  ADD KEY `dokumens_pemesanan_id_foreign` (`pemesanan_id`);

--
-- Indeks untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indeks untuk tabel `hotels`
--
ALTER TABLE `hotels`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `kamar_hotels`
--
ALTER TABLE `kamar_hotels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kamar_hotels_hotel_id_foreign` (`hotel_id`),
  ADD KEY `kamar_hotels_vendor_id_foreign` (`vendor_id`),
  ADD KEY `kamar_hotels_pemesanan_id_foreign` (`pemesanan_id`);

--
-- Indeks untuk tabel `kamar_hotel_penumpang`
--
ALTER TABLE `kamar_hotel_penumpang`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kamar_hotel_penumpang_penumpang_id_kamar_hotel_id_unique` (`penumpang_id`,`kamar_hotel_id`),
  ADD KEY `kamar_hotel_penumpang_kamar_hotel_id_foreign` (`kamar_hotel_id`);

--
-- Indeks untuk tabel `kategori_pemesanans`
--
ALTER TABLE `kategori_pemesanans`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `keretas`
--
ALTER TABLE `keretas`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `maskapais`
--
ALTER TABLE `maskapais`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indeks untuk tabel `pembayarans`
--
ALTER TABLE `pembayarans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pembayarans_unit_kerja_id_foreign` (`unit_kerja_id`),
  ADD KEY `pembayarans_pemesanan_id_foreign` (`pemesanan_id`);

--
-- Indeks untuk tabel `pembayaran_penumpang`
--
ALTER TABLE `pembayaran_penumpang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pembayaran_penumpang_penumpang_id_foreign` (`penumpang_id`),
  ADD KEY `pembayaran_penumpang_pembayaran_id_foreign` (`pembayaran_id`),
  ADD KEY `pembayaran_penumpang_user_id_foreign` (`user_id`);

--
-- Indeks untuk tabel `pemesanans`
--
ALTER TABLE `pemesanans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pemesanans_invoice_unique` (`invoice`),
  ADD KEY `pemesanans_kategori_pemesanan_id_foreign` (`kategori_pemesanan_id`),
  ADD KEY `pemesanans_unit_kerja_id_foreign` (`unit_kerja_id`);

--
-- Indeks untuk tabel `penumpangs`
--
ALTER TABLE `penumpangs`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `penumpang_dokumen`
--
ALTER TABLE `penumpang_dokumen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `penumpang_dokumen_penumpang_id_foreign` (`penumpang_id`),
  ADD KEY `penumpang_dokumen_dokumen_id_foreign` (`dokumen_id`);

--
-- Indeks untuk tabel `penumpang_tiket_kereta`
--
ALTER TABLE `penumpang_tiket_kereta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `penumpang_tiket_kereta_penumpang_id_foreign` (`penumpang_id`),
  ADD KEY `penumpang_tiket_kereta_tiket_kereta_id_foreign` (`tiket_kereta_id`);

--
-- Indeks untuk tabel `penumpang_tiket_pesawat`
--
ALTER TABLE `penumpang_tiket_pesawat`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `penumpang_tiket_pesawat_penumpang_id_tiket_pesawat_id_unique` (`penumpang_id`,`tiket_pesawat_id`),
  ADD KEY `penumpang_tiket_pesawat_tiket_pesawat_id_foreign` (`tiket_pesawat_id`);

--
-- Indeks untuk tabel `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indeks untuk tabel `stasiuns`
--
ALTER TABLE `stasiuns`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tiket_keretas`
--
ALTER TABLE `tiket_keretas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tiket_keretas_kode_booking_kereta_unique` (`kode_booking_kereta`),
  ADD KEY `tiket_keretas_pemesanan_id_foreign` (`pemesanan_id`),
  ADD KEY `tiket_keretas_vendor_id_foreign` (`vendor_id`),
  ADD KEY `tiket_keretas_kereta_id_foreign` (`kereta_id`),
  ADD KEY `tiket_keretas_stasiun_berangkat_id_foreign` (`stasiun_berangkat_id`),
  ADD KEY `tiket_keretas_stasiun_tiba_id_foreign` (`stasiun_tiba_id`);

--
-- Indeks untuk tabel `tiket_pesawats`
--
ALTER TABLE `tiket_pesawats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tiket_pesawats_maskapai_id_foreign` (`maskapai_id`),
  ADD KEY `tiket_pesawats_vendor_id_foreign` (`vendor_id`),
  ADD KEY `tiket_pesawats_pemesanan_id_foreign` (`pemesanan_id`),
  ADD KEY `tiket_pesawats_bandara_berangkat_id_foreign` (`bandara_berangkat_id`),
  ADD KEY `tiket_pesawats_bandara_tiba_id_foreign` (`bandara_tiba_id`);

--
-- Indeks untuk tabel `unit_kerjas`
--
ALTER TABLE `unit_kerjas`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_username_unique` (`username`);

--
-- Indeks untuk tabel `vendors`
--
ALTER TABLE `vendors`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `bandaras`
--
ALTER TABLE `bandaras`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `dokumens`
--
ALTER TABLE `dokumens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `hotels`
--
ALTER TABLE `hotels`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `kamar_hotels`
--
ALTER TABLE `kamar_hotels`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `kamar_hotel_penumpang`
--
ALTER TABLE `kamar_hotel_penumpang`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `kategori_pemesanans`
--
ALTER TABLE `kategori_pemesanans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `keretas`
--
ALTER TABLE `keretas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `maskapais`
--
ALTER TABLE `maskapais`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pembayarans`
--
ALTER TABLE `pembayarans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pembayaran_penumpang`
--
ALTER TABLE `pembayaran_penumpang`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pemesanans`
--
ALTER TABLE `pemesanans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `penumpangs`
--
ALTER TABLE `penumpangs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `penumpang_dokumen`
--
ALTER TABLE `penumpang_dokumen`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `penumpang_tiket_kereta`
--
ALTER TABLE `penumpang_tiket_kereta`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `penumpang_tiket_pesawat`
--
ALTER TABLE `penumpang_tiket_pesawat`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `stasiuns`
--
ALTER TABLE `stasiuns`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tiket_keretas`
--
ALTER TABLE `tiket_keretas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tiket_pesawats`
--
ALTER TABLE `tiket_pesawats`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `unit_kerjas`
--
ALTER TABLE `unit_kerjas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `vendors`
--
ALTER TABLE `vendors`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `dokumens`
--
ALTER TABLE `dokumens`
  ADD CONSTRAINT `dokumens_pemesanan_id_foreign` FOREIGN KEY (`pemesanan_id`) REFERENCES `pemesanans` (`id`),
  ADD CONSTRAINT `dokumens_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`);

--
-- Ketidakleluasaan untuk tabel `kamar_hotels`
--
ALTER TABLE `kamar_hotels`
  ADD CONSTRAINT `kamar_hotels_hotel_id_foreign` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`),
  ADD CONSTRAINT `kamar_hotels_pemesanan_id_foreign` FOREIGN KEY (`pemesanan_id`) REFERENCES `pemesanans` (`id`),
  ADD CONSTRAINT `kamar_hotels_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`);

--
-- Ketidakleluasaan untuk tabel `kamar_hotel_penumpang`
--
ALTER TABLE `kamar_hotel_penumpang`
  ADD CONSTRAINT `kamar_hotel_penumpang_kamar_hotel_id_foreign` FOREIGN KEY (`kamar_hotel_id`) REFERENCES `kamar_hotels` (`id`),
  ADD CONSTRAINT `kamar_hotel_penumpang_penumpang_id_foreign` FOREIGN KEY (`penumpang_id`) REFERENCES `penumpangs` (`id`);

--
-- Ketidakleluasaan untuk tabel `pembayarans`
--
ALTER TABLE `pembayarans`
  ADD CONSTRAINT `pembayarans_pemesanan_id_foreign` FOREIGN KEY (`pemesanan_id`) REFERENCES `pemesanans` (`id`),
  ADD CONSTRAINT `pembayarans_unit_kerja_id_foreign` FOREIGN KEY (`unit_kerja_id`) REFERENCES `unit_kerjas` (`id`);

--
-- Ketidakleluasaan untuk tabel `pembayaran_penumpang`
--
ALTER TABLE `pembayaran_penumpang`
  ADD CONSTRAINT `pembayaran_penumpang_pembayaran_id_foreign` FOREIGN KEY (`pembayaran_id`) REFERENCES `pembayarans` (`id`),
  ADD CONSTRAINT `pembayaran_penumpang_penumpang_id_foreign` FOREIGN KEY (`penumpang_id`) REFERENCES `penumpangs` (`id`),
  ADD CONSTRAINT `pembayaran_penumpang_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `pemesanans`
--
ALTER TABLE `pemesanans`
  ADD CONSTRAINT `pemesanans_kategori_pemesanan_id_foreign` FOREIGN KEY (`kategori_pemesanan_id`) REFERENCES `kategori_pemesanans` (`id`),
  ADD CONSTRAINT `pemesanans_unit_kerja_id_foreign` FOREIGN KEY (`unit_kerja_id`) REFERENCES `unit_kerjas` (`id`);

--
-- Ketidakleluasaan untuk tabel `penumpang_dokumen`
--
ALTER TABLE `penumpang_dokumen`
  ADD CONSTRAINT `penumpang_dokumen_dokumen_id_foreign` FOREIGN KEY (`dokumen_id`) REFERENCES `dokumens` (`id`),
  ADD CONSTRAINT `penumpang_dokumen_penumpang_id_foreign` FOREIGN KEY (`penumpang_id`) REFERENCES `penumpangs` (`id`);

--
-- Ketidakleluasaan untuk tabel `penumpang_tiket_kereta`
--
ALTER TABLE `penumpang_tiket_kereta`
  ADD CONSTRAINT `penumpang_tiket_kereta_penumpang_id_foreign` FOREIGN KEY (`penumpang_id`) REFERENCES `penumpangs` (`id`),
  ADD CONSTRAINT `penumpang_tiket_kereta_tiket_kereta_id_foreign` FOREIGN KEY (`tiket_kereta_id`) REFERENCES `tiket_keretas` (`id`);

--
-- Ketidakleluasaan untuk tabel `penumpang_tiket_pesawat`
--
ALTER TABLE `penumpang_tiket_pesawat`
  ADD CONSTRAINT `penumpang_tiket_pesawat_penumpang_id_foreign` FOREIGN KEY (`penumpang_id`) REFERENCES `penumpangs` (`id`),
  ADD CONSTRAINT `penumpang_tiket_pesawat_tiket_pesawat_id_foreign` FOREIGN KEY (`tiket_pesawat_id`) REFERENCES `tiket_pesawats` (`id`);

--
-- Ketidakleluasaan untuk tabel `tiket_keretas`
--
ALTER TABLE `tiket_keretas`
  ADD CONSTRAINT `tiket_keretas_kereta_id_foreign` FOREIGN KEY (`kereta_id`) REFERENCES `keretas` (`id`),
  ADD CONSTRAINT `tiket_keretas_pemesanan_id_foreign` FOREIGN KEY (`pemesanan_id`) REFERENCES `pemesanans` (`id`),
  ADD CONSTRAINT `tiket_keretas_stasiun_berangkat_id_foreign` FOREIGN KEY (`stasiun_berangkat_id`) REFERENCES `stasiuns` (`id`),
  ADD CONSTRAINT `tiket_keretas_stasiun_tiba_id_foreign` FOREIGN KEY (`stasiun_tiba_id`) REFERENCES `stasiuns` (`id`),
  ADD CONSTRAINT `tiket_keretas_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`);

--
-- Ketidakleluasaan untuk tabel `tiket_pesawats`
--
ALTER TABLE `tiket_pesawats`
  ADD CONSTRAINT `tiket_pesawats_bandara_berangkat_id_foreign` FOREIGN KEY (`bandara_berangkat_id`) REFERENCES `bandaras` (`id`),
  ADD CONSTRAINT `tiket_pesawats_bandara_tiba_id_foreign` FOREIGN KEY (`bandara_tiba_id`) REFERENCES `bandaras` (`id`),
  ADD CONSTRAINT `tiket_pesawats_maskapai_id_foreign` FOREIGN KEY (`maskapai_id`) REFERENCES `maskapais` (`id`),
  ADD CONSTRAINT `tiket_pesawats_pemesanan_id_foreign` FOREIGN KEY (`pemesanan_id`) REFERENCES `pemesanans` (`id`),
  ADD CONSTRAINT `tiket_pesawats_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
