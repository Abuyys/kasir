# Business Requirement Document (BRD)
## Sistem Informasi Penjualan & Stok (SIS-TOKO)

---

### Dokumen Kontrol

| Versi | Tanggal | Penulis | Deskripsi Perubahan |
| :--- | :--- | :--- | :--- |
| 1.0 | 7 Juni 2026 | Tim Pengembang Ilmu Komputer | Inisiasi Dokumen Awal |

---

## 1. Pendahuluan

### 1.1 Latar Belakang Bisnis
Banyak toko kecil, minimarket, dan cafe saat ini masih mengandalkan pencatatan transaksi dan stok secara manual atau menggunakan sistem yang terpisah (tidak terintegrasi). Pendekatan tradisional ini menimbulkan berbagai tantangan operasional, antara lain:
*   **Ketidakakuratan Data:** Kesalahan manusia dalam penghitungan stok fisik dan pencatatan transaksi manual.
*   **Selisih Stok:** Sulitnya melacak aliran keluar-masuk barang secara real-time yang memicu selisih stok (shrinkage).
*   **Antrean Panjang:** Waktu pelayanan transaksi per pelanggan menjadi lambat karena proses input manual.
*   **Ketiadaan Analisis:** Pemilik kesulitan melihat tren penjualan harian atau bulanan secara instan untuk pengambilan keputusan bisnis.

### 1.2 Tujuan Bisnis
*   **Efisiensi Transaksi:** Mempermudah dan mempercepat proses transaksi penjualan bagi kasir di titik penjualan (POS).
*   **Visibilitas Stok Real-Time:** Membantu pemilik toko memantau tingkat persediaan barang secara akurat kapan saja dan di mana saja.
*   **Peningkatan Layanan:** Mengurangi waktu tunggu pelanggan di kasir untuk meningkatkan kepuasan pelanggan.
*   **Akurasi Finansial & Inventaris:** Meminimalisasi selisih stok dan kesalahan pencatatan keuangan akibat human error.

### 1.3 Ruang Lingkup Bisnis
*   **Cakupan Sistem:**
    *   Modul Kasir (Penjualan / Point of Sale).
    *   Modul Manajemen Stok (Inventarisasi & Katalog Barang).
    *   Modul Laporan & Analitik Penjualan (Dashboard & Histori).
    *   Modul Manajemen Pengguna (Role-based access).
*   **Pengguna Utama (Aktor):**
    *   **Kasir:** Mengoperasikan modul transaksi penjualan di lapangan.
    *   **Pemilik Toko:** Mengelola data master, stok, melihat laporan, dan mengatur pengguna.
*   **Metode Akses:** Aplikasi berbasis web yang dapat diakses secara online melalui browser desktop maupun laptop.

---

## 2. Kebutuhan Bisnis (Business Requirements)

### 2.1 Kebutuhan Tingkat Tinggi (High-Level Requirements)

| ID | Deskripsi Kebutuhan | Prioritas | Target Pengguna |
| :--- | :--- | :--- | :--- |
| **B1** | Kasir harus dapat memproses transaksi penjualan dengan cepat dan mudah menggunakan input barcode maupun pencarian nama barang. | **High** | Kasir |
| **B2** | Pemilik toko harus dapat memantau stok barang secara real-time dan mendapatkan notifikasi otomatis saat stok berada di bawah batas minimum. | **High** | Pemilik Toko |
| **B3** | Sistem harus mempercepat proses check-out kasir guna mengurangi antrean pelanggan di toko. | **Medium** | Kasir / Pelanggan |
| **B4** | Pemilik toko harus dapat mengakses laporan penjualan berkala (harian, mingguan, bulanan) yang disajikan dalam bentuk tabel dan grafik visual. | **High** | Pemilik Toko |

### 2.2 Stakeholders & Analisis Pengguna

*   **Pemilik Toko (Owner):**
    *   *Kebutuhan:* Memerlukan kontrol penuh atas data barang, laporan pendapatan harian/bulanan, dan pengawasan stok real-time untuk menghindari kehabisan barang (*stockout*).
*   **Kasir (Cashier):**
    *   *Kebutuhan:* Memerlukan antarmuka yang bersih, intuitif, tombol yang cukup besar, dan navigasi yang cepat untuk menyelesaikan transaksi dalam hitungan detik.
*   **Pelanggan (Customer - Implisit):**
    *   *Kebutuhan:* Mengharapkan proses pembayaran yang cepat dan struk belanja yang jelas sebagai bukti transaksi.

---

## 3. Key Performance Indicators (KPI)

Untuk mengukur keberhasilan penerapan sistem SIS-TOKO ini, metrik berikut akan digunakan sebagai acuan:

1.  **Kecepatan Transaksi:** Rata-rata waktu transaksi dari pemindaian barang pertama hingga pencetakan struk adalah **< 1 menit per pelanggan**.
2.  **Akurasi Persediaan:** Tingkat akurasi data stok pada sistem dibandingkan dengan stok fisik (melalui stock opname) mencapai **> 98%**.
3.  **Reduksi Selisih Stok:** Menurunkan selisih stok bulanan (akibat kehilangan atau salah catat) minimal **50%** dibanding sebelum sistem diimplementasikan.

---

## 4. Batasan & Asumsi Bisnis

### 4.1 Batasan (Constraints)
*   Sistem dikembangkan murni berbasis web (*web-based*). Aplikasi mobile native tidak masuk dalam fase rilis awal.
*   Transaksi pembayaran di fase awal hanya mendukung pembayaran offline secara tunai (cash) atau pencatatan manual metode pembayaran non-tunai (belum terintegrasi payment gateway langsung).
*   Penyimpanan data menggunakan database relasional MariaDB yang dihosting secara cloud.

### 4.2 Asumsi (Assumptions)
*   Toko memiliki koneksi internet stabil (minimal koneksi seluler/Wi-Fi standar).
*   Perangkat keras kasir (PC/Laptop/Tablet) memiliki browser modern (Chrome/Firefox/Edge) dengan resolusi layar minimal 1366x768 piksel.
*   Sebelum sistem resmi digunakan untuk transaksi komersial, pemilik toko telah melakukan *stock opname* awal dan mengunggah data master barang ke sistem.
