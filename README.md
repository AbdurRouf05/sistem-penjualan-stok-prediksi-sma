# Sistem Informasi Penjualan, Stok & Pemesanan — Toko Aneka Camilan Dua Putra

Rancang bangun sistem informasi penjualan, pengelolaan stok dan pemesanan barang berbasis web dengan fitur **prediksi kebutuhan stok menggunakan metode Single Moving Average (SMA)**.

Dibangun dengan Laravel 10, Livewire 3 dan Bootstrap (CoreUI).

## Fitur

- **Penjualan & kasir (POS)**: transaksi, pembayaran, retur, cetak nota
- **Pengelolaan stok**: produk, kategori, satuan, penyesuaian stok, notifikasi stok menipis, pembelian ke supplier
- **Pemesanan online**: katalog, keranjang, checkout dan lacak pesanan untuk pelanggan, serta konfirmasi pesanan oleh admin (stok otomatis berkurang dan tercatat sebagai penjualan)
- **Prediksi kebutuhan stok (SMA)**: prediksi bulan berikutnya per produk, tabel perhitungan, MAD/MSE/MAPE, perbandingan nilai n, grafik aktual vs prediksi, rekomendasi restock
- **Laporan**: laba/rugi, penjualan, pembelian, pembayaran
- **Hak akses**: Pemilik (Super Admin) dan Kasir

## Instalasi

Kebutuhan: PHP 8.1–8.3 (ekstensi `pdo_sqlite`/`pdo_mysql`, `mbstring`, `intl`, `gd`, `zip`, `exif`, `fileinfo`), Composer, Node.js.

```bash
git clone https://github.com/AbdurRouf05/sistem-penjualan-stok-prediksi-sma.git
cd sistem-penjualan-stok-prediksi-sma
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
```

Atur database di `.env`. Untuk SQLite (paling mudah):

```
DB_CONNECTION=sqlite
```

lalu buat file kosong `database/database.sqlite`. Untuk MySQL, isi `DB_CONNECTION=mysql`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.

```bash
php artisan migrate --seed
php artisan serve
```

Buka <http://127.0.0.1:8000>. Toko online ada di `/toko`, panel admin di `/login`.

| Peran | Email | Password |
|---|---|---|
| Pemilik | `admin@duaputra.test` | `12345678` |
| Kasir | `kasir@duaputra.test` | `12345678` |

Seeder mengisi data contoh (produk camilan, riwayat penjualan ±18 bulan, pesanan online) supaya fitur prediksi langsung bisa dicoba.

Panduan penggunaan lengkap, rumus SMA dan lokasi kode penting ada di [PANDUAN.md](PANDUAN.md).

## Pengujian

```bash
php artisan test
```

## Kredit

Modul dasar penjualan, kasir dan stok dikembangkan dari [Triangle POS](https://github.com/FahimAnzamDip/triangle-pos) karya Fahim Anzam Dip (lisensi MIT).
