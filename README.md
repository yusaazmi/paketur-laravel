# Panduan Penggunaan

Ini adalah aplikasi backend test Paketur
Ini adalah requirement untuk menjalankan aplikasi:

- [PHP](https://www.php.net/) (versi 8.1.0 atau lebih tinggi)
- [Composer](https://getcomposer.org/) (versi 2.7 atau lebih tinggi) 
- [Laravel](https://laravel.com/) (versi 11)
- [Database](https://www.mysql.com/) (MySQL, versi 8.1)

## Instalasi

1. Pastikan Anda memiliki PHP, Composer, dan MySQL terpasang di komputer Anda.
2. Clone repositori ini ke komputer Anda.
3. Buka terminal dan navigasikan ke direktori proyek.
4. Jalankan perintah berikut untuk menginstal semua dependensi:

    ```bash
    composer install
    ```

5. Salin file `.env.example` menjadi `.env`:

    ```bash
    cp .env.example .env
    ```

6. Generate kunci aplikasi:

    ```bash
    php artisan key:generate
    ```

7. Atur koneksi basis data Anda di dalam file `.env`.
8. Jalankan migrasi untuk membuat tabel basis data:

    ```bash
    php artisan migrate:fresh
    ```

9. Jalankan seeder untuk mengisi data awal ke dalam basis data:

    ```bash
    php artisan db:seed
    ```
10. Untuk menjalankan unit test anda dapat melakukan perintah berikut:
    ```bash
    php artisan test
    ```
11. Untuk generate code coverage anda dapat melakukan perintah berikut :
    ```bash
    php vendor/bin/phpunit --coverage-html reports
    ```
    dan kemudian anda bisa membuka reports/index.html melalui explore
14. Untuk menjalankan aplikasi anda dapat menggunakan perintah berikut :
    ```bash
    php artisan serve
    ```
    Kemudian masuk ke
    ```bash
    http://127.0.0.1:8000/api/login
    ```
