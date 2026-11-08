# Web Mitra MMA

Portal web PT Mustika Makmur Abadi untuk katalog publik dan pengelolaan operasional mitra. Halaman aplikasi menggunakan Laravel Blade dengan controller HTTP; autentikasi dikelola melalui Laravel Fortify.

## Persyaratan

- PHP 8.3 atau lebih baru
- Composer
- Node.js dan npm
- Database yang dikonfigurasi di `.env`

## Menjalankan aplikasi

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
```

Atur koneksi database di `.env`, lalu jalankan:

```powershell
php artisan migrate
npm install
npm run build
php artisan serve
```

## Validasi

```powershell
php artisan test
vendor/bin/pint --test
vendor/bin/phpstan analyse
```

Login terkunci selama satu menit setelah lima percobaan sandi yang gagal. Nilai tersebut dapat diatur di `config/security.php`.
