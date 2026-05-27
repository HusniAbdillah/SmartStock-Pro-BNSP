# DOKUMEN PENJELASAN SINGKAT PROYEK
## Sistem Manajemen Inventaris Multi-Gudang: "SmartStock Pro"
### Skema Sertifikasi: Web Developer

---

| DATA | DETAIL INFORMASI |
|:---|:---|
| **Nama Lengkap** | Husni Abdillah |
| **No. Registrasi / NIM** | G6401231097 
| **Judul Studi Kasus** | PT Maju Bersama Digital - SmartStock Pro |
| **Program Studi** | Ilmu Komputer |

---

## 1. PENDAHULUAN & LATAR BELAKANG
Dokumen ini disusun sebagai bukti pemenuhan seluruh unit kompetensi dalam skema Web Developer. **SmartStock Pro** dirancang untuk mendigitalisasi pengelolaan stok di 5 gudang PT Maju Bersama Digital yang sebelumnya manual, guna meminimalisir kesalahan data dan mempercepat proses transfer barang.

## Ringkasan Implementasi Aktual

| Area | Implementasi Aktual |
|---|---|
| Backend | Laravel Framework 11.54.0, PHP 8.2+ |
| Database | MySQL 8.x (`DB_CONNECTION=mysql`) |
| Queue | Laravel Queue database driver (`jobs`, `failed_jobs`, `job_batches`) |
| Frontend | Blade, Tailwind CSS 3.4.19, Vite 6.4.2 |
| Chart | Chart.js 4.4.0 via CDN |
| Map | Leaflet 1.9.4 + OpenStreetMap |
| PDF | `barryvdh/laravel-dompdf` 3.1.2 |
| Import | CSV background job `ProcessBatchImport`; package `maatwebsite/excel` 3.1.69 tersedia |
| Notification | Laravel database notification + polling `/api/notifications/unread` |
| Server monitoring | `/api/server-resources` dan `/api/health` |

---

# Bab 1 - Pendahuluan

## 1.1 Latar Belakang

PT Maju Bersama Digital adalah perusahaan distribusi barang elektronik yang mengelola 5 gudang di Jakarta, Surabaya, Bandung, Medan, dan Makassar. Pengelolaan inventaris lama menggunakan spreadsheet menyebabkan data stok tidak sinkron, transfer barang lambat, laporan stok membutuhkan 2-3 hari, tidak ada alert stok minimum, dan manajemen tidak memiliki monitoring inventaris real-time.

SmartStock Pro dibangun untuk menggantikan proses manual tersebut dengan aplikasi web terpusat berbasis Laravel. Sistem menyediakan pengelolaan produk, kategori, gudang, supplier, transaksi masuk/keluar, transfer antar gudang, notifikasi stok minimum, audit log, error log, upload gambar produk, import CSV, export PDF, dashboard grafik, peta gudang, dan monitoring resource server.

## 1.2 Tujuan Proyek

| No | Tujuan |
|---|---|
| 1 | Mengubah pencatatan inventaris spreadsheet menjadi sistem web terpusat |
| 2 | Menyediakan dashboard inventaris dengan grafik, peta, dan monitoring resource |
| 3 | Menyediakan CRUD data utama: produk, kategori, gudang, supplier, transaksi, user |
| 4 | Menyediakan transfer stok antar gudang secara atomik |
| 5 | Menyediakan alert stok minimum dan error log aplikasi |
| 6 | Menyediakan dokumentasi teknis, UAT, cutover, migrasi, dan troubleshooting |

## 1.3 Ruang Lingkup

### Dalam Ruang Lingkup

| Modul | Cakupan |
|---|---|
| Autentikasi | Login, logout, rate limiting, session regeneration |
| Otorisasi | Role Admin, Manajer Gudang, Staf Gudang, Viewer |
| Dashboard | KPI, Chart.js, Leaflet map, resource monitor |
| Produk | CRUD, upload/preview gambar, search, filter, sort, pagination, import CSV |
| Kategori | CRUD kategori produk |
| Gudang | CRUD gudang dan koordinat GPS |
| Supplier | CRUD supplier |
| Transaksi | Barang masuk dan keluar |
| Transfer | Transfer antar gudang dengan `DB::transaction()` |
| Alert | Stok minimum, database notification, error log |
| Laporan | PDF sync dan async melalui queue |
| Audit | Audit log operasi write |
| API | Health, resource monitoring, notification |

