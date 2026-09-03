# 📖 Gapura — Living Project Documentation

> **Selalu baca file ini sebelum mulai mengerjakan sesuatu di repo ini.**
> File ini diupdate setiap kali ada temuan atau perubahan penting.

---

## 🏗️ Stack & Teknologi

| Item | Nilai |
|---|---|
| Framework | Laravel 12 |
| PHP | ^8.2 |
| Admin Panel | **Filament v5** (bukan v3 atau v4!) |
| Module System | `coolsam/modules` v5.3 |
| PDF | `barryvdh/laravel-dompdf` 3.1.2 |
| DB (Production) | MySQL — host `srv1098.hstgr.io`, DB `u122950122_apps` |
| Auth | Custom RBAC (bukan Spatie) |
| Dev command | `composer run dev` (serve + queue + pail + vite sekaligus) |

---

## 📁 Struktur Direktori Utama

```
gapura/
├── app/                          # Core Laravel
│   ├── Models/                   # User, Role, Permission
│   ├── Filament/Concerns/        # HasRbacPermission, dll
│   └── Providers/AppServiceProvider.php  # Kosong, belum ada logic
├── Modules/
│   ├── Ticketing/                # Modul tiket perjalanan
│   ├── User/                     # Modul manajemen user
│   └── PengajuanDana/            # Modul pengajuan dana
├── database/migrations/          # Core migrations (users, roles, dll)
├── docs/                         # ← Dokumentasi ini
└── modules_statuses.json         # Status aktif/nonaktif tiap modul
```

Tiap modul memiliki struktur: `app/{Filament,Http,Models,Providers}/`, `database/migrations/`, `routes/`

---

## ⚠️ CRITICAL GOTCHA: Konflik Nama `Schema`

**Filament v5** menggunakan `Filament\Schemas\Schema`, sedangkan Laravel punya `Illuminate\Support\Facades\Schema`.
Jika keduanya di-import tanpa alias → **PHP Fatal Error: name already in use**.

**Aturan wajib:**
```php
use Filament\Schemas\Schema;                          // Untuk form/table — JANGAN diubah
use Illuminate\Support\Facades\Schema as DBSchema;   // Untuk DB operations
```

Panggil dengan `DBSchema::hasTable(...)` atau `DBSchema::hasColumn(...)`.

**File yang sudah diperbaiki:**
- `Modules/Ticketing/.../ReservasiPesawats/RelationManagers/PenumpangPesawatRelationManager.php`
- `Modules/Ticketing/.../ReservasiPesawats/RelationManagers/RiwayatPembayaranRelationManager.php`

---

## 👤 Module: User

### Path Penting
| File | Lokasi |
|---|---|
| Model | `app/Models/User.php` |
| Form | `Modules/User/app/Filament/Clusters/User/Resources/Users/Schemas/UserForm.php` |
| Create Page | `Modules/User/app/Filament/Clusters/User/Resources/Users/Pages/CreateUser.php` |
| Edit Page | `Modules/User/app/Filament/Clusters/User/Resources/Users/Pages/EditUser.php` |
| Plugin | `Modules/User/app/Filament/UserPlugin.php` |

### Model `User` — Hal Penting
- **`$fillable`**: `name`, `username`, `email`, `password`, `is_active`, `active_role_id`
- **`casts`**: `password` → `'hashed'` → Eloquent auto-hash. **Jangan pakai `bcrypt()` manual!**
- **`username`**: di-mutate otomatis → lowercase, spasi jadi `_`, hapus karakter non `a-z0-9_.`
- **RBAC**: Custom, bukan Spatie. Pakai `canAccess(string $permissionName)` dan `isSuperAdmin()`
- **Cache permission**: key `user:{id}:permissions`, TTL 10 menit
- Relasi: `roles()` BelongsToMany, `activeRole()` BelongsTo ke `active_role_id`

### Kolom `password` di tabel `users`
> **NOT NULL tanpa default value** — selalu sertakan saat insert!

Error jika tidak ada: `SQLSTATE[HY000]: General error: 1364 Field 'password' doesn't have a default value`

