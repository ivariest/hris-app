# HRIS App

HRIS App adalah aplikasi internal berbasis Laravel untuk mengelola data karyawan, organisasi, attendance, recruitment, leave, allowance, dan kebutuhan General Affair.

Project ini dibangun dari Laravel 12 dengan Blade, Tailwind CSS, Alpine.js, dan Vite. Saat ini project tidak menggunakan Laravel Livewire.

## Tech Stack

- PHP 8.2+
- Laravel 12
- MySQL
- Blade
- Tailwind CSS 4
- Alpine.js
- Vite
- Pest untuk testing

## Modul Utama

- Authentication: sign in, sign up, logout
- Master organisasi: company, department, sub department, position level, position, location
- Employee management: employee profile, agreement, promotion, mutation, demotion, deactivation
- Attendance: shift, holiday, import attendance, adjustment, recap, attendance report
- Leave: annual leave dan collective leave
- Recruitment: manpower request, candidate pipeline, recruitment report
- Allowance report: rekap uang makan dan transport dengan potongan keterlambatan
- General Affair: daftar kendaraan asset, dokumen STNK/KIR, service kendaraan

## Requirements

- PHP 8.2 atau lebih baru
- Composer
- Node.js 18 atau lebih baru
- npm
- MySQL atau MariaDB
- Laragon direkomendasikan untuk development lokal di Windows

## Setup Lokal

Clone repository:

```bash
git clone <repository-url>
cd hris-app
```

Install dependency PHP:

```bash
composer install
```

Install dependency frontend:

```bash
npm install
```

Copy file environment:

```bash
copy .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

Sesuaikan database di `.env`:

```env
APP_NAME=HRIS
APP_URL=http://hris-app.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hris
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
```

Jalankan migration:

```bash
php artisan migrate
```

Buat storage link untuk file upload:

```bash
php artisan storage:link
```

## Menjalankan Project

Untuk development Laravel + Vite:

```bash
composer run dev
```

Command ini menjalankan Laravel server, queue listener, log monitor, dan Vite dev server.

Jika menggunakan Laragon virtual host, jalankan Laragon seperti biasa lalu tetap jalankan Vite untuk load CSS/JS development:

```bash
npm run dev
```

Untuk build asset production:

```bash
npm run build
```

## Testing

Jalankan test:

```bash
php artisan test
```

Atau lewat script Composer:

```bash
composer run test
```

## Command Berguna

Clear cache Laravel:

```bash
php artisan optimize:clear
```

Cache ulang view:

```bash
php artisan view:cache
```

Lihat daftar route:

```bash
php artisan route:list
```

Rollback migration terakhir:

```bash
php artisan migrate:rollback
```

## Catatan Development

- File upload disimpan melalui Laravel storage, jadi pastikan `php artisan storage:link` sudah dijalankan.
- Session menggunakan database, jadi tabel session harus ada dari migration Laravel.
- Untuk tampilan development, Vite harus berjalan agar CSS dan JS terbaru ter-load.
- Kalau muncul error koneksi MySQL refused, pastikan MySQL/Laragon sudah aktif dan konfigurasi `.env` sesuai.

## Checklist Sebelum Push

```bash
php artisan optimize:clear
php artisan test
npm run build
git status
```

Pastikan tidak ada file environment lokal seperti `.env` ikut ter-commit.

## License

Project ini mengikuti license yang digunakan repository internal atau ketentuan pemilik project.