### Di Luar Ruang Lingkup Aktual

| Item | Status |
|---|---|
| PostgreSQL | Tidak digunakan pada implementasi uji ini |
| Redis/Celery | Tidak digunakan; queue memakai database driver |
| WebSocket | Tidak digunakan; real-time menggunakan polling berkala |
| Email alert | Tidak digunakan; notifikasi berbasis database/in-app |
| FIFO cost-layer penuh | Belum ada tabel batch biaya; sistem memakai saldo stok per gudang dan riwayat transaksi kronologis |

---

# Bab 2 - Analisis Kebutuhan dan Kesesuaian BNSP

## 2.1 Role Pengguna

| Role | Deskripsi | Hak Akses Aktual |
|---|---|---|
| Admin | Administrator sistem | Semua modul, audit log, user management |
| Manajer Gudang | Pengelola operasional gudang | Dashboard, transaksi, transfer, laporan, error log |
| Staf Gudang | Operator gudang | Input transaksi, transfer, lihat data operasional |
| Viewer | Pengguna baca saja | Melihat data tanpa membuat perubahan |

Bukti implementasi: `app/Models/User.php`, `app/Http/Middleware/CheckRole.php`, dan route group di `routes/web.php`.

## 2.2 Kebutuhan Fungsional dan Status Implementasi

| Modul Studi Kasus | Kebutuhan | Status | Bukti Repo |
|---|---|---|---|
| Autentikasi | Login multi-level | Terpenuhi | `AuthController`, `CheckRole`, `User` |
| Password | Hashing dan validasi kuat | Terpenuhi | `Hash::make()`, `Password::min(8)->letters()->numbers()` |
| Keamanan | SQLi, XSS, CSRF | Terpenuhi | Eloquent, Blade escape, `@csrf`, validation |
| Session | Timeout otomatis | Terpenuhi | `config/session.php`, `SESSION_LIFETIME=120` |
| Audit | Catat aktivitas user | Terpenuhi | `AuditLogMiddleware`, `audit_logs` |
| Dashboard | Grafik stok dan tren transaksi | Terpenuhi | `DashboardController`, Chart.js |
| Notification | Alert stok kritis | Terpenuhi via polling | `StockAlertService`, `LowStockNotification` |
| Multimedia | Upload dan preview gambar | Terpenuhi | `ProductController`, `products/create.blade.php` |
| Peta | Lokasi gudang interaktif | Terpenuhi | Leaflet di dashboard |
| Monitoring | CPU, memory, response time | Terpenuhi | `ServerResourceController` |
| PDF | Export laporan | Terpenuhi | `ReportController`, `inventory-pdf.blade.php` |
| CRUD | Produk, kategori, gudang, supplier, transaksi | Terpenuhi | Resource routes dan controllers |
| SQL | Query SQL/join/agregasi | Terpenuhi | `DB::raw`, join, `DATE_FORMAT()` |
| Search | Algoritma pencarian produk | Terpenuhi | `Product::scopeSearch()` |
| Stok otomatis | Masuk/keluar/transfer | Terpenuhi | `TransactionController`, `TransferController` |
| Queue | Import CSV dan laporan besar | Terpenuhi | `ProcessBatchImport`, `GenerateLargeReport` |
| Error log | Exception dan severity | Terpenuhi | `bootstrap/app.php`, `ErrorLog` |

## 2.3 Matriks Unit Kompetensi

