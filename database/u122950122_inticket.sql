-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Waktu pembuatan: 03 Okt 2026 pada 15.01
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
-- Database: `u122950122_inticket`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
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
-- Struktur dari tabel `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
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
-- Struktur dari tabel `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `display_name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `group_name` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `permission_role`
--

CREATE TABLE `permission_role` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `display_name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `role_user`
--

CREATE TABLE `role_user` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ticketing_activity_log`
--

CREATE TABLE `ticketing_activity_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tckt_pemesanan_id` bigint(20) UNSIGNED DEFAULT NULL,
  `entity_type` varchar(255) NOT NULL,
  `entity_id` bigint(20) UNSIGNED NOT NULL,
  `event` varchar(255) NOT NULL,
  `changes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`changes`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ticketing_bandara`
--

CREATE TABLE `ticketing_bandara` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_bandara` varchar(255) NOT NULL,
  `kode_bandara` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ticketing_dokumen`
--

CREATE TABLE `ticketing_dokumen` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tckt_vendor_id` bigint(20) UNSIGNED NOT NULL,
  `tckt_pemesanan_id` bigint(20) UNSIGNED NOT NULL,
  `jenis_dokumen` varchar(255) NOT NULL,
  `keterangan` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ticketing_hotel`
--

CREATE TABLE `ticketing_hotel` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_hotel` varchar(255) NOT NULL,
  `bintang` int(11) DEFAULT NULL,
  `kota` varchar(255) DEFAULT NULL,
  `telepon` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ticketing_kamar_hotel`
--

CREATE TABLE `ticketing_kamar_hotel` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tckt_hotel_id` bigint(20) UNSIGNED NOT NULL,
  `tckt_vendor_id` bigint(20) UNSIGNED NOT NULL,
  `tckt_pemesanan_id` bigint(20) UNSIGNED NOT NULL,
  `jumlah_kamar` int(11) NOT NULL,
  `lama_menginap` int(11) NOT NULL,
  `tipe_kamar` varchar(255) NOT NULL,
  `jadwal_checkin` datetime NOT NULL,
  `jadwal_checkout` datetime NOT NULL,
  `include_breakfast` tinyint(1) NOT NULL,
  `zona_waktu` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ticketing_kamar_hotel_penumpang`
--

CREATE TABLE `ticketing_kamar_hotel_penumpang` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tckt_penumpang_id` bigint(20) UNSIGNED NOT NULL,
  `tckt_kamar_hotel_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ticketing_kategori_pemesanan`
--

CREATE TABLE `ticketing_kategori_pemesanan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_kategori` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ticketing_kereta`
--

CREATE TABLE `ticketing_kereta` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_kereta` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ticketing_maskapai`
--

CREATE TABLE `ticketing_maskapai` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_maskapai` varchar(255) NOT NULL,
  `logo_maskapai` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ticketing_pelanggan`
--

CREATE TABLE `ticketing_pelanggan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_pelanggan` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ticketing_pembayar`
--

CREATE TABLE `ticketing_pembayar` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_pembayar` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ticketing_pembayaran`
--

CREATE TABLE `ticketing_pembayaran` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tckt_unit_kerja_id` bigint(20) UNSIGNED NOT NULL,
  `tckt_pemesanan_id` bigint(20) UNSIGNED NOT NULL,
  `nama_pembayar` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ticketing_pembayaran_penumpang`
--

CREATE TABLE `ticketing_pembayaran_penumpang` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tckt_penumpang_id` bigint(20) UNSIGNED NOT NULL,
  `tckt_pembayaran_id` bigint(20) UNSIGNED NOT NULL,
  `nama_pembayar` varchar(255) DEFAULT NULL,
  `tckt_unit_kerja_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tckt_pembayar_id` bigint(20) UNSIGNED DEFAULT NULL,
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
-- Struktur dari tabel `ticketing_pemesanan`
--

CREATE TABLE `ticketing_pemesanan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `invoice` varchar(255) NOT NULL,
  `nama_customer` varchar(255) NOT NULL,
  `tckt_kategori_pemesanan_id` bigint(20) UNSIGNED NOT NULL,
  `tckt_unit_kerja_id` bigint(20) UNSIGNED NOT NULL,
  `status_pemesanan` varchar(255) NOT NULL,
  `pulang_pergi` int(11) NOT NULL,
  `tanggal_pemesanan` date NOT NULL,
  `harga_beli` bigint(20) NOT NULL,
  `harga_publish` bigint(20) DEFAULT NULL,
  `harga_jual` bigint(20) NOT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ticketing_penumpang`
--

CREATE TABLE `ticketing_penumpang` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_penumpang` varchar(255) NOT NULL,
  `jenis_kelamin` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ticketing_penumpang_dokumen`
--

CREATE TABLE `ticketing_penumpang_dokumen` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tckt_penumpang_id` bigint(20) UNSIGNED NOT NULL,
  `tckt_dokumen_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ticketing_penumpang_tiket_kereta`
