# Sistem Informasi Penjualan, Stok & Pemesanan — Toko Aneka Camilan Dua Putra

Aplikasi web berbasis **Laravel 10** dengan fitur:

| Fitur | Keterangan |
|---|---|
| Penjualan & Kasir (POS) | Transaksi penjualan, pembayaran, retur, cetak nota/PDF |
| Pengelolaan stok | Produk, kategori, satuan, penyesuaian stok, notifikasi stok menipis, pembelian ke supplier |
| **Pemesanan online** | Toko online untuk pelanggan (katalog, keranjang, checkout, lacak pesanan) dan pengelolaan pesanan oleh admin |
| **Prediksi kebutuhan stok (Single Moving Average)** | Prediksi bulan berikutnya per produk, tabel perhitungan, MAD/MSE/MAPE, perbandingan nilai n, grafik, rekomendasi restock |
| Laporan | Laba/rugi, penjualan, pembelian, pembayaran, retur |
| Hak akses | Role Super Admin (pemilik) dan Kasir, dapat diatur di menu Hak Akses |

Basis kode diambil dari proyek open source [Triangle POS](https://github.com/FahimAnzamDip/triangle-pos) (lisensi MIT), lalu dikembangkan dengan modul **Order** (pemesanan online) dan **Forecast** (prediksi SMA), antarmuka berbahasa Indonesia, dan format Rupiah.

---

## Menjalankan aplikasi

1. Klik dua kali **`jalankan.bat`**.
2. Browser terbuka di <http://127.0.0.1:8000>. Jangan tutup jendela hitam (server) selama aplikasi dipakai.

| Halaman | Alamat |
|---|---|
| Toko online (pelanggan) | <http://127.0.0.1:8000/toko> |
| Login admin/kasir | <http://127.0.0.1:8000/login> |

### Akun

| Peran | Email | Password |
|---|---|---|
| Pemilik (Super Admin) | `admin@duaputra.test` | `12345678` |
| Kasir | `kasir@duaputra.test` | `12345678` |

Ganti password lewat menu profil sebelum dipakai sungguhan.

### Data demo

Database sudah berisi data contoh: 15 produk camilan, 4 kategori, supplier, pelanggan, **riwayat penjualan April 2025 – sekarang** (lengkap dengan lonjakan saat Lebaran dan akhir tahun) dan 7 pesanan online. Untuk mengembalikan data demo ke kondisi awal, jalankan **`reset-data-demo.bat`**.

Saat sudah memakai data asli toko, hapus baris `$this->call(DemoDataSeeder::class);` di `database/seeders/DatabaseSeeder.php` sebelum reset.

---

## Alur penggunaan

### Pemesanan online
1. Pelanggan membuka `/toko`, memilih produk, lalu checkout (nama, HP, alamat, cara ambil, cara bayar).
2. Pelanggan mendapat **kode pesanan** (mis. `PSN-261013-ABCDE`) untuk dilacak di menu *Lacak Pesanan*.
3. Admin/kasir membuka menu **Pesanan Online**. Badge kuning menunjukkan jumlah pesanan baru.
4. Status pesanan: `Menunggu Konfirmasi → Diproses → Dikirim → Selesai`, atau `Dibatalkan`.
   - **Konfirmasi & Proses**: stok dicek dan dikurangi, pesanan dicatat sebagai **penjualan**. Data ini otomatis ikut dipakai dalam prediksi.
   - **Batalkan** setelah diproses: stok dikembalikan dan catatan penjualan dihapus.

### Prediksi kebutuhan stok (SMA)
Menu **Prediksi Stok (SMA)**:
- Parameter: **n** (2–6 bulan), **jumlah data** (6/12/18/24 bulan), dan **bulan terakhir data**. Bawaannya n = 3, 12 bulan, sampai bulan lalu.
- Halaman ringkasan: prediksi bulan berikutnya untuk semua produk, MAPE, stok saat ini, **rekomendasi restock** = prediksi − stok (minimal 0), dan status *Perlu Restock*/*Aman*.
- Tombol detail (ikon grafik) menampilkan:
  - tabel perhitungan per periode: X, F, X−F, |X−F|, (X−F)², persentase error;
  - MAD, MSE, MAPE dan kategori MAPE (Lewis, 1982);
  - perbandingan n = 2…6 dan n terbaik (MAPE terkecil);
  - grafik aktual vs prediksi.
- Tersedia tombol **Cetak** untuk lampiran laporan.

Rumus yang dipakai:

```
F(t+1) = (X(t) + X(t-1) + ... + X(t-n+1)) / n

MAD  = Σ|X − F| / jumlah periode uji
MSE  = Σ(X − F)² / jumlah periode uji
MAPE = Σ(|X − F| / X × 100%) / jumlah periode uji   (periode dengan X = 0 dilewati)
```

Data aktual (X) adalah **jumlah barang terjual per bulan** dari transaksi penjualan berstatus *Completed* (kasir, penjualan biasa, dan pesanan online yang sudah dikonfirmasi).

---

## Lokasi kode penting (untuk Bab 4 / lampiran)

| Bagian | File |
|---|---|
| Rumus SMA, MAD, MSE, MAPE | `Modules/Forecast/Services/SingleMovingAverage.php` |
| Pengambilan data penjualan per bulan | `Modules/Forecast/Services/SalesHistory.php` |
| Controller & tampilan prediksi | `Modules/Forecast/Http/Controllers/ForecastController.php`, `Modules/Forecast/Resources/views/` |
| Alur pesanan (stok & pencatatan penjualan) | `Modules/Order/Services/OrderService.php` |
| Toko online pelanggan | `Modules/Order/Http/Controllers/StoreController.php`, `Modules/Order/Resources/views/store/` |
| Pengelolaan pesanan admin | `Modules/Order/Http/Controllers/OrderController.php`, `Modules/Order/Resources/views/admin/` |
| Tabel `orders` & `order_details` | `Modules/Order/Database/Migrations/` |
| Data demo | `database/seeders/DemoDataSeeder.php` |
| Pengujian otomatis | `tests/Unit/SingleMovingAverageTest.php`, `tests/Feature/OrderAndForecastTest.php` |

Menjalankan pengujian otomatis (memakai database terpisah di memori, data aplikasi tidak terhapus):

```
D:\setup\php\php.exe artisan test
```

---

## Database: SQLite (bawaan) atau MySQL

Secara bawaan aplikasi memakai **SQLite** (`database/database.sqlite`), jadi tidak perlu menyalakan server database.

Untuk memakai **MySQL/MariaDB (XAMPP/Laragon)**:
1. Buat database kosong, mis. `dua_putra`.
2. Ubah `.env`:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=dua_putra
   DB_USERNAME=root
   DB_PASSWORD=
   ```
3. Jalankan `reset-data-demo.bat` untuk membuat tabel dan mengisi data.

## Menjalankan di komputer lain

Butuh PHP 8.1–8.3 dengan ekstensi `pdo_sqlite`/`pdo_mysql`, `mbstring`, `intl`, `gd`, `zip`, `exif`, `fileinfo`, `openssl`, `curl`. Laragon atau XAMPP versi PHP 8.2/8.3 sudah cukup. Salin folder `app` apa adanya (termasuk `vendor`), lalu jalankan:

```
php artisan serve
```

`jalankan.bat` memakai PHP di `D:\setup\php`, dan otomatis memakai `php` dari PATH bila folder itu tidak ada.