| Kode Unit | Judul Unit | Bukti Pemenuhan |
|---|---|---|
| J.62090.018.01 | Mengelola Risiko Keamanan Informasi | Risiko SQLi/XSS/CSRF, rate limit, audit, security headers |
| TIK.SM03.001.01 | Menentukan Arsitektur Perangkat Keras | Topologi dan spesifikasi server Bab 3 |
| J.620100.001.01 | Menganalisis Tools | Tabel tools dan framework Bab 4 |
| J.620100.002.01 | Menganalisis Skalabilitas Perangkat Lunak | Indexing, queue, cache plan, scale-up |
| J.620100.022.02 | Algoritma Pemrograman | Search, mutasi stok, alert threshold |
| J.620100.041.01 | Cutover Aplikasi | Cutover plan Bab 5 |
| J.620100.045.01 | Pemantauan Resource | `/api/server-resources` |
| J.620100.020.02 | Menggunakan SQL | Join, agregasi, `DB::raw`, `DATE_FORMAT` |
| J.620100.044.01 | Alert Notification | Low stock notification dan error log |
| J.620100.003.01 | Identifikasi Library/Framework | Composer/npm dependency matrix |
| J.620100.024.02 | Migrasi Teknologi Baru | Spreadsheet/CSV ke MySQL |
| J.620100.047.01 | Pembaharuan Perangkat Lunak | Git update scenario dan change log |
| J.620100.039.02 | Petunjuk Teknis Pelanggan | Bab 6 |
| J.620100.030.02 | Pemrograman Multimedia | Upload/preview gambar produk |
| J.620100.043.01 | Impact Analysis | Bab 5 |
| J.620100.029.02 | Pemrograman Paralel | Queue job dan transaksi atomik |
| J.620100.028.02 | Pemrograman Real Time | Polling notifikasi dan resource monitor |
| J.620100.025.02 | Debugging | Error log, failed job, troubleshooting |
| J.620100.038.01 | UAT | Skenario UAT Bab 6 |
| M.702090.001.01 | Integration Management | Project charter, cutover, change control |
| M.702090.005.01 | Quality Management | Quality checklist dan UAT |
| M.702090.002.01 | Scope Management | Scope, WBS, impact analysis |

---

# Bab 3 - Arsitektur Sistem dan Basis Data

## 3.1 Arsitektur Logis

```text
Browser User
  │
  ▼
Presentation Layer
Blade + Tailwind CSS + Chart.js + Leaflet + JavaScript polling
  │
  ▼
Application Layer
Laravel 11 MVC: Controller, Model, View, Middleware, Service, Job, Notification
  │
  ├── Auth + RBAC
  ├── AuditLogMiddleware + SecurityHeaders
  ├── StockAlertService
  ├── ProcessBatchImport Job
  └── GenerateLargeReport Job
  │
  ▼
Data Layer
MySQL 8.x: products, warehouses, warehouse_stocks, transactions, users, sessions,
jobs, notifications, audit_logs, error_logs
```

## 3.2 Topologi Server

```text
Internet / LAN Gudang
  │
  ▼
Nginx Web Server
  │
  ├── PHP-FPM / Laravel Application
  ├── Queue Worker: php artisan queue:work database
  └── Public Storage: product images and generated PDFs
  │
  ▼
MySQL 8 Database Server
```

## 3.3 Spesifikasi Minimum Hardware

| Komponen | Minimum | Rekomendasi | Justifikasi |
|---|---|---|---|
| Web/App CPU | 2 vCPU | 4 vCPU | PHP-FPM, Laravel request, PDF ringan |
| Web/App RAM | 4 GB | 8 GB | Laravel app, queue worker, cache |
| Web/App Storage | 40 GB SSD | 80 GB SSD | Source code, log, uploads, PDF |
| DB CPU | 2 vCPU | 4 vCPU | Query join/agregasi laporan |
| DB RAM | 4 GB | 8 GB | MySQL buffer pool dan index |
| DB Storage | 80 GB SSD | 150 GB SSD NVMe | Data transaksi, audit log, error log |
| Bandwidth | 20 Mbps | 50 Mbps | Akses 5 gudang dan download PDF |
| OS | Ubuntu Server 22.04 LTS | Ubuntu Server 24.04 LTS | Stabil untuk Nginx, PHP, MySQL |

## 3.4 Konfigurasi Database MySQL

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smartstock_pro
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

## 3.5 Desain Tabel

| Tabel | Fungsi |
|---|---|
| `users` | Data pengguna dan role |
| `sessions` | Session database driver |
| `categories` | Kategori produk |
| `suppliers` | Data pemasok |
| `warehouses` | Data gudang dan koordinat GPS |
| `products` | Master produk, SKU, harga, threshold, gambar |
| `warehouse_stocks` | Saldo stok per produk per gudang |
| `inventory_transactions` | Mutasi masuk, keluar, transfer |
| `audit_logs` | Audit operasi pengguna |
| `error_logs` | Error dan alert severity |
| `notifications` | In-app database notification |
| `jobs`, `failed_jobs`, `job_batches` | Laravel database queue |

## 3.6 ERD Ringkas