--

CREATE TABLE `ticketing_penumpang_tiket_kereta` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tckt_penumpang_id` bigint(20) UNSIGNED NOT NULL,
  `tckt_tiket_kereta_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ticketing_penumpang_tiket_pesawat`
--

CREATE TABLE `ticketing_penumpang_tiket_pesawat` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tckt_penumpang_id` bigint(20) UNSIGNED NOT NULL,
  `tckt_tiket_pesawat_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ticketing_stasiun`
--

CREATE TABLE `ticketing_stasiun` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_stasiun` varchar(255) NOT NULL,
  `kode_stasiun` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ticketing_tiket_kereta`
--

CREATE TABLE `ticketing_tiket_kereta` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tckt_pemesanan_id` bigint(20) UNSIGNED NOT NULL,
  `tckt_vendor_id` bigint(20) UNSIGNED NOT NULL,
  `tckt_kereta_id` bigint(20) UNSIGNED NOT NULL,
  `tckt_stasiun_berangkat_id` bigint(20) UNSIGNED NOT NULL,
  `tckt_stasiun_tiba_id` bigint(20) UNSIGNED NOT NULL,
  `kode_booking_kereta` varchar(255) NOT NULL,
  `jadwal_berangkat_kereta` datetime NOT NULL,
  `jadwal_tiba_kereta` datetime NOT NULL,
  `zona_waktu` varchar(255) DEFAULT NULL,
  `zona_waktu_kedatangan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ticketing_tiket_pesawat`
--

CREATE TABLE `ticketing_tiket_pesawat` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tckt_maskapai_id` bigint(20) UNSIGNED NOT NULL,
  `tckt_vendor_id` bigint(20) UNSIGNED NOT NULL,
  `tckt_pemesanan_id` bigint(20) UNSIGNED NOT NULL,
  `tckt_bandara_berangkat_id` bigint(20) UNSIGNED NOT NULL,
  `tckt_bandara_tiba_id` bigint(20) UNSIGNED NOT NULL,
  `nomor_ticket` varchar(255) NOT NULL,
  `nomor_penerbangan` varchar(255) NOT NULL,
  `kode_booking_pesawat` varchar(255) NOT NULL,
  `kelas` varchar(255) NOT NULL,
  `jenis_penerbangan` varchar(255) DEFAULT NULL,
  `jadwal_berangkat_pesawat` datetime NOT NULL,
  `jadwal_tiba_pesawat` datetime NOT NULL,
  `detail_pulang_pergi` text DEFAULT NULL,
  `zona_waktu` varchar(255) DEFAULT NULL,
  `zona_waktu_kedatangan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ticketing_unit_kerja`
--

CREATE TABLE `ticketing_unit_kerja` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_unit_kerja` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ticketing_vendor`
--

CREATE TABLE `ticketing_vendor` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_vendor` varchar(255) NOT NULL,
  `jenis_vendor` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `active_role_id` bigint(20) UNSIGNED DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indeks untuk tabel `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indeks untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indeks untuk tabel `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indeks untuk tabel `job_batches`
--
ALTER TABLE `job_batches`
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
-- Indeks untuk tabel `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_unique` (`name`);

--
-- Indeks untuk tabel `permission_role`
--
ALTER TABLE `permission_role`
  ADD PRIMARY KEY (`id`),
  ADD KEY `permission_role_permission_id_foreign` (`permission_id`),
  ADD KEY `permission_role_role_id_foreign` (`role_id`);

--
-- Indeks untuk tabel `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_unique` (`name`);

--
-- Indeks untuk tabel `role_user`
--
ALTER TABLE `role_user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `role_user_role_id_foreign` (`role_id`),
  ADD KEY `role_user_user_id_foreign` (`user_id`);