### Form `UserForm.php` — Field Password
```php
// Password wajib di Create, opsional di Edit
TextInput::make('password')
    ->password()->revealable()
    ->required(fn (string $operation): bool => $operation === 'create')
    ->minLength(8)->maxLength(255)
    ->dehydrated(fn (?string $state): bool => filled($state))  // Skip jika kosong di Edit
    ->confirmed(),

TextInput::make('password_confirmation')
    ->password()->revealable()
    ->required(fn (string $operation): bool => $operation === 'create')
    ->minLength(8)->maxLength(255)
    ->dehydrated(false),  // JANGAN simpan ke DB
```
> **JANGAN** tambah `->dehydrateStateUsing(fn ($s) => bcrypt($s))` → double-hash karena model sudah cast `hashed`!

### Filament Resources User
- `Users/` → CRUD akun user (URL: `/dashboard/pengguna/akun`)
- `Roles/` → CRUD role
- `DeleteAction` di EditUser sengaja di-comment-out

---

## ✈️ Module: Ticketing

### Path Penting
| Komponen | Lokasi |
|---|---|
| Models | `Modules/Ticketing/app/Models/` |
| Filament Resources | `Modules/Ticketing/app/Filament/Clusters/Ticketing/Resources/` |
| Plugin | `Modules/Ticketing/app/Filament/TicketingPlugin.php` |

### Models Ticketing (semua prefix `ticketing_`)
| Model | Tabel | Keterangan |
|---|---|---|
| `TicketingTiketPesawat` | `ticketing_tiket_pesawat` | Entry utama reservasi pesawat |
| `TicketingPemesanan` | `ticketing_pemesanan` | Data pemesanan |
| `TicketingPembayaran` | `ticketing_pembayaran` | Pembayaran per pemesanan |
| `TicketingPembayaranPenumpang` | `ticketing_pembayaran_penumpang` | Pembayaran per penumpang |
| `TicketingPenumpang` | `ticketing_penumpang` | Data penumpang |
| `TicketingPembayar` | `ticketing_pembayar` | Entitas pembayar |
| `TicketingUnitKerja` | `ticketing_unit_kerja` | Unit kerja |
| `TicketingMaskapai` | `ticketing_maskapai` | Data maskapai |
| `TicketingBandara` | `ticketing_bandara` | Data bandara |
| `TicketingVendor` | `ticketing_vendor` | Vendor tiket |
| `TicketingActivityLog` | `ticketing_activity_log` | Log aktivitas |

### Resources Ticketing (14 resources)
- `ReservasiPesawats` — Reservasi pesawat **(paling kompleks)**
- `ReservasiHotels`, `ReservasiKeretas`, `ReservasiDokumens` — Reservasi lain
- `TicketingBandaras`, `TicketingHotels`, `TicketingKeretas`, `TicketingMaskapais` — Master data
- `TicketingPelanggans`, `TicketingPembayars`, `TicketingStasiuns`, `TicketingUnitKerjas`, `TicketingVendors` — Master data
- `TicketingActivityLogs` — Log

### ReservasiPesawat — Detail Penting
**Model:** `TicketingTiketPesawat`

**RelationManagers:**

1. **`PenumpangPesawatRelationManager`**
   - Relationship: `ticketingPembayaranPenumpang`
   - Create: buat `TicketingPenumpang` → attach ke reservasi → buat `TicketingPembayaranPenumpang`
   - Pakai `DBSchema::hasColumn()` sebelum insert kolom opsional

2. **`RiwayatPembayaranRelationManager`**
   - Relationship: `ticketingPembayaranPenumpang`
   - Ada cache internal `$terbayarPerPenumpangCache` untuk performa
   - Pakai `DBSchema::hasTable()` dan `DBSchema::hasColumn()`

### Kolom Opsional di `ticketing_pembayaran_penumpang`
Kolom ini ditambahkan via migration terpisah, mungkin belum ada di DB lama. **Selalu guard sebelum insert:**
- `tckt_pembayar_id` (dari migration `2026_08_14_000002_...`)
- `tckt_unit_kerja_id` (dari migration `2026_08_13_000002_...`)
- `nama_pembayar` (dari migration `2026_08_13_000002_...`)