```text
users ──< inventory_transactions >── products >── categories
  │                                  │
  └──< audit_logs                    └── suppliers

products ──< warehouse_stocks >── warehouses
warehouses ──< inventory_transactions
users ──< notifications
users ──< error_logs.resolved_by
```

## 3.7 Indexing dan Skalabilitas

| Tabel | Index | Manfaat |
|---|---|---|
| `products` | `sku`, `category_id,is_active` | Search/filter produk |
| `warehouse_stocks` | unique `product_id,warehouse_id` | Lookup stok gudang |
| `inventory_transactions` | `product_id,warehouse_id` | Riwayat produk/gudang |
| `inventory_transactions` | `type,created_at` | Grafik dan filter transaksi |
| `audit_logs` | `user_id,created_at` | Audit user |
| `error_logs` | `severity,is_resolved` | Dashboard error |
| `jobs` | `queue` | Queue worker |

---

# Bab 4 - Implementasi Teknis, Tools, Algoritma, dan Keamanan

## 4.1 Tools dan Framework

| Komponen | Versi Aktual | Fungsi | Alasan Pemilihan |
|---|---:|---|---|
| Laravel | 11.54.0 | Backend MVC | Routing, ORM, validation, auth, queue, notification |
| PHP | ^8.2 | Runtime | Stabil dan didukung Laravel 11 |
| MySQL | 8.x | Database | ACID, indexing, familiar, sesuai implementasi uji |
| Laravel Queue DB | Built-in | Background job | Cukup untuk prototype tanpa Redis |
| DomPDF | 3.1.2 | Export PDF | Integrasi Laravel mudah |
| Maatwebsite Excel | 3.1.69 | Dukungan spreadsheet | Tersedia untuk pengembangan import Excel |
| Tailwind CSS | 3.4.19 | UI | Utility-first, responsif |
| Vite | 6.4.2 | Build asset | Build modern |
| Chart.js | 4.4.0 CDN | Grafik | Ringan dan cepat |
| Leaflet | 1.9.4 CDN | Peta gudang | Open-source dan gratis |

## 4.2 Algoritma Search Produk

Pencarian produk diimplementasikan pada `Product::scopeSearch()` dengan pencarian `LIKE` pada nama, SKU, dan deskripsi. Query dikombinasikan dengan filter kategori, filter stok rendah, sorting whitelist, dan pagination 15 data per halaman.

```text
Input keyword
  ├─ where name LIKE keyword
  ├─ orWhere sku LIKE keyword
  └─ orWhere description LIKE keyword
Apply filters, sort whitelist, paginate
```

## 4.3 Algoritma Stok Otomatis

| Operasi | Algoritma |
|---|---|
| Masuk | Validasi input, `updateOrCreate` stok gudang, `increment`, catat transaksi |
| Keluar | Cek stok cukup, `decrement`, catat transaksi |
| Transfer | Cek stok asal, `DB::transaction()`, decrement asal, increment tujuan, catat transaksi transfer |
| Alert | Setelah mutasi stok, jalankan `StockAlertService::checkAndAlert()` |

## 4.4 SQL Aktual

Contoh pemakaian SQL/query builder:

| File | Implementasi |
|---|---|
| `ReportController` | Join `warehouse_stocks`, `products`, `categories`, `warehouses` |
| `ReportController` | `DB::raw('SUM(...)')`, `DATE_FORMAT(created_at, '%Y-%m')` |
| `ProductController` | `whereRaw()` filter stok minimum |
| `Warehouse` model | Join dan sum nilai stok |
| `GenerateLargeReport` | Query laporan PDF besar |

## 4.5 Real-Time dan Monitoring

| Fitur | Cara Kerja | Interval |
|---|---|---:|
| Notifikasi | Fetch `/api/notifications/unread` | 12 detik |
| Resource server | Fetch `/api/server-resources` | 8 detik |
| Health check | GET `/api/health` | On demand |

## 4.6 Alert Notification

Alur alert:

```text
Transaksi/transfer selesai
  ▼
StockAlertService
  ├─ stok > threshold: selesai
  ├─ stok <= threshold: error_logs warning + notification
  └─ stok == 0: error_logs critical + notification
```

Target notifikasi: user aktif dengan role Admin dan Manajer Gudang.

## 4.7 Risiko Keamanan dan Mitigasi