--
-- Indeks untuk tabel `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indeks untuk tabel `ticketing_activity_log`
--
ALTER TABLE `ticketing_activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticketing_activity_log_user_id_foreign` (`user_id`),
  ADD KEY `ticketing_activity_log_entity_type_entity_id_index` (`entity_type`,`entity_id`),
  ADD KEY `ticketing_activity_log_tckt_pemesanan_id_index` (`tckt_pemesanan_id`);

--
-- Indeks untuk tabel `ticketing_bandara`
--
ALTER TABLE `ticketing_bandara`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `ticketing_dokumen`
--
ALTER TABLE `ticketing_dokumen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_dokumen_vendor` (`tckt_vendor_id`),
  ADD KEY `idx_dokumen_pemesanan` (`tckt_pemesanan_id`);

--
-- Indeks untuk tabel `ticketing_hotel`
--
ALTER TABLE `ticketing_hotel`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `ticketing_kamar_hotel`
--
ALTER TABLE `ticketing_kamar_hotel`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_kamar_hotel_pemesanan` (`tckt_pemesanan_id`),
  ADD KEY `idx_kamar_hotel_hotel` (`tckt_hotel_id`),
  ADD KEY `idx_kamar_hotel_vendor` (`tckt_vendor_id`),
  ADD KEY `idx_kamar_hotel_jadwal_checkin` (`jadwal_checkin`);

--
-- Indeks untuk tabel `ticketing_kamar_hotel_penumpang`
--
ALTER TABLE `ticketing_kamar_hotel_penumpang`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_penumpang_kamar_hotel` (`tckt_penumpang_id`,`tckt_kamar_hotel_id`),
  ADD KEY `idx_khp_kamar_hotel` (`tckt_kamar_hotel_id`);

--
-- Indeks untuk tabel `ticketing_kategori_pemesanan`
--
ALTER TABLE `ticketing_kategori_pemesanan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `ticketing_kereta`
--
ALTER TABLE `ticketing_kereta`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `ticketing_maskapai`
--
ALTER TABLE `ticketing_maskapai`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `ticketing_pelanggan`
--
ALTER TABLE `ticketing_pelanggan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `ticketing_pembayar`
--
ALTER TABLE `ticketing_pembayar`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `ticketing_pembayaran`
--
ALTER TABLE `ticketing_pembayaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pembayaran_pemesanan` (`tckt_pemesanan_id`),
  ADD KEY `idx_pembayaran_unit_kerja` (`tckt_unit_kerja_id`);

--
-- Indeks untuk tabel `ticketing_pembayaran_penumpang`
--
ALTER TABLE `ticketing_pembayaran_penumpang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticketing_pembayaran_penumpang_user_id_foreign` (`user_id`),
  ADD KEY `idx_pp_pembayaran_penumpang` (`tckt_pembayaran_id`,`tckt_penumpang_id`),
  ADD KEY `idx_pp_penumpang` (`tckt_penumpang_id`),
  ADD KEY `ticketing_pembayaran_penumpang_tckt_pembayar_id_foreign` (`tckt_pembayar_id`);

--
-- Indeks untuk tabel `ticketing_pemesanan`
--
ALTER TABLE `ticketing_pemesanan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ticketing_pemesanan_invoice_unique` (`invoice`),
  ADD KEY `ticketing_pemesanan_created_by_foreign` (`created_by`),
  ADD KEY `idx_pemesanan_kategori` (`tckt_kategori_pemesanan_id`),
  ADD KEY `idx_pemesanan_unit_kerja` (`tckt_unit_kerja_id`),
  ADD KEY `idx_pemesanan_tanggal` (`tanggal_pemesanan`),
  ADD KEY `idx_pemesanan_status` (`status_pemesanan`);

--
-- Indeks untuk tabel `ticketing_penumpang`
--
ALTER TABLE `ticketing_penumpang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_penumpang_nama` (`nama_penumpang`);

--
-- Indeks untuk tabel `ticketing_penumpang_dokumen`
--
ALTER TABLE `ticketing_penumpang_dokumen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pd_penumpang` (`tckt_penumpang_id`),
  ADD KEY `idx_pd_dokumen` (`tckt_dokumen_id`);

--
-- Indeks untuk tabel `ticketing_penumpang_tiket_kereta`
--
ALTER TABLE `ticketing_penumpang_tiket_kereta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ptk_penumpang` (`tckt_penumpang_id`),
  ADD KEY `idx_ptk_tiket_kereta` (`tckt_tiket_kereta_id`);

--
-- Indeks untuk tabel `ticketing_penumpang_tiket_pesawat`
--
ALTER TABLE `ticketing_penumpang_tiket_pesawat`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_penumpang_tiket` (`tckt_penumpang_id`,`tckt_tiket_pesawat_id`),
  ADD KEY `idx_ptp_tiket_pesawat` (`tckt_tiket_pesawat_id`);