**Pattern guard:**
```php
$payload = ['tckt_penumpang_id' => ..., 'tckt_pembayaran_id' => ..., ...];

if (! DBSchema::hasColumn('ticketing_pembayaran_penumpang', 'tckt_pembayar_id')) {
    unset($payload['tckt_pembayar_id']);
}
// dst...

Model::create($payload);
```

---

## 💰 Module: PengajuanDana

### Path Penting
| Komponen | Lokasi |
|---|---|
| Models | `Modules/PengajuanDana/app/Models/` |
| Enums | `Modules/PengajuanDana/app/Enums/` |
| Services | `Modules/PengajuanDana/app/Services/` |

### Models (semua prefix `judan_`)
| Model | Tabel |
|---|---|
| `ProposalSubmission` | `judan_proposal_submissions` |
| `ProposalDraft` | `judan_proposal_drafts` |
| `Event` | `judan_events` |
| `Need` | `judan_needs` |
| `Bank` | `judan_banks` |
| `BankAccount` | `judan_bank_accounts` |
| `BankTransfer` | `judan_bank_transfers` |
| `BankAsal` | `judan_bank_asals` |
| `Division` | `judan_divisions` |
| `Institution` | `judan_institutions` |
| `Vendor` | `judan_vendors` |

---

## 🗄️ Database Core (di `database/migrations/`)

| Tabel | Keterangan |
|---|---|
| `users` | name, username, email, password (NOT NULL!), is_active, active_role_id |
| `roles` | name, display_name, description, is_active |
| `permissions` | name |
| `role_user` | Pivot user ↔ role |
| `permission_role` | Pivot role ↔ permission |
| `cache`, `jobs` | Laravel defaults |

---

## 🔐 RBAC Custom

Sistem RBAC **custom** (bukan Spatie):
- `User` → BelongsToMany → `Role` (via `role_user`)
- `Role` → BelongsToMany → `Permission` (via `permission_role`)
- Cek permission: `$user->canAccess('permission-name')`
- Super admin: user yang memiliki role bernama `'Super-admin'`
- Concern Filament: `App\Filament\Concerns\HasRbacPermission` — dipakai di semua Resource

---

## 🎨 Pola Kode Filament v5

### Method signature form/table
```php
// Di RelationManager / Resource
public function form(Schema $schema): Schema {
    return $schema->schema([...]);  // pakai ->schema()
}

// Di class Schemas/ terpisah
public static function configure(Schema $schema): Schema {
    return $schema->components([...]);  // pakai ->components()
}
```

### Override save dengan `->using()`
Banyak action pakai `->using(fn...)` untuk kontrol penuh proses simpan.

### Struktur Resource Besar
```
ResourceName/
├── Pages/                   ← Create, Edit, List, View
├── RelationManagers/
├── Schemas/                 ← ResourceForm.php (form definition)
└── Tables/                  ← ResourceTable.php (table definition)
```

### `->mutateRecordDataUsing()` untuk Populate Edit Form
Dipakai di `EditAction` untuk mengambil data dari relasi ke form:
```php
EditAction::make()
    ->mutateRecordDataUsing(function (array $data, Model $record): array {
        $data['nama_penumpang'] = $record->ticketingPenumpang?->nama_penumpang;
        return $data;
    })
```

---

## 🐛 Bug & Gotcha yang Sudah Ditemukan & Diperbaiki

| # | Bug | Penyebab | Fix |
|---|---|---|---|
| 1 | Fatal error: `Schema` name already in use | Konflik `Filament\Schemas\Schema` vs `Illuminate\Support\Facades\Schema` | `use ... Schema as DBSchema;` |
| 2 | `Field 'password' doesn't have a default value` di CreateUser | Form tidak punya field password | Tambah field password + password_confirmation di `UserForm.php` |
| 3 | Password double-hashed | `dehydrateStateUsing(bcrypt(...))` + model cast `hashed` | Hapus `dehydrateStateUsing` |

---

## 📝 Changelog

| Tanggal | File | Perubahan |
|---|---|---|
| 2026-09-03 | `PenumpangPesawatRelationManager.php` | Fix konflik alias Schema → DBSchema; fix bare `Schema::` calls |
| 2026-09-03 | `RiwayatPembayaranRelationManager.php` | Fix konflik alias Schema → DBSchema |
| 2026-09-03 | `UserForm.php` | Tambah field `password` & `password_confirmation` |

