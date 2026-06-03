# Aplikasi Simulasi Angsuran Motor Bekas (Mokas) SFI

Aplikasi Simulasi Angsuran Motor Bekas (Mokas) merupakan platform kalkulasi pembiayaan kendaraan bekas yang dikembangkan untuk mendukung proses simulasi kredit angsuran kendaraan untuk dealer mitra SFI. Aplikasi dibangun menggunakan PHP nativen dengan pendekatan service-oriented architecture untuk mempertahankan efisiensi akses database, mengoptimalkan query execution, serta stabilitas sistem kalkulasi finansial. Aplikasi ini mengintegrasikan validasi berbagai tipe pembiayaan, kalkulasi multi-stage pokok hutang, penentuan bunga, hingga pencatatan historis perhitungan simulasi oleh dealer ke pusat.

---

# 1. System Architecture

Seluruh komunikasi database dilakukan melalui Data Access Layer dengan pola Singleton. Arsitektur aplikasi menggunakan pendekatan Service-Oriented dengan pemisahan tanggung jawab antar layer :

| Layer              | Responsibility                                                         |
| ------------------ | ---------------------------------------------------------------------- |
| Presentation Layer | Rendering antarmuka dan komunikasi asynchronous melalui XMLHttpRequest |
| Session Layer      | Penyimpanan dan sinkronisasi state simulasi                            |
| Service Layer      | Implementasi business rules dan financial calculation                  |
| Data Access Layer  | Eksekusi Stored Procedure dan parameterized SQL                        |
| Persistence Layer  | Audit trail dan historical transaction recording                       |

## Database Connection Strategy

Untuk memastikan hanya satu koneksi aktif yang digunakan sepanjang lifecycle request, koneksi SQL Server dikelola melalui `Database::getInstance()`

### Database Configuration

| Property        | Value                     |
| --------------- | ------------------------- |
| Database        | SFI_DWH                   |
| DBMS            | Microsoft SQL Server      |
| Driver          | sqlsrv                    |
| Host            | 172.16.0.239              |
| Isolation Level | SQLSRV_TXN_READ_COMMITTED |

### Connection Resiliency

- Automatic reconnection
- Maximum retry: 3 attempts
- Adaptive retry interval: 2–10 detik

---

# 2. Data Access Layer & Query Security

Untuk menghindari query interpolation maupun dynamic SQL string construction. Seluruh akses database diimplementasikan melalui :

- Stored Procedure
- Prepared Statement
- Parameterized T-SQL

Data Access Layer juga menyediakan local in-memory caching untuk mengurangi frekuensi query terhadap Data Warehouse.

## Cache Strategy

Cache dikelola pada: `DatabaseService::$cache` dengan konfigurasi:

| Dataset             |      TTL |
| ------------------- | -------: |
| Default Query Cache |  300 sec |
| Historical MRP      |  600 sec |
| Discount Refund     | 1800 sec |

Note : Cache bersifat request-local dan digunakan untuk dataset dengan karakteristik read-heavy.

---

# 3. Database Objects & Stored Procedure Layer

## 3.1 Authentication Module

Autentikasi dealer dilakukan sepenuhnya pada database layer menggunakan `SP_LOGIN_CHECK_DEALER`.

| Parameter | Description            |
| --------- | ---------------------- |
| Username  | Dealer username        |
| Password  | MD5 encrypted password |

### Output Dataset

`SP_LOGIN_CHECK_DEALER` menghasilkan single-row dataset yang dipetakan ke session state :

| Session Variable | Description       |
| ---------------- | ----------------- |
| DEALER_ID        | Dealer identifier |
| DEALER_NAME      | Dealer name       |
| DEALER_CODE      | Dealer code       |
| AREA_NEW         | Regional mapping  |
| BRANCH           | Dealer branch     |

Note : Jika autentikasi gagal maka session tidak diinisialisasi.

---

## 3.2 Master Asset Extraction

Data master kendaraan diperoleh melalui table `Dashboard_Master_Asset`. Table ini membangun hierarki :

- Merk
- Model
- Type
- Unit Category
- Unit Segment

### Output Dataset

| Field              | Description      |
| ------------------ | ---------------- |
| UNIT_MERK_NAME     | Vehicle brand    |
| UNIT_MODEL_NAME    | Vehicle model    |
| UNIT_TYPE_NAME     | Vehicle type     |
| UNIT_CATEGORY_NAME | Vehicle category |
| UNIT_SEGMENT_NAME  | Vehicle segment  |

---

## 3.3 Maximum Retail Price (MRP)

MRP digunakan sebagai baseline pembiayaan dan validasi LTV melalui table `Dashboard_MRP_Asset`

### Query Parameters

| Parameter      | Description        |
| -------------- | ------------------ |
| UNIT_TYPE_NAME | Vehicle type       |
| UNIT_TAHUN     | Manufacturing year |
| AREA           | Regional filter    |

### Processing Logic

#### Head Office User

Jika session mendeteksi user account HO :

- Filter AREA diabaikan
- Query dilakukan secara nasional
- Nilai MRP tertinggi antar AREA dipilih sebagai MRP Standar.

#### Regular Dealer

Dealer reguler diisolasi berdasarkan kategori AREA dengan memperoleh MRP tertinggi dari seluruh area nasional.

### Output (HO Case)

| Variable     | Description                      |
| ------------ | -------------------------------- |
| mrpStandar   | Highest regional or national MRP |
| mrpPengajuan | Proposed financing MRP           |

---

## 3.4 Dealer Discount Refund

Batas refund dealer diambil dari tabel `M_AREA_DEALER_KHUSUS`

### Query Key

| Field     | Description       |
| --------- | ----------------- |
| DEALER_ID | Dealer identifier |

