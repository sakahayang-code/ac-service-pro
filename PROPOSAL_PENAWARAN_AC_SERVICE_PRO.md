# PROPOSAL PENAWARAN APLIKASI
## SISTEM INFORMASI MANAJEMEN SERVIS AIR CONDITIONER (AC) TERINTEGRASI
### AC SERVICE PRO — VERSION 1.0 FINAL PRODUCTION READY

---

**Nomor Dokumen:** 001/PBL-ACPRO/XI/2026  
**Tanggal Penawaran:** 15 November 2026  
**Sifat:** Rahasia & Eksklusif  
**Perihal:** Penawaran Penjualan Lisensi & Implementasi Software AC SERVICE PRO  

---

### IDENTITAS PENYEDIA / PENGEMBANG
- **Nama Penyedia:** Muhdir Abdillah, A.Md., Kom.
- **Keahlian:** Full-Stack Web & Mobile Application Engineer
- **Kontak Resmi (WhatsApp/Telp):** [085318766057](https://wa.me/6285318766057)
- **Status Produk:** Siap Digunakan Operasional Nyata (100% Production Ready, Bukan Mockup / Prototype)

---

## 1. RINGKASAN EKSEKUTIF (EXECUTIVE SUMMARY)

Bisnis jasa servis dan perawatan Air Conditioner (AC) modern dihadapkan pada tantangan operasional yang tinggi, seperti:
1. Sulitnya melacak status ribuan pesanan servis secara akurat (*Work Order lifecycle*).
2. Kesulitan memantau pergerakan teknisi di lapangan dan riwayat penanganan unit AC pelanggan.
3. Kebocoran stok sparepart (freon, pipa, kapasitor, kompresor) yang tidak tercatat secara otomatis saat servis.
4. Keterlambatan penagihan faktur dan pembukuan kas yang rentan salah hitung.
5. Kurangnya profesionalitas sistem di mata pelanggan retail maupun perusahaan/perkantoran.

Untuk mengatasi permasalahan tersebut, kami menawarkan **AC SERVICE PRO v1.0**, sebuah sistem informasi manajemen terintegrasi berbasis *Web Application + Progressive Web Application (PWA)* yang dibangun khusus untuk mengotomatisasi seluruh alur kerja usaha jasa servis AC dari hulu ke hilir.

Aplikasi ini telah selesai dibangun, teruji melalui **70 Automated System Tests (100% PASS)**, dan siap langsung diimplementasikan pada bisnis Anda.

---

## 2. SCREENSHOT TAMPILAN APLIKASI AKTUAL (LIVE PRODUCTION)

Berikut merupakan tangkapan layar asli (*authentic screenshot*) yang diambil langsung dari database dan sistem operasional **AC SERVICE PRO** yang sedang berjalan:

### 2.1 Dashboard Utama Manajemen (Tampilan Desktop / Laptop)
Dashboard terintegrasi menampilkan metrik performa usaha harian/bulanan, status operasional antrean SPK secara real-time, grafik tren pendapatan & pengeluaran 6 bulan, teknisi terbaik, dan peringatan stok minimum:

![Dashboard Utama Desktop AC Service Pro](/home/antigravity/.gemini/jetski/brain/v1/default/jetski_remote_agents_3p/684bddf4-b765-4dda-b6f5-0712ef01c38c/screenshot_actual_dashboard.png)

### 2.2 Manajemen Work Order (SPK Servis) & Penjadwalan
Tabel terpadu seluruh perintah kerja servis, 9 tahapan alur status pekerjaan, data unit AC & tipe freon, keluhan pelanggan, dan alokasi teknisi:

![Manajemen Work Order Servis AC Service Pro](/home/antigravity/.gemini/jetski/brain/v1/default/jetski_remote_agents_3p/684bddf4-b765-4dda-b6f5-0712ef01c38c/screenshot_actual_work_orders.png)

### 2.3 Antarmuka Smartphone (PWA Mobile Ready)
Tampilan mobile responsif khusus HP Android dengan tombol **Menu** aktif yang menampilkan seluruh kategori sistem layaknya tampilan laptop, serta modul tugas lapangan teknisi dengan navigasi peta GPS dan WhatsApp klien:

| Menu Navigasi Lengkap Smartphone | Operasional Teknisi Lapangan Smartphone |
|:---:|:---:|
| ![Menu Navigasi Mobile](/home/antigravity/.gemini/jetski/brain/v1/default/jetski_remote_agents_3p/684bddf4-b765-4dda-b6f5-0712ef01c38c/screenshot_actual_mobile_menu.png) | ![Teknisi Lapangan Mobile](/home/antigravity/.gemini/jetski/brain/v1/default/jetski_remote_agents_3p/684bddf4-b765-4dda-b6f5-0712ef01c38c/screenshot_actual_mobile_technician.png) |

---

## 3. CAKUPAN MODUL & FITUR UTAMA (13 MODUL TERLENGKAP)

Aplikasi **AC SERVICE PRO** mencakup 13 modul fungsional yang saling terhubung:

| No | Modul Sistem | Fitur Utama |
|---|---|---|
| **1** | **Dashboard Terpusat** | Real-time KPI (WO hari ini, selesai, berjalan, tertunda), performa teknisi, grafik omset bulanan, notifikasi stok minimum, pengingat servis rutin berkala. |
| **2** | **Manajemen Pelanggan** | Auto sequence kode (`CUST-0001`), database WhatsApp & alamat, kategori (Rumah, Kantor, Sekolah, Hotel, Pabrik), histori servis & kepemilikan unit AC. |
| **3** | **Manajemen Unit AC** | Multi-unit per pelanggan, pendataan merek, tipe PK (1/2 PK s.d. 5 PK), jenis freon (R22, R32, R410A), riwayat cuci AC, isi freon, dan perbaikan. |
| **4** | **Pemesanan & Work Order (SPK)** | Auto sequence `WO-YYYYMM-XXXX`, pemilihan unit & layanan, prioritas, tarif estimasi, cetak SPK fisik, penugasan banyak teknisi per pekerjaan. Status alur kerja: `BARU` $\rightarrow$ `DIKONFIRMASI` $\rightarrow$ `DIJADWALKAN` $\rightarrow$ `DALAM PERJALANAN` $\rightarrow$ `SEDANG DIKERJAKAN` $\rightarrow$ `SELESAI` $\rightarrow$ `LUNAS`. |
| **5** | **Manajemen Teknisi** | Profil teknisi, sertifikasi, jadwal kerja, absensi check-in/out, rekap omset pekerjaan, perhitungan komisi otomatis, dan penilaian pelanggan. |
| **6** | **Aplikasi Mobile Teknisi (PWA)** | Antarmuka mobile native di HP Android: melihat tugas aktif, navigasi peta, tombol WhatsApp ke pelanggan, checklist pemeriksaan unit, pengisian diagnosis, pengurangan stok material otomatis, dan tanda tangan digital (*signature pad*). |
| **7** | **Katalog Layanan & Tarif Dinamis** | Manajemen harga fleksibel (Cuci AC, Isi Freon, Pasang Baru, Bongkar, Ganti Sparepart) berdasarkan kapasitas PK dan freon tanpa perlu ubah kode program. |
| **8** | **Inventori & Kartu Stok Sparepart** | SKU barang, stok real-time, batas stok minimum, otomatis terpotong saat dipakai teknisi, mutasi masuk/keluar, pembelian barang (Purchase Order/PO), dan data pemasok/supplier. |
| **9** | **Kasir, Faktur & Pembayaran** | Terbit faktur otomatis (`INV-YYYYMM-XXXX`), pembayaran multi-metode (Tunai, Transfer BCA/Mandiri, QRIS), cetak faktur digital & PDF, pencatatan termin/DP, dan status piutang. |
| **10** | **Buku Kas & Pengeluaran** | Pencatatan arus kas operasional (bensin teknisi, alat servis, makan), mutasi antar-rekening bank, dan laporan saldo riil. |
| **11** | **Kontrak Perawatan Berkala** | Pengelolaan kontrak korporat/instansi tahunan, jadwal kunjungan servis rutin otomatis, dan auto-generate Work Order berkala. |
| **12** | **Portal Layanan Mandiri Pelanggan** | Pelanggan dapat login untuk memesan servis online, memantau posisi teknisi, melihat faktur tagihan, serta menyampaikan tiket komplain & garansi. |
| **13** | **Laporan Usaha & Analitik** | Laporan pendapatan harian/bulanan, laba rugi bersih (*Profit & Loss*), valuasi nilai stok inventori, produktivitas teknisi, serta export data ke CSV/Excel. |

---

## 4. KEUNGGULAN TEKNIS & EFISIENSI INFRASTRUKTUR

1. **Teknologi Ringan & Cepat (PHP 8.2+ Native + SQLite 3 WAL Mode)**:
   - Tidak memerlukan server database MySQL/PostgreSQL yang berat atau langganan VPS mahal.
   - Database SQLite tersimpan di direktori privat yang aman di luar *public root*, dengan proteksi *Busy Timeout* dan mekanisme backup otomatis 1 klik.
2. **Kompatibilitas Shared Hosting**:
   - Dapat dijalankan di hampir semua shared hosting cPanel murah di Indonesia dengan biaya server yang sangat hemat.
   - Mendukung instalasi di subfolder (misal: `domainanda.com/serviceac/`) maupun domain utama.
3. **Progressive Web App (PWA) & Offline Ready**:
   - Dapat dipasang (*install*) langsung ke layar utama Android layaknya aplikasi Play Store.
   - Dilengkapi *Service Worker* dan penyimpanan lokal *IndexedDB* untuk caching tugas teknisi saat jaringan seluler lemah di lokasi servis.
4. **Desain Khusus Smartphone + Desktop**:
   - Tampilan handphone dilengkapi navigasi bawah (*bottom navigation*) dan tombol **Menu** aktif yang menampilkan seluruh hierarki sistem.
   - Tampilan laptop menyajikan sidebar enterprise dengan dashboard visual lebar dan kartu analitik.

---

## 5. SKEMA HARGA & INVESTASI

Kami menawarkan sistem lengkap ini dengan harga investasi yang sangat terjangkau:

| Deskripsi Item | Rincian | Nilai Investasi |
|---|---|:---:|
| **Lisensi Penuh Aplikasi AC SERVICE PRO v1.0** | - Full Source Code Siap Pakai<br>- 13 Modul Terintegrasi<br>- Database Schema & Master Seed Data<br>- PWA Android Offline Asset<br>- Panduan Operasional Singkat | **Rp 2.000.000,-** |
| **TOTAL BIAYA LISENSI APLIKASI** | *(Dua Juta Rupiah)* | **Rp 2.000.000,-** |

> [!NOTE]
> **Catatan Biaya Domain & Hosting:**  
> Harga di atas **TIDAK TERMASUK** biaya sewa nama domain (.com / .id / .co.id) dan paket web hosting/cPanel. Pembelian domain dan hosting disediakan atau dibeli langsung oleh pihak Pembeli/Klien. Penyedia siap memberikan rekomendasi spesifikasi hosting terbaik dan membantu menghubungkannya.

---

## 6. MEKANISME & TERMIN PEMBAYARAN

Untuk menjamin kenyamanan dan keamanan transaksi kedua belah pihak, pembayaran dibagi menjadi **2 (dua) Termin**:

```
+--------------------------------------------------------------------------+
| TOTAL NILAI INVESTASI: Rp 2.000.000,-                                    |
+--------------------------------------------------------------------------+
|  TERMIN 1: 50% (Rp 1.000.000,-)                                          |
|  Dibayarkan setelah penandatanganan / persetujuan proposal penawaran     |
|  (Sebagai komitmen kesepakatan dan persiapan penyiapan environment).     |
+--------------------------------------------------------------------------+
|  TERMIN 2: 50% (Rp 1.000.000,-)                                          |
|  Dibayarkan setelah aplikasi berhasil terpasang dan beroperasi secara    |
|  LIVE PUBLIK di hosting klien.                                           |
+--------------------------------------------------------------------------+
```

### Rekening Pembayaran Resmi:
- **Nama Penerima:** Muhdir Abdillah
- **Nomor Rekening / Bank:** *(Akan dicantumkan pada invoice resmi / konfirmasi via WhatsApp)*
- **Konfirmasi Pembayaran:** WhatsApp ke **085318766057**

---

## 7. APA SAJA YANG DITERIMA PEMBELI (DELIVERABLES)

Setelah pelunasan Termin 2, Pembeli akan menerima:
1. **Full Paket Source Code Aplikasi** (AC SERVICE PRO v1.0, bebas watermark, siap digunakan seumur hidup tanpa biaya langganan bulanan software).
2. **Database SQLite Terkonfigurasi** beserta data awal (Master Akun Owner, Admin, Teknisi, Kasir, Gudang, CS, Pelanggan, dan Katalog Tarif Servis).
3. **Bantuan Instalasi & Deployment Hosting**: Penyedia akan membantu proses instalasi sampai aplikasi live 100% dan dapat diakses publik melalui browser HP dan komputer.
4. **Garansi Pemeliharaan Minor (30 Hari)**: Jaminan perbaikan jika ditemukan kendala teknis atau bug bawaan selama 30 hari pasca live publik.

---

## 8. LEMBAR PENGESAHAN & PERSETUJUAN PENAWARAN

Dengan menandatangani dokumen ini atau mengonfirmasi persetujuan melalui WhatsApp, kedua belah pihak menyatakan sepakat atas ruang lingkup pekerjaan, harga lisensi sebesar **Rp 2.000.000,-**, serta jadwal pembayaran 2 termin di atas.

Dibuat pada: **15 November 2026**

<br>

| PIHAK PENGEMBANG / PENJUAL | PIHAK PEMBELI / KLIEN |
|:---:|:---:|
| <br><br><br> | <br><br><br> |
| **MUHDIR ABDILLAH, A.Md., Kom.**<br>Pengembang Utama AC Service Pro<br>WA: 085318766057 | **(......................................................)**<br>Pemilik Usaha / Pimpinan Perusahaan<br>Tgl: .................................................... |

---

### Ingin Langsung Berkonsultasi atau Memproses Pemesanan?
Silakan hubungi kami langsung via WhatsApp untuk respon instan:  
👉 **[Klik Disini untuk Chat WhatsApp: 085318766057](https://wa.me/6285318766057?text=Halo%20Pak%20Muhdir%20Abdillah,%20saya%20berminat%20dengan%20Proposal%20Penawaran%20Aplikasi%20AC%20SERVICE%20PRO%20seharga%20Rp%202.000.000.)**