| Risiko | Dampak | Mitigasi Aktual |
|---|---|---|
| SQL Injection | Data rusak/bocor | Eloquent, Query Builder, validation, whitelist sort |
| XSS | Script injection | Blade escape `{{ }}`, validasi input, CSP header |
| CSRF | Request palsu | `@csrf` pada form dan token pada PATCH fetch |
| Brute force login | Akun dibobol | RateLimiter 5 attempt/menit per email+IP |
| Session hijacking | Sesi dicuri | Session regenerate login, invalidate logout, http_only cookie |
| Broken access control | Role bypass | `auth`, `role` middleware, `canModify()` |
| Upload file berbahaya | Malware/script upload | Validasi `image`, mime jpg/jpeg/png/webp, max 2MB |
| Error disclosure | Stack trace tampil | Exception disimpan ke `error_logs`, custom 403/404 |
| Audit gap | Sulit forensik | `AuditLogMiddleware` mencatat operasi write |

## 4.8 Security Headers

`SecurityHeaders` menambahkan:

```text
X-Content-Type-Options: nosniff
X-Frame-Options: SAMEORIGIN
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: camera=(), microphone=(), geolocation=()
Content-Security-Policy: default-src 'self'; ...
```

## 4.9 Analisis Skalabilitas

| Area | Implementasi Saat Ini | Peningkatan Jika Beban Naik |
|---|---|---|
| Database | MySQL + index FK/filter | Tambah composite index, read replica |
| Queue | Database queue | Migrasi ke Redis queue bila job tinggi |
| Dashboard | Query saat load | Cache KPI 1-5 menit |
| File upload | Local storage | Object storage untuk multi-server |
| PDF | Background job | Worker terpisah dengan Supervisor |
| Web server | Single app server | Load balancer + horizontal scaling |

---

# Bab 5 - Manajemen Proyek, Migrasi, Cutover, dan Pembaharuan

## 5.1 Project Charter

| Elemen | Isi |
|---|---|
| Nama Proyek | SmartStock Pro |
| Sponsor | PT Maju Bersama Digital |
| Asesi/Developer | Husni Abdillah |
| Masalah | Spreadsheet manual, stok tidak sinkron, laporan lambat, tidak ada alert |
| Tujuan | Website inventaris multi-gudang real-time berbasis polling |
| Deliverable | Prototype Laravel, database MySQL, dashboard, report PDF, import CSV, dokumentasi |
| Batasan Waktu | 3 hari efektif / 18 jam |
| Kriteria Sukses | Modul utama bisa didemokan dan dokumen memenuhi FR.IA.04A/04B |

## 5.2 WBS dan Scope

| WBS | Pekerjaan | Output |
|---|---|---|
| 1.0 | Analisis kebutuhan | Scope dan kebutuhan |
| 2.0 | Desain arsitektur | Topologi, ERD, server spec |
| 3.0 | Auth dan RBAC | Login, role, session |
| 4.0 | Master data | Produk, kategori, gudang, supplier |
| 5.0 | Transaksi | Masuk, keluar, transfer |
| 6.0 | Dashboard | Chart, map, monitoring |
| 7.0 | Alert dan log | Notification, error log, audit log |
| 8.0 | Import/export | CSV import, PDF export |
| 9.0 | Testing/UAT | Test scenario dan sign-off |
| 10.0 | Dokumentasi | Laporan, user guide, FAQ |

## 5.3 Quality Checklist

- [ ] Login/logout berjalan dan session aman.
- [ ] Role Admin, Manajer, Staf, Viewer sesuai akses.
- [ ] CRUD master data berjalan.
- [ ] Transaksi keluar tidak boleh melebihi stok.
- [ ] Transfer antar gudang atomik.
- [ ] Alert stok minimum tercatat.
- [ ] Import CSV diproses oleh queue.
- [ ] PDF dapat diunduh.
- [ ] Audit log mencatat operasi write.
- [ ] Error log mencatat exception/job gagal.

## 5.4 Strategi Migrasi Spreadsheet ke MySQL

| Tahap | Aktivitas |
|---|---|
| Extract | Export Excel lama ke CSV UTF-8 |
| Transform | Normalisasi SKU, kategori, supplier, angka, threshold |
| Load | Upload CSV dan proses melalui `ProcessBatchImport` |
| Validate | Bandingkan `COUNT(products)` dan `SUM(warehouse_stocks.quantity)` |
| Sign-off | Manajer gudang menyetujui saldo awal |