--
-- Indeks untuk tabel `ticketing_stasiun`
--
ALTER TABLE `ticketing_stasiun`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `ticketing_tiket_kereta`
--
ALTER TABLE `ticketing_tiket_kereta`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ticketing_tiket_kereta_kode_booking_kereta_unique` (`kode_booking_kereta`),
  ADD KEY `idx_tiket_kereta_pemesanan` (`tckt_pemesanan_id`),
  ADD KEY `idx_tiket_kereta_vendor` (`tckt_vendor_id`),
  ADD KEY `idx_tiket_kereta_kereta` (`tckt_kereta_id`),
  ADD KEY `idx_tiket_kereta_stasiun_berangkat` (`tckt_stasiun_berangkat_id`),
  ADD KEY `idx_tiket_kereta_stasiun_tiba` (`tckt_stasiun_tiba_id`);

--
-- Indeks untuk tabel `ticketing_tiket_pesawat`
--
ALTER TABLE `ticketing_tiket_pesawat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tiket_pesawat_pemesanan` (`tckt_pemesanan_id`),
  ADD KEY `idx_tiket_pesawat_maskapai` (`tckt_maskapai_id`),
  ADD KEY `idx_tiket_pesawat_vendor` (`tckt_vendor_id`),
  ADD KEY `idx_tiket_pesawat_bandara_berangkat` (`tckt_bandara_berangkat_id`),
  ADD KEY `idx_tiket_pesawat_bandara_tiba` (`tckt_bandara_tiba_id`);

--
-- Indeks untuk tabel `ticketing_unit_kerja`
--
ALTER TABLE `ticketing_unit_kerja`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `ticketing_vendor`
--
ALTER TABLE `ticketing_vendor`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_phone_unique` (`phone`),
  ADD KEY `users_active_role_id_foreign` (`active_role_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `permission_role`
--
ALTER TABLE `permission_role`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `role_user`
--
ALTER TABLE `role_user`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ticketing_activity_log`
--
ALTER TABLE `ticketing_activity_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ticketing_bandara`
--
ALTER TABLE `ticketing_bandara`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ticketing_dokumen`
--
ALTER TABLE `ticketing_dokumen`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ticketing_hotel`
--
ALTER TABLE `ticketing_hotel`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ticketing_kamar_hotel`
--
ALTER TABLE `ticketing_kamar_hotel`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ticketing_kamar_hotel_penumpang`
--
ALTER TABLE `ticketing_kamar_hotel_penumpang`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ticketing_kategori_pemesanan`
--
ALTER TABLE `ticketing_kategori_pemesanan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ticketing_kereta`
--
ALTER TABLE `ticketing_kereta`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ticketing_maskapai`
--
ALTER TABLE `ticketing_maskapai`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ticketing_pelanggan`
--
ALTER TABLE `ticketing_pelanggan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ticketing_pembayar`
--
ALTER TABLE `ticketing_pembayar`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ticketing_pembayaran`
--
ALTER TABLE `ticketing_pembayaran`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ticketing_pembayaran_penumpang`
--
ALTER TABLE `ticketing_pembayaran_penumpang`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ticketing_pemesanan`
--
ALTER TABLE `ticketing_pemesanan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ticketing_penumpang`
--
ALTER TABLE `ticketing_penumpang`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ticketing_penumpang_dokumen`
--
ALTER TABLE `ticketing_penumpang_dokumen`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ticketing_penumpang_tiket_kereta`
--
ALTER TABLE `ticketing_penumpang_tiket_kereta`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ticketing_penumpang_tiket_pesawat`
--
ALTER TABLE `ticketing_penumpang_tiket_pesawat`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ticketing_stasiun`
--
ALTER TABLE `ticketing_stasiun`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ticketing_tiket_kereta`
--
ALTER TABLE `ticketing_tiket_kereta`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ticketing_tiket_pesawat`
--
ALTER TABLE `ticketing_tiket_pesawat`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ticketing_unit_kerja`
--
ALTER TABLE `ticketing_unit_kerja`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ticketing_vendor`
--
ALTER TABLE `ticketing_vendor`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `permission_role`
--
ALTER TABLE `permission_role`
  ADD CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `role_user`
--
ALTER TABLE `role_user`
  ADD CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
