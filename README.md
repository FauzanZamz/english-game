# EnglishEdu Pack (Laravel)

Paket ini berisi file-file inti untuk website edukasi Bahasa Inggris dengan 2 game: **Spelling Bee** dan **Crossword**.

## Cara Pakai (Ringkas)
1. Buat project Laravel (11+):  
   `composer create-project laravel/laravel englishedu && cd englishedu`
2. Install Breeze (opsional untuk auth Blade):  
   ```bash
   composer require laravel/breeze --dev
   php artisan breeze:install blade
   npm install && npm run build
   ```
3. Copy **konten folder** dari `englishedu_pack/` ke folder project Laravel Anda (merge struktur folder).
4. Set `.env` untuk koneksi database, lalu:
   ```bash
   php artisan migrate
   php artisan db:seed --class=WordSeeder
   php artisan serve
   ```
5. Akses setelah login:
   - Spelling Bee: `/spelling`
   - Crossword: `/crossword`
   - Leaderboard: `/leaderboard/spelling`, `/leaderboard/crossword`

## Catatan
- Seeder mengharapkan file json kata di: `storage/app/words/`:
  - `animals.json`, `fruits_vegetables.json`, `jobs.json`, `music_instruments.json`
- Service `LexicoService` akan menggunakan `dictionaryapi.dev` dan `wikipedia summary` (tanpa API key). Ada caching via table `cached_defs`.
- Jika Anda tidak ingin memanggil API eksternal, perbarui service untuk baca definisi dari file lokal.
- UI voice (Spelling Bee) menggunakan Web Speech API (Chrome).

Selengkapnya ikuti komentar pada masing-masing file.