### Output Field

| Field           | Description            |
| --------------- | ---------------------- |
| DISCOUNT_REFUND | Refund deduction limit |

### Normalization Rule

Nilai : `DISCOUNT_REFUND < 0` dinormalisasi menjadi `0` guna mencegah negative deduction.

---

# 4. Financial Calculation Engine

Seluruh komputasi pembiayaan dipusatkan pada `CalculationService::calculate()`. Method ini bertindak sebagai deterministic calculation engine yang mentransformasikan FinancingCriteria dan FinancingDetails menjadi `CalculationResult`

---

# 5. Loan Validation & LTV Rules

Validasi minimum DP dilakukan berdasarkan regional mapping.

## Minimum DP Rules

### 25% Requirement

Berlaku untuk:

- KALIMANTAN
- IBT
- SULAWESI
- SUMBAGSEL
- SUMBAGUT&TENG

### 20% Requirement

Berlaku untuk:

- JABODETABEKSER
- JABAR
- JATENG
- JATIM

### Validation Formula

`Total DP >= mrpPengajuan × minimumDPPercent` (Jika tidak memenuhi threshold, proses kalkulasi dihentikan.)

---

# 6. Multi-Stage Financing Pipeline

## Stage 1 — Pokok Hutang 1 (PH1)

- Rumus : `PH1 = MRP Pengajuan − Total DP`
- Constraint: `PH1 > 0`. Jika tidak valid, kalkulasi dibatalkan.

---

## Stage 2 — Fidusia Cost

Biaya Fidusia :

| PH1 Range        |   Fidusia |
| ---------------- | --------: |
| ≤ 50.000.000     |   215.000 |
| ≤ 100.000.000    |   265.000 |
| ≤ 249.999.999    |   365.000 |
| ≤ 500.000.000    |   615.000 |
| ≤ 20.000.000.000 | 1.015.000 |

Konfigurasi disimpan pada `CalculationService::$fidusiaRanges`

---

## Stage 3 — Insurance Premium

Premi asuransi dihitung berdasarkan:

- Plafond Exposure
- Tenor
- Naximum insurance age limit (7 tahun)

Output dikumulatifkan sebagai `totalPremiAsuransi`

---

## Stage 4 — Pokok Hutang 2 (PH2)

- Rumus : `PH2 = PH1 + Admin Fee + Fidusia + Insurance Premium`
- Administrative fee : `Rp 6.000.000`

---

## Stage 5 — Pokok Hutang 3 (PH3)

- Biaya provisi dihitung menggunakan `ProvisiCalculator`
- Rumus : `PH3 = PH2 + Provisi`

---

## Stage 6 — Final Principal

- Life insurance dihitung menggunakan `LifeInsuranceCalculator`
- Rumus : `Total Pokok Hutang = PH3 + Life Insurance`
- Nilai ini akan digunakan sebagai Principal Financing Final.

---

# 7. Interest, Installment & Account Receivable

## Effective Rate Processing

Aplikasi menentukan EffectiveRateAkhr dan Final Flat Rate berdasarkan:

- Unit Segment
- Year
- Dealer ID
- Discount bunga
- Tipe angsuran

Mendukung tipe angsuran:

- ADDM
- ADDB

## Total Interest Formula

- Rumus : `Total Bunga = Final Flat Rate × (Tenor/12) × Total Pokok Hutang`

## Total Net AR

- Rumus : `Net AR = Total Pokok Hutang + Total Bunga`

## Monthly Installment

- Rumus : `Net AR / Tenor`
- Implementasi menggunakan thousand rounding `round(($netAR / $tenor) / 1000) * 1000` untuk menghasilkan nominal angsuran standar.

---

# 8. Dealer Financial Variables

## Refund

- Rumus : `Refund = (14% × Total Bunga) − Discount Refund`
- Constraint : `Refund >= 0`
- Negative result distandarisasi menjadi nol.

## Total Down Payment (TDP)

- Rumus ADDM : `TDP = DP + Angsuran`
- Rumus ADDB : `TDP = DP`

## Pelunasan Pokok

- Rumus : `Pelunasan Pokok = MRP Pengajuan − TDP`

## All-In

- Rumus : `All-In = Refund + PH1`
- Nilai ini merepresentasikan kontribusi pendapatan dealer terhadap pembiayaan.

---

# 9. Session Lifecycle & State Management

- State simulasi dikelola melalui `$_SESSION['provider']` yang menyimpan serialisasi `FinancingProvider`
- Session Lifecycle :

1. User melakukan update melalui DOM
2. XMLHttpRequest menangani update state
3. FinancingCriteria diperbarui
4. Action calculate dipanggil
5. CalculationService melakukan validasi dan komputasi
6. CalculationResult dibentuk

Note : Metode digunakan untuk mempertahankan continuity state tanpa dependency framework.

---

# 10. Persistence & Historical Recording

Jika `calculationStatus = true` hasil simulasi diteruskan menuju `DealerRecordService::recordSimulation()`. Service ini bertanggung jawab terhadap:

- Historical logging
- Dealer activity recording
- Central reporting integration
- Audit trail generation

Setiap perhitungan simulasi yang dilakukan akan dicatat sebagai historical financing transaction untuk monitoring dan reporting HO.

---

# Technology Stack

| Component      | Technology                             |
| -------------- | -------------------------------------- |
| Language       | PHP Native                             |
| Database       | Microsoft SQL Server                   |
| Driver         | sqlsrv                                 |
| Architecture   | Service-Oriented                       |
| Session        | PHP Session                            |
| Query Security | Parameterized Query + Stored Procedure |
| Communication  | XMLHttpRequest                         |
