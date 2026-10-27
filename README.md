# Vaultify

Vaultify adalah password manager pribadi untuk tugas kuliah. Proyek ini memakai Laravel 13, PHP 8.4, MySQL 8.4, Blade, Tailwind CSS, Vite, dan JavaScript vanilla.

## Struktur penting

- `app/Http/Controllers/Auth/` - controller registrasi, login, dan logout manual.
- `app/Models/User.php` - model pengguna dengan mass assignment terbatas dan cast password hashed.
- `database/migrations/0001_01_01_000000_create_users_table.php` - tabel users, password reset tokens, dan sessions.
- `resources/views/auth/` - halaman login dan registrasi.
- `resources/views/components/layouts/` - layout Blade guest dan pengguna login.
- `resources/views/vault.blade.php` - vault credential dengan pencarian, favorit, reveal/copy, dan status wordlist.
- `resources/views/password-generator.blade.php` - generator password aman berbasis Web Crypto API.
- `app/Services/PasswordWordlistService.php` - pencocokan exact dan case-sensitive terhadap wordlist lokal.
- `tests/Feature/` - pengujian autentikasi, CRUD vault, generator, dan wordlist.
- `compose.yaml` dan `docker/php/Dockerfile` - konfigurasi container aplikasi dan MySQL.

## Menyiapkan koneksi database

Salin `.env.example` menjadi `.env` jika belum tersedia, lalu isi `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, dan `DB_ROOT_PASSWORD` dengan nilai lokal Anda sendiri. Jangan menyimpan kredensial ke repository.

Untuk penggunaan `compose.yaml`, `DB_HOST` harus bernilai `mysql`. Jalankan dari direktori proyek menggunakan Docker Compose yang tersedia di lingkungan Anda. Jika Docker Compose plugin tidak tersedia, gunakan container yang sudah disiapkan oleh lingkungan kerja dan jangan menampilkan kredensial lokal.

Jangan menjalankan migration sebelum MySQL aktif dan koneksi berhasil diverifikasi.

## Menjalankan aplikasi

Dengan Docker Compose:

```bash
docker compose up --build
docker compose exec app php artisan migrate
```

Buka `http://localhost:8000`.

Jika Docker Compose belum tersedia, container PHP dapat dijalankan dengan pola berikut setelah container MySQL dan network disiapkan:

```bash
docker build -f docker/php/Dockerfile -t vaultify-app .
docker run --rm -p 8000:8000 -v "$PWD:/var/www/html" vaultify-app
```

## Pengujian dan build asset

Pengujian dapat dijalankan tanpa database development karena `phpunit.xml` menggunakan SQLite in-memory:

```bash
docker run --rm -v "$PWD:/app" -w /app composer:2 php artisan test
```

Build asset frontend:

```bash
npm install
npm run build
```

Pada lingkungan tanpa Node.js host, gunakan container Node:

```bash
docker run --rm -u "$(id -u):$(id -g)" -v "$PWD:/app" -w /app node:22 npm install
docker run --rm -u "$(id -u):$(id -g)" -v "$PWD:/app" -w /app node:22 npm run build
```

## Wordlist lokal

Wordlist pengujian sintetis disimpan di `storage/app/private/wordlists/` dan tidak boleh
diakses melalui route publik atau dimasukkan ke repository. Import dilakukan secara streaming:

```bash
docker exec vaultify-app php artisan wordlist:import storage/app/private/wordlists/vaultify-synthetic-20261026.txt
```

Password credential tetap disimpan terenkripsi dengan `APP_KEY`; wordlist lokal adalah dataset
referensi dan tidak merepresentasikan kredensial pengguna.

## Isolasi pengujian

`phpunit.xml` dan `tests/TestCase.php` memaksa seluruh feature test memakai SQLite in-memory.
Guard runtime akan menghentikan test bila driver atau database aktif bukan SQLite `:memory:`.
Jangan menjalankan test tanpa memverifikasi guard tersebut, terutama ketika MySQL development
berisi data yang ingin dipertahankan.

## Arsitektur dan skema

Autentikasi menggunakan controller Laravel manual, session authentication, middleware `guest`
dan `auth`, serta rate limiting login. `CredentialController` selalu mengambil credential melalui
relasi user yang sedang login. Route mutation menggunakan POST, PUT/PATCH, atau DELETE dan
dilindungi CSRF.

Tabel `credentials` berisi `user_id`, nama layanan, URL, username, ciphertext password dan
catatan, flag favorit, status wordlist, serta timestamps. Foreign key `user_id` mengarah ke
`users.id` dengan cascade saat user dihapus. Kolom ciphertext memakai `TEXT`/`LONGTEXT`.
Tabel `password_wordlist` memiliki unique index pada `password_value` dan collation biner
MySQL untuk pencocokan case-sensitive.

Password credential dan catatan dienkripsi dengan `Crypt::encryptString()` menggunakan `APP_KEY`
yang sudah ada. Password login menggunakan hashing Laravel dan tidak dapat dipulihkan.

## Generator password

Generator berjalan sepenuhnya di browser menggunakan `crypto.getRandomValues()`, rejection
sampling untuk menghindari modulo bias, dan Fisher-Yates shuffle. Panjang yang didukung adalah
8--64 karakter. Setiap kategori yang dipilih menyumbang minimal satu karakter. Hasil tidak
dikirim ke server atau browser storage.

## Siklus wordlist

Command `wordlist:import` membaca file secara streaming, menghapus hanya line ending, melewati
baris kosong dan entri terlalu panjang, lalu memakai insert idempotent untuk duplikasi. Setelah
import, credential yang dapat didekripsi diperiksa ulang. Ciphertext yang tidak valid dilewati.
Nilai password tidak dicetak oleh command.

## Hasil verifikasi Milestone 8

Pemeriksaan terakhir yang dijalankan:

- `php artisan test`: 32 test lulus, 159 assertion.
- `vendor/bin/pint --test`: lulus.
- `npm run build`: lulus.
- `php artisan migrate:status`: seluruh 6 migration berstatus `Ran`.
- Runtime web: `APP_ENV=local`, driver `mysql`, database `vaultify`.
- MySQL development: `users=0`, `credentials=0`, `password_wordlist=120` setelah data E2E
  dibersihkan.
- Missing CSRF pada web runtime menghasilkan HTTP 419.
- Import ulang wordlist: 0 entri baru, 120 duplikat, 0 baris kosong, 0 dilewati.
- Browser memverifikasi CRUD, search title/username/URL, favorite, reveal/hide/copy, wordlist
  match/non-match, generator validation, clipboard failure, guest redirect, ownership 404,
  dan mobile navigation.

Screenshot aktual tersedia di `docs/screenshots/`, termasuk login, register, vault dummy,
form create/edit, search/favorites, status wordlist, generator validation, generator, dan
mobile vault.
