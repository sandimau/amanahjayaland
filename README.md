# Backend Amanah Jaya Land

Website publik menggunakan **desain asli** yang sudah tersedia dalam `public/original-design/*-standalone.html` beserta aset di `public/original-assets/`, bukan Blade baru. Seluruh aplikasi Laravel berada langsung di **root repository** setelah di-clone. File HTML sumber yang besar dan skrip ekstraksi di luar aplikasi ini hanya tersedia di komputer pengembangan, **tidak disertakan dalam repository**. Gambar besar menggunakan varian WebP bila ukurannya lebih kecil. Laravel menyediakan API serta halaman admin berbasis Blade.

## Menjalankan lokal

Prasyarat: PHP 8.4 (tersedia pada mesin ini), Composer, ekstensi SQLite (`pdo_sqlite`) dan `fileinfo`.

```powershell
cd amanahjayaland
composer install
Copy-Item .env.example .env # hanya bila .env belum ada
php artisan key:generate # hanya untuk .env baru
New-Item database/database.sqlite -ItemType File # hanya bila belum ada
php artisan migrate
php artisan storage:link
php artisan admin:create admin@contoh.com
php artisan serve
```

API tersedia di `http://127.0.0.1:8000/api`. Untuk MySQL, ubah `DB_CONNECTION`, `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` dalam `.env` sebelum migrasi. **Jangan commit `.env` atau database lokal.** Saat produksi, set `APP_DEBUG=false`, gunakan HTTPS, dan batasi CORS ke domain frontend.

## Website & admin

Setelah `php artisan serve`, buka `http://127.0.0.1:8000/` untuk website dengan desain HTML asli. Halaman tersedia: Beranda, Layanan, Tentang Kami, Galeri, Detail Proyek, dan Kontak. `public/js/original-bridge.js` menghubungkan tautan antarhalaman, formulir kontak ke `POST /api/contact`, kartu proyek beranda/galeri, foto galeri, dan detail proyek ke API. Proyek baru otomatis berstatus draf sampai dicentang **Tampilkan proyek di website**. Kartu/foto demo dari HTML asli **disembunyikan bila belum ada konten yang dipublikasikan**; galeri dan foto proyek mendukung paginasi. Detail proyek menyembunyikan contoh spesifikasi, angka KPR, dan rekomendasi yang tidak tersimpan di database. Beberapa gambar/logo yang pada sumber HTML memakai URL `/_blob/...` tidak ikut terkemas dan diganti placeholder lokal; untuk mendapatkan gambar aslinya, berikan berkas aset asli.

Buka `http://127.0.0.1:8000/login` dan masuk menggunakan akun dari `php artisan admin:create admin@contoh.com`. Admin dapat membuat/edit/hapus proyek, mengunggah foto (maks. 5 MB, JPG/PNG/WebP), dan membaca/menghapus pesan. Form admin web menggunakan sesi dan CSRF Laravel; token API tidak perlu digunakan dalam browser. CSS admin ada di `public/css/site.css`, tanpa proses build Node.js. Setelah menambahkan gambar, pastikan `php artisan storage:link` telah dijalankan. Form kontak menyimpan pesan ke database, **belum otomatis mengirim email**. Beberapa bagian lain dari desain asli (misalnya angka statistik dan teks perusahaan) masih berupa contoh; audit konten bersama pemilik proyek sebelum publikasi.

### Mengubah konten desain asli

Di admin buka **Konten Website** (`/admin/konten`), pilih salah satu dari enam halaman, pilih **Teks**, **Gambar**, **Tautan**, **Latar**, atau **Petunjuk isian**, kemudian klik bagian pada pratinjau. Ubah teks/URL atau unggah foto JPG/PNG/WebP (maksimum 5 MB), lalu klik **Simpan Perubahan**. Header dan footer yang sama di setiap halaman otomatis disimpan sebagai konten bersama; perubahan bagian lain hanya berlaku untuk halaman yang sedang dipilih. Daftar perubahan di bawah pratinjau dapat dikembalikan ke konten semula. Editor menyimpan data di tabel `site_contents`, bukan memodifikasi file HTML sumber. Salinan teks/gambar dinamis proyek dan galeri tetap di menu **Proyek**. Menu **Pengaturan** (`/admin/pengaturan`) mengubah harga, DP, bunga, dan tenor awal kalkulator serta daftar layanan formulir kontak. Perubahan struktur, skrip interaktif, rumus KPR, serta data spesifikasi proyek yang belum ada dalam database **tidak** diubah melalui editor visual; bagian ini memerlukan pengembangan tersendiri. Pratinjau detail proyek hanya digunakan untuk mengedit bagian statis seperti header/footer.

Jika editor gambar tidak berfungsi, pastikan `php artisan storage:link` sudah dijalankan. Pengunjung mengambil perubahan dari `GET /api/site-content?page=home|services|about|contact|gallery|project` dan `GET /api/site-settings` dengan `Cache-Control: no-store` sehingga penyimpanan perubahan tidak memerlukan regenerasi desain.

## Endpoint

- `GET /api/projects` — proyek yang sudah terbit (pagination).
- `GET /api/projects/{slug}` — detail proyek beserta foto.
- `GET /api/gallery` — foto proyek yang sudah terbit (pagination).
- `POST /api/contact` — kirim pesan (`name`, `email`, `message`, opsional `phone`, `subject`; dibatasi 5 permintaan/menit/IP).
- `POST /api/admin/login` — kirim `email` dan `password`, ambil `token` (dibatasi 5 permintaan/menit/IP).
- Dengan header `Authorization: Bearer <token>`: `POST /api/admin/logout`, CRUD `/api/admin/projects`, `POST /api/admin/gallery`, `DELETE /api/admin/gallery/{image}`, `GET /api/admin/messages`, `GET /api/admin/messages/{message}`, `DELETE /api/admin/messages/{message}`.

Untuk unggah cover proyek gunakan multipart `cover_image`; untuk foto galeri gunakan multipart `project_id` dan `image`. Ukuran maksimum 5 MB, format JPG/PNG/WebP. Path `cover_image`/`image_path` dari respons bisa ditampilkan dengan URL `http://127.0.0.1:8000/storage/<path>` setelah `storage:link`. Update berkas via `POST` multipart dengan field `_method=PUT` (HTML/form-data tidak diproses sebagai PUT multipart oleh PHP). Field proyek: `name`, `slug` (unik), `summary`, `description`, `location`, `is_published` (default false).

`php artisan test` menjalankan pengujian API dan website. Backend menggunakan PHP 8.4 dan Laravel 12.74.0; `composer audit` saat pembaruan tidak menemukan advisory. Untuk produksi tetap siapkan HTTPS, kredensial dan database produksi, cadangan data, pengiriman email jika diperlukan, serta pembatasan CORS. PHP 8.4 sudah diprioritaskan pada PATH pengguna Windows (buka terminal baru agar aktif). Composer global 2.7.4 di mesin ini menampilkan peringatan deprecation pada PHP 8.4; perbarui Composer secara terpisah bila ingin menghilangkan peringatan tersebut.

Halaman publik mengirim `Cache-Control: public, max-age=600` dan tidak membuat sesi/cookie. Untuk Apache, `public/original-assets/.htaccess` mengaktifkan cache aset selama seminggu; `php artisan serve` **mengabaikan `.htaccess`**. Jika memakai Nginx, atur cache pada URL `/original-assets/` secara terpisah. Aset yang dibagikan antarhalaman hanya bisa mendapat manfaat cache browser bila server mengirim header cache yang sesuai.
