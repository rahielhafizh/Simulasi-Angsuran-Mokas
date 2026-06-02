# Sistem Simulasi Angsuran Motor Bekas (Mokas)

---

## 1. Arsitektur Sistem & Data Access Layer

Sistem menggunakan pola _Singleton_ pada koneksi basis data (`Database` ) yang dienkapsulasi dengan mekanisme _retry_, _connection pooling_, dan _error logging_ dinamis untuk memastikan stabilitas koneksi ke Data Warehouse SFI (`SFI_DWH`).

- **Server Host:** `172.16.0.239`
- **Database:** `SFI_DWH`
- **Driver:** SQLSRV (isolasi transaksi `SQLSRV_TXN_READ_COMMITTED`)
- **Caching Strategy:** Sistem mengimplementasikan in-memory array cache (`DatabaseService`) dengan parameter Time-To-Live (TTL) (default 300 detik, 600 detik untuk data MRP).

## 2. Implementasi Stored Procedure & Skema Relasional

Sistem tidak menggunakan ORM, melainkan berinteraksi secara langsung melalui eksekusi _Stored Procedure_ dan _Parameterized T-SQL Queries_ untuk memitigasi risiko SQL Injection dan mengoptimalkan _query execution plan_.

### A. Authentication & Session Handling

Proses autentikasi _dealer_ dan _user_ dialihkan sepenuhnya ke dalam layer database menggunakan Stored Procedure.

- **SP Name:** `SP_LOGIN_CHECK_DEALER`
- **Parameter Input:** \* `@Username` (String)
- `@Password` (MD5 Hash)

- **Proses & Filter:** Mengevaluasi kredensial akses dan mencocokkan profil dealer/cabang.
- **Output Mapping:** Menghasilkan dataset _single-row_ yang diikat ke variabel sesi (`$_SESSION`):
- `DEALER_NAME`, `AREA_NEW`, `DEALER_CODE`, `BRANCH`, `DEALER_ID`.

### B. Master Asset Retrieval (`Dashboard_Master_Asset`)

Proses ekstraksi hierarki kendaraan terstruktur dalam tiga tingkat agregasi menggunakan kondisi fiter `LTRIM(RTRIM())` untuk pembersihan anomali spasi string.

- **Tabel Target:** `Dashboard_Master_Asset`
- **Dependency Alur:** `Brand (Merk)` -> `Model` -> `Type`.
- **Query Output:** `UNIT_MERK_NAME`, `UNIT_MODEL_NAME`, `UNIT_TYPE_NAME`, `UNIT_CATEGORY_NAME`, `UNIT_SEGMENT_NAME`.

### C. Maximum Retail Price (MRP) Extraction (`Dashboard_MRP_Asset`)

Pengambilan nilai taksiran standar kendaraan (MRP) dikendalikan melalui sistem hirarki _Area_.

- **Tabel Target:** `Dashboard_MRP_Asset`
- **Filter Logic:** Berdasarkan parameter `UNIT_TYPE_NAME`, `UNIT_TAHUN`.
- **Special Condition:**
  Jika sesi _user_ adalah 'HO' (_Head Office_), sistem mengabaikan filter spesifik `AREA` dan menjalankan agregasi pengelompokan (DISTINCT) untuk menarik seluruh data MRP dalam setiap area. Jika _user_ adalah cabang/dealer reguler, filter dibatasi strictly pada `AREA` terkait dengan _sorting_ `UNIT_MRP DESC`.

### D. Dealer Discount Calculation (`M_AREA_DEALER_KHUSUS`)

- **Query Tujuan:** Batasan redundansi diskon untuk entitas spesifik.
- **Input Parameter:** `DEALER_ID`.
- **Output:** Field `DISCOUNT_REFUND`. Melakukan normalisasi hasil, jika hasil komputasi _database_ < 0, program akan mereduksi nilainya secara absolut menjadi 0.

## 3. Finansial & Business Logic Engine (`CalculationService`)

Core kalkulasi angsuran yang terkoordinir secara terpusat. State management dari setiap request dijaga oleh `FinancingProvider` yang memvalidasi _request flow_ sebelum mentransfer beban proses ke `CalculationService`.