### Mapping Field

| Spreadsheet | Database | Transformasi |
|---|---|---|
| Kode Barang | `products.sku` | Trim, uppercase, unique |
| Nama Barang | `products.name` | Trim |
| Kategori | `categories.name` / `products.category_id` | Lookup/create |
| Supplier | `suppliers.name` / `products.supplier_id` | Lookup |
| Harga | `products.price` | Numeric |
| Minimum Stok | `products.minimum_threshold` | Default 5 |
| Gudang | `warehouses.id` | Lookup nama/kota |
| Jumlah | `warehouse_stocks.quantity` | Integer >= 0 |

### Validasi SQL

```sql
SELECT COUNT(*) AS total_produk FROM products;

SELECT w.city, SUM(ws.quantity) AS total_stok
FROM warehouse_stocks ws
JOIN warehouses w ON w.id = ws.warehouse_id
GROUP BY w.city;

SELECT sku, COUNT(*) FROM products GROUP BY sku HAVING COUNT(*) > 1;
```

### Rollback Plan

| Langkah | Aksi |
|---|---|
| 1 | Aktifkan maintenance mode: `php artisan down` |
| 2 | Stop/restart queue worker: `php artisan queue:restart` |
| 3 | Restore backup MySQL pre-cutover |
| 4 | Verifikasi jumlah produk dan stok |
| 5 | Aktifkan aplikasi: `php artisan up` |
| 6 | Gunakan spreadsheet sebagai fallback sementara |

## 5.5 Cutover Plan

| Waktu | Aktivitas | PIC | Checklist |
|---|---|---|---|
| H-2 | Backup database/spreadsheet lama | Admin IT | [ ] |
| H-1 | Export CSV final | Manajer Gudang | [ ] |
| H-1 | Import ke staging | Developer | [ ] |
| H-1 | Validasi total stok | QA + Manajer | [ ] |
| H | Deploy via Git | Developer | [ ] |
| H | `composer install --no-dev`, `npm run build` | Developer | [ ] |
| H | `php artisan migrate --force` | Developer | [ ] |
| H | Import data final | Developer | [ ] |
| H | Smoke test login, CRUD, transaksi, laporan | QA | [ ] |
| H | Go-live | PM | [ ] |
| H+1 | Hypercare dan monitor error log | Developer/Admin | [ ] |

## 5.6 Simulasi Pembaharuan via Git

Skenario: menambahkan export Excel tanpa mengganggu laporan PDF.

| Tahap | Command/Proses | Tujuan |
|---|---|---|
| Branch | `git checkout -b feature/export-excel` | Isolasi fitur |
| Develop | Tambah controller, route, button | Implementasi |
| Test | `php artisan test` + manual UAT | Validasi |
| Merge | Pull request ke `develop` | Review |
| Release | `git checkout -b release/1.1.0` | Stabilkan |
| Deploy | `git pull && composer install --no-dev && php artisan migrate --force` | Production update |
| Rollback | `git revert <merge_commit>` | Aman tanpa force push |

## 5.7 Impact Analysis

| Perubahan | Modul Terdampak | Risiko | Mitigasi |
|---|---|---|---|
| Menambah field produk | Produk, import, laporan, PDF | Form/import gagal | Field nullable/default, update validation |
| FIFO cost-layer penuh | Transaksi, transfer, laporan | Perubahan logika besar | Tambah tabel `stock_batches`, UAT stok |
| Migrasi queue ke Redis | Import, PDF, notification | Worker salah konfigurasi | Staging test, rollback DB queue |
| Tambah gudang baru | Dashboard, transfer, laporan | Map/agregasi perlu validasi | Validasi koordinat dan stok awal |
| Tambah email alert | Alert, mail config | Email gagal | Database notification tetap fallback |

---

# Bab 6 - Dokumentasi Pelanggan, API, Troubleshooting, FAQ, dan UAT

## 6.1 Instalasi Lokal dengan MySQL

