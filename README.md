# SIMANTAP

## 1. Tentang Proyek

SIMANTAP adalah aplikasi pelaporan dan penanganan kerusakan sarana prasarana sekolah. Proyek ini dibuat menggunakan Laravel dan MySQL untuk membantu pencatatan laporan, pembagian tugas petugas, pengajuan dana, serta konfirmasi hasil pekerjaan.

Proyek ini berangkat dari beberapa kebutuhan dalam pengelolaan fasilitas sekolah:

- Laporan kerusakan perlu dicatat di satu tempat agar tidak tercecer atau terlewat.
- Koordinator perlu mengetahui laporan yang belum diperiksa dan petugas yang sedang menangani pekerjaan.
- Pelapor perlu mendapat informasi tentang perkembangan laporannya, termasuk jika ada kendala.
- Barang dan lokasinya perlu tercatat dengan jelas agar laporan mengacu pada barang yang tepat.

Ada tiga peran pengguna dalam aplikasi:

| Peran | Tugas utama |
|---|---|
| PJ Lab | Melaporkan kerusakan di laboratorium, memantau laporan, dan mengonfirmasi hasil penanganan atas laporannya. |
| Petugas Sarpras | Melaporkan kerusakan, memeriksa barang sesuai penugasan, mengajukan dana jika diperlukan, dan mencatat hasil pekerjaan. |
| Koordinator Sarpras | Membagi tugas, meninjau rekomendasi, menetapkan prioritas saat persetujuan, memutuskan pengajuan dana, dan menutup laporan. |

Secara garis besar, alurnya adalah:

**Laporan dibuat → Pemeriksaan → Persetujuan rencana → Penanganan → Konfirmasi pelapor → Penutupan laporan.**

Jika penanganan membutuhkan biaya, petugas mengajukan dana sebelum pekerjaan dilaksanakan. Jika hasilnya masih bermasalah, laporan dilanjutkan dengan pemeriksaan ulang. Perkembangan laporan juga dapat dilihat melalui halaman Transparansi tanpa perlu login.

## 2. Cara Menjalankan Proyek

Siapkan PHP 8.2 atau lebih baru, Composer, dan MySQL 8.0.16 atau lebih baru. Laragon dapat digunakan untuk menjalankan PHP dan MySQL di Windows. Koneksi internet diperlukan saat memasang dependensi dan membuka halaman yang menggunakan Tailwind CDN serta Google Fonts.

Langkah berikut digunakan untuk pemasangan baru dengan database bernama **`simantap-sman2`**. Buka folder proyek di VS Code, lalu gunakan terminal **PowerShell** pada folder yang berisi file `artisan`.

**1. Pasang dependensi proyek**

```powershell
composer install
```

Perintah ini memasang paket yang dibutuhkan Laravel ke folder `vendor`.

**2. Siapkan file konfigurasi**

Jika belum ada file `.env`, salin `.env.example`:

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

Jika proyek sudah mempunyai `.env` dan sebelumnya sudah berjalan, pertahankan file serta `APP_KEY` yang ada. Tidak perlu menyalin ulang atau membuat key baru.

**3. Buat database dan atur koneksi**

Nyalakan MySQL melalui Laragon. Buka phpMyAdmin atau aplikasi pengelola database, lalu buat database kosong dengan nama `simantap-sman2`. Jika menggunakan SQL, jalankan:

```sql
CREATE DATABASE `simantap-sman2`
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

Kemudian sesuaikan bagian berikut pada file `.env`:

```dotenv
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=simantap-sman2
DB_USERNAME=root
DB_PASSWORD=
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

Isi `DB_USERNAME`, `DB_PASSWORD`, dan `DB_PORT` sesuai pengaturan MySQL di laptop masing-masing. Password boleh kosong jika akun MySQL memang tidak menggunakan password. File `.env` berisi konfigurasi lokal dan tidak perlu diunggah ke GitHub.

**4. Buat tabel dan isi data awal**

Setelah koneksi diarahkan ke database baru yang masih kosong, jalankan:

```powershell
php artisan config:clear
php artisan migrate --seed
```

Migration membuat tabel aplikasi. Identitas barang memakai `inventaris_id` yang diberikan otomatis oleh database. Seeder mengisi data awal, termasuk akun pengguna, ruangan, barang, dan contoh laporan. Tidak perlu mengimpor file SQL secara terpisah.

**5. Jalankan aplikasi**

```powershell
php artisan serve
```

Buka [http://127.0.0.1:8000](http://127.0.0.1:8000) di browser dan biarkan terminal tetap berjalan. Halaman pertama menampilkan Transparansi. Gunakan tombol Login untuk masuk sesuai peran pengguna.

Halaman saat ini menggunakan CSS di folder `public`, Tailwind CDN, dan Google Fonts

**6. Masuk menggunakan akun awal**

Setelah seed berhasil dijalankan, gunakan salah satu akun berikut. Password awal seluruh akun adalah **`SimantapDev123!`**.

| Peran | Email |
|---|---|
| PJ Lab Kimia | `pj.kimia@example.test` |
| PJ Lab Fisika | `pj.fisika@example.test` |
| PJ Lab Biologi | `pj.biologi@example.test` |
| Petugas A | `petugas.a@example.test` |
| Petugas B | `petugas.b@example.test` |
| Koordinator Sarpras | `koordinator@example.test` |

Untuk membuka proyek pada penggunaan berikutnya, cukup nyalakan MySQL dan jalankan `php artisan serve`. Tidak perlu memasang dependensi, menjalankan migration, atau mengisi data awal setiap kali membuka aplikasi. Setiap anggota kelompok memakai database di laptopnya sendiri; perubahan data tidak otomatis masuk ke laptop anggota lain.