### A. Validasi LTV (Loan-to-Value) & Threshold Down Payment

Sistem mengeksekusi validasi persentase batas bawah _Down Payment_ (DP) yang bersifat dinamis berdasarkan parameter Geografis / `AREA_NEW`.

- **Zona 25% Threshold DP:** KALIMANTAN, IBT, SULAWESI, SUMBAGSEL, SUMBAGUT&TENG.
- **Zona 20% Threshold DP:** JABODETABEKSER, JABAR, JATENG, JATIM.

### B. Arsitektur Pokok Hutang (PH)

Perhitungan pendanaan dirancang secara _multi-stage pipeline_:

1. **Pokok Hutang 1 (PH1):**
   Murni `MRP Pengajuan - Total DP`.
   (Calculation failed jika <= 0).

2. **Biaya Fidusia:**
   Ditentukan melalui pemetaan statis multi-tier (Range: Rp 50.000.000 -> Rp 215.000; hingga limit teratas > Rp 500.000.000 -> Rp 1.015.000).

3. **Total Premi Asuransi:**
   Ditentukan secara terpisah sesuai dengan kategori dalam _Insurance Region_ (1-3), Tenor, dan Nilai OTR.

4. **Pokok Hutang 2 (PH2):**
   `PH1 + Biaya Administrasi Tetap (Rp 6.000.000) + Biaya Fidusia + Total Premi Asuransi`.

5. **Pokok Hutang 3 (PH3):**
   Menambahkan persentase Biaya Provisi ke dalam `PH2`.

6. **Total Pokok Hutang Final:**
   `PH3 + Biaya Life Insurance`.

### C. Komputasi Angsuran & Interest Rate

- **Base Target:**
  Kalkulasi persentase _Effective Rate Akhir_ berdasarkan variabel Tenor, Nego Bunga, Dealer ID, Kategori dan Segmen Unit, serta Tahun Unit.

- **Konversi Flat Rate:**
  _Effective Rate_ dikonversi secara matematis menjadi _Final Flat Rate_ untuk menyesuaikan metode komputasi ADDB (_Angsuran Dibayar di Belakang_) atau ADDM (_Angsuran Dibayar di Muka_).

- **Total Bunga:**
  `Final Flat Rate * (Tenor / 12) * Total Pokok Hutang Final`.

- **Angsuran per Bulan:**
  `(Total Pokok Hutang Final + Total Bunga) / Tenor`.
  Nilai difinalisasi dengan pembulatan ke skala ribuan terdekat (_Round to Nearest Thousand_).

### D. Kalkulasi Refund & Pelunasan

Struktur perhitungan insentif dealer (_Refund_) dengan algoritma:

1. **Refund Base:**
   `Didefinisikan bernilai tetap sebesar 14% dari `Total Bunga`.

2. **Net Refund:**
   ``Refund Base - Discount Refund (Data Ekstraksi Dealer)`.

3. **TDP (Total Down Payment):**

- Jika Tipe Angsuran = `ADDM`: `DP + Angsuran Pertama`.
- Jika Tipe Angsuran = `ADDB`: Mutlak bernilai `DP`.

## 4. State Management Lifecycle

1. **Inisialisasi (Index):**
   `FinancingProvider` diserialisasi dan dipertahankan dalam variabel `$_SESSION['provider']`.

2. **Mutasi AJAX:**
   Setiap elemen input pada DOM (Merk, DP, Tenor, Nego Bunga) memicu XHR _Post Request_, mengubah properti internal dari `FinancingCriteria` dan `FinancingDetails`.

3. **Kalkulasi Reaktif:**
   Aksi `calculate` akan mentrigger validasi _strict_ secara terpusat pada service calculation terkait kesiapan parameter.

4. **Data Persistensi:**
   Parameter perhitungan yang divalidasi dengan status akhir sukses (_CalculationStatus = true_) langsung dikirim menuju modul log transaksi `DealerRecordService::recordSimulation` untuk kebutuhan analitik operasional lanjutan.