```bash
git clone <repository-url> SmartStock-Pro-BNSP
cd SmartStock-Pro-BNSP
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Edit `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smartstock_pro
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database
SESSION_DRIVER=database
CACHE_STORE=database
```

Jalankan:

```bash
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
php artisan queue:work
```

## 6.2 Akun Demo Seeder

| Email | Password | Role |
|---|---|---|
| `admin@smartstock.id` | `password` | Admin |
| `manager@smartstock.id` | `password` | Manajer Gudang |
| `staf@smartstock.id` | `password` | Staf Gudang |
| `viewer@smartstock.id` | `password` | Viewer |

## 6.3 Panduan Fitur Utama

| Fitur | Cara Penggunaan |
|---|---|
| Login | Buka `/login`, masukkan email dan password |
| Dashboard | Lihat KPI, grafik tren, peta, resource monitor |
| Produk | Tambah/edit produk, upload gambar, cari/filter/sort |
| Import CSV | Menu Produk, upload CSV, jalankan queue worker |
| Transaksi masuk | Pilih produk, gudang, supplier, jumlah; stok bertambah |
| Transaksi keluar | Pilih produk, gudang, jumlah; sistem validasi stok cukup |
| Transfer | Pilih produk, gudang asal/tujuan, jumlah; stok dipindah atomik |
| Laporan | Buka menu Laporan, download PDF |
| Audit Log | Admin melihat catatan aktivitas write |
| Error Log | Admin/Manajer melihat alert/error berdasarkan severity |

## 6.4 Dokumentasi API

Semua endpoint API memakai middleware `auth`.

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/api/health` | Health check aplikasi |
| GET | `/api/server-resources` | CPU, memory, response time |
| GET | `/api/stock?product_id={id}&warehouse_id={id}` | Ambil stok produk pada gudang tertentu untuk preview transfer |
| GET | `/api/notifications/unread` | Daftar notifikasi belum dibaca |
| PATCH | `/api/notifications/{id}/read` | Tandai satu notifikasi terbaca |
| PATCH | `/api/notifications/read-all` | Tandai semua notifikasi terbaca |

Contoh response `/api/server-resources`:

```json
{
  "cpu": 28.4,
  "memory": 35.7,
  "response_time": 68,
  "timestamp": "2026-05-27T16:00:00.000000Z"
}
```

## 6.5 Troubleshooting

| Masalah | Penyebab | Solusi |
|---|---|---|
| Login gagal | Password salah/user nonaktif/rate limit | Cek akun, tunggu rate limit selesai |
| Error 403 | Role tidak berhak | Gunakan role sesuai atau ubah via Admin |
| Error 419 | CSRF/session expired | Refresh dan login ulang |
| Gambar 404 | Storage link belum dibuat | `php artisan storage:link` |
| Import CSV tidak jalan | Queue worker mati | `php artisan queue:work` |
| PDF besar tidak selesai | Job gagal/worker mati | Cek `failed_jobs` dan `error_logs` |
| Notifikasi tidak muncul | Threshold belum tercapai atau role bukan target | Pastikan stok <= threshold dan login Admin/Manajer |
| Query lambat | Data membesar/index kurang | Cek `EXPLAIN`, tambah index, optimasi query |
| Transfer ditolak | Stok asal tidak cukup/gudang sama | Perbaiki quantity dan tujuan |
| Dashboard resource kosong | API gagal atau belum login | Cek `/api/server-resources` dan session |

Perintah debugging:

```bash
php artisan about
php artisan route:list
php artisan migrate:status
php artisan queue:failed
php artisan queue:retry all
php artisan optimize:clear
php artisan test
```

## 6.6 Skenario UAT

| ID | Modul | Skenario | Expected Result | Status |
|---|---|---|---|---|
| UAT-01 | Auth | Login Admin | Masuk dashboard dan audit tercatat | Lulus |
| UAT-02 | RBAC | Viewer akses `/users` | Ditolak 403 | Lulus |
| UAT-03 | Produk | Tambah produk dengan gambar | Produk tersimpan, preview muncul | Lulus |
| UAT-04 | Search | Cari SKU produk | Produk sesuai keyword muncul | Lulus |
| UAT-05 | Transaksi | Barang masuk 10 unit | Stok bertambah 10 | Lulus |
| UAT-06 | Transaksi | Barang keluar melebihi stok | Ditolak dengan pesan validasi | Lulus |
| UAT-07 | Transfer | Transfer 5 unit antar gudang | Stok asal turun dan tujuan naik | Lulus |
| UAT-08 | Alert | Stok turun sampai threshold | Error log dan notifikasi muncul | Lulus |
| UAT-09 | Laporan | Export PDF | File PDF terunduh | Lulus |
| UAT-10 | Import | Upload CSV valid | Produk diproses oleh queue | Lulus |
| UAT-11 | Monitoring | Dashboard resource | CPU/memory/response time terupdate | Lulus |
| UAT-12 | Debugging | Simulasi job gagal | `failed_jobs`/`error_logs` mencatat kegagalan | Lulus |

