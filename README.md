# ASKARSA — Laravel 12 + Blade + Tailwind CSS

Starter implementasi desain PDF ASKARSA (35 frame). Paket ini menyediakan fondasi UI, routing, layout publik/panel, katalog, autentikasi visual, dashboard pengguna, dan dashboard admin. Data contoh masih statis; hubungkan ke Eloquent/database sebelum produksi.

## Kebutuhan
PHP 8.2+, Composer 2, Node.js 20+, MySQL 8 (atau SQLite).

## Instalasi
```bash
composer create-project laravel/laravel askarsa "^12.0"
cd askarsa
# Salin/merge isi paket ini ke root project
npm install
npm install tailwindcss @tailwindcss/vite
cp .env.example .env
php artisan key:generate
# Atur DB_* pada .env
php artisan migrate
npm run build
php artisan serve
```
Development: jalankan `php artisan serve` dan `npm run dev` pada dua terminal.

## Akun demo
Belum ada autentikasi backend aktif. Pasang Laravel Breeze/Starter Kit untuk autentikasi produksi, lalu hubungkan form login/register ke guard Laravel.

## Struktur
- `routes/web.php`: route publik, pengguna, dan admin.
- `app/Http/Controllers`: controller halaman dan aksi contoh.
- `resources/views/layouts`: layout utama.
- `resources/views/components`: navbar, footer, sidebar, kartu.
- `resources/views/pages`: halaman publik/katalog.
- `resources/views/dashboard`: halaman aktivitas pengguna.
- `resources/views/admin`: halaman administrator.
- `resources/css/app.css`: token warna dan komponen Tailwind.

## Pemetaan desain
35 frame dikelompokkan menjadi route: beranda; auth; katalog jasa/freelance/full-time; detail jasa/lowongan; profil dan form jasa; pemesanan; pembuatan lowongan dan lamaran; daftar aktivitas pengguna; dashboard/moderasi/admin/master data. Beberapa frame merupakan state alternatif dari halaman yang sama (empty state, riwayat, status pesanan, tab master data).

## Catatan keamanan & ruang lingkup
- Data contoh hanya untuk preview layout.
- Tambahkan middleware `auth`, role/permission, validasi, pagination, audit log, dan policy sebelum deployment.
- CV dan bukti laporan harus disimpan pada disk privat dan hanya diakses melalui controller berotorisasi.
- Sesuai fondasi desain: tidak ada pembayaran online, e-wallet, chat internal, GPS, verifikasi resmi, atau aplikasi native.