## 6.7 FAQ

### 1. Apa itu SmartStock Pro?
SmartStock Pro adalah website inventaris multi-gudang untuk PT Maju Bersama Digital, menggantikan spreadsheet manual dengan sistem berbasis Laravel.

### 2. Database apa yang digunakan?
Database uji/production menggunakan MySQL 8.x. SQLite hanya default `.env.example` bawaan Laravel untuk development cepat.

### 3. Apakah mendukung 5 gudang?
Ya. Seeder menyediakan gudang Jakarta, Surabaya, Bandung, Medan, dan Makassar lengkap dengan koordinat.

### 4. Apa saja role pengguna?
Admin, Manajer Gudang, Staf Gudang, dan Viewer.

### 5. Bagaimana sistem mencegah stok minus?
Transaksi keluar dan transfer memeriksa stok tersedia sebelum melakukan decrement.

### 6. Apakah transfer antar gudang aman?
Ya. Transfer memakai `DB::transaction()` sehingga perubahan stok asal dan tujuan atomik.

### 7. Apakah real-time memakai WebSocket?
Tidak. Implementasi aktual memakai polling API berkala: 12 detik untuk notifikasi dan 8 detik untuk resource monitor.

### 8. Bagaimana cara import data produk?
Upload file CSV melalui menu Produk. Job `ProcessBatchImport` memproses data di background.

### 9. Mengapa laporan PDF besar perlu queue?
Agar proses generate PDF tidak membuat UI freeze dan tidak menahan request utama.

### 10. Apakah sistem memakai Redis atau Celery?
Tidak. Queue aktual memakai Laravel database queue. Redis dapat menjadi rencana peningkatan jika job meningkat.

### 11. Apakah sistem memakai PostgreSQL?
Tidak. Untuk implementasi ini, dokumentasi diselaraskan ke MySQL sesuai penggunaan Anda.

### 12. Bagaimana jika aplikasi error?
Unhandled exception dicatat ke tabel `error_logs` dengan severity `critical` dan dapat dilihat dari menu Error Log.

## 6.8 Jawaban Singkat untuk Pertanyaan Asesor

| Aspek | Jawaban Ringkas |
|---|---|
| Analisis dan perancangan | Prioritas dimulai dari keamanan, arsitektur MySQL-Laravel, database, lalu fitur dashboard dan laporan |
| Notifikasi tidak muncul/query lambat | Cek `StockAlertService`, `notifications`, `error_logs`, `failed_jobs`, lalu optimasi index/query dengan `EXPLAIN` |
| UAT | Susun skenario bersama gudang/manajemen, catat hasil, ambil sign-off |
| Scope creep | Catat di change log, analisis impact, buat branch Git, jadwalkan release berikutnya |

---

# Lampiran A - Struktur Repository

| Area | Path |
|---|---|
| Web routes | `routes/web.php` |
| API routes | `routes/api.php` |
| Controllers | `app/Http/Controllers` |
| API Controllers | `app/Http/Controllers/Api` |
| Models | `app/Models` |
| Jobs | `app/Jobs` |
| Services | `app/Services` |
| Notifications | `app/Notifications` |
| Middleware | `app/Http/Middleware` |
| Migrations | `database/migrations` |
| Seeder | `database/seeders/DatabaseSeeder.php` |
| Views | `resources/views` |
| PDF view | `resources/views/reports/inventory-pdf.blade.php` |

# Lampiran B - Gudang Seeder

| No | Gudang | Kota | Latitude | Longitude |
|---|---|---|---:|---:|
| 1 | Gudang Jakarta Pusat | Jakarta | -6.2088 | 106.8456 |
| 2 | Gudang Surabaya | Surabaya | -7.2575 | 112.7521 |
| 3 | Gudang Bandung | Bandung | -6.9175 | 107.6191 |
| 4 | Gudang Medan | Medan | 3.5952 | 98.6722 |
| 5 | Gudang Makassar | Makassar | -5.1477 | 119.4327 |