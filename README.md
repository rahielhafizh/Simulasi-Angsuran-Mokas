# Dokumentasi Arsitektur & Analisis Sistem Simulasi Angsuran Mokas

## Daftar Isi

1. [Konteks Bisnis dan Arsitektur Sistem](#1-konteks-bisnis-dan-arsitektur-sistem)
2. [Analisis Struktur Aplikasi dan Fungsi File](#2-analisis-struktur-aplikasi-dan-fungsi-file)
3. [Alur Eksekusi dan Data Flow Sistem](#3-alur-eksekusi-dan-data-flow-sistem)
4. [Mekanisme Kalkulasi dan Rumus Finansial](#4-mekanisme-kalkulasi-dan-rumus-finansial)
5. [Terminologi Multifinansial Terintegrasi](#5-terminologi-multifinansial-terintegrasi)

---

## 1. Konteks Bisnis dan Arsitektur Sistem

**Enterprise Financing Architecture**
Aplikasi ini berfungsi sebagai infrastruktur _point-of-sales_ yang menyediakan analis kredit dan perwakilan dealer di lapangan, memungkinkan mereka menghasilkan struktur pembiayaan yang presisi, tervalidasi, dan memiliki dasar hukum komersial secara _real-time_. Arsitektur sistem mengadopsi pola pemisahan _concern_ yang ketat antara lapisan presentasi (_User Interface_), manajemen _state_ (_Provider Layer_), mesin komputasi bisnis (_Service Layer_), dan interkoneksi data (_Data Layer_). Integrasi secara langsung dengan sistem Data Warehouse SQL Server (SFI_DWH) menjamin bahwa parameter krusial seperti _Market Retail Price_ (MRP), persentase asuransi OJK, dan matriks suku bunga regional selalu merujuk pada data referensi tunggal (_Single Source of Truth_), sehingga mengeliminasi risiko redundansi atau asimetri data di tingkat aplikasi klien.

**Risk Mitigation dan Data Integrity**
Ketahanan operasional sistem ini ditopang oleh serangkaian lapisan mitigasi risiko otomatis yang dirancang untuk menekan angka _Non-Performing Financing_ (NPF) sejak fase inisiasi. Aplikasi menerapkan validasi pertukaran data _backend-to-backend_ (B2B) untuk memeriksa riwayat agunan aset pada pangkalan data eksternal, mengeksekusi kalkulasi aktuaria asuransi yang berjenjang terhadap depresiasi nilai kendaraan, serta merekam seluruh percobaan simulasi ke dalam log audit (_audit trail_) yang masif. Desain ini secara efektif memastikan bahwa logika komputasi tertutup rapat di sisi peladen (_server-side_), mencegah manipulasi data dari sisi klien, serta menjaga integritas perhitungan rasio _Loan to Value_ (LTV) secara mutlak.

---

## 2. Analisis Struktur Aplikasi dan Fungsi File

### Root Directory (Core Controllers & Entry Points)

- **`index.php`**: Bertindak sebagai _Front Controller_ absolut yang mengorkestrasikan siklus hidup aplikasi. File ini menginisialisasi eksekusi, menangkap _request_ HTTP dan AJAX masuk, memvalidasi eksistensi sesi autentikasi pengguna secara ketat, serta memetakan perutean internal (_internal routing_). Pada tingkat operasional, `index.php` menyuntikkan dependensi ke dalam `FinancingProvider` dan mengelola pembaruan _state_ dinamis seperti _discount refund_ yang bersifat spesifik per dealer.
- **`login.php`**: Lapisan presentasi untuk gerbang autentikasi. File ini merender antarmuka pengisian kredensial dan dirancang untuk mengimplementasikan manajemen sesi berkeamanan tinggi dengan standar _strict cookie_, serta menampilkan umpan balik visual apabila terjadi penolakan akses dari modul verifikator.
- **`checklogin.php`**: Bertindak sebagai _Authentication Handler_ inti. Modul ini menerima _payload_ POST dari antarmuka login, melakukan komparasi _hash_ kredensial agen atau otoritas dealer terhadap tabel pengguna di pangkalan data relasional. Setelah proses verifikasi dinyatakan valid, file ini menerbitkan token sesi dan memberikan otorisasi akses navigasi menuju ruang kerja _dashboard_ simulasi utama.
- **`logout.php`**: Eksekutor prosedur _Session Termination_. Script ini memastikan seluruh variabel yang terikat pada memori peladen untuk pengguna terkait dihapus tanpa sisa, dan menginstruksikan peramban klien untuk membatalkan _cookie_ autentikasi. Hal ini mencegah pembajakan sesi (_session hijacking_) pada terminal dealer yang digunakan secara komunal.
- **`vehicle_check.php`**: Modul integrasi API yang beroperasi di belakang layar untuk melakukan pemeriksaan silang (_cross-checking_) nomor rangka dan nomor mesin ke sistem otoritas pihak ketiga (seperti RAPINDO) melalui protokol cURL. Implementasi teknisnya mencakup manajemen _timeout_ koneksi dan parsing _header authorization_, yang berfungsi memastikan bahwa aset kendaraan tidak sedang berada dalam status diagunkan oleh entitas multifinance lain.
- **`router.php`**: Berfungsi sebagai _Network Interceptor_ tingkat dasar. Komponen ini menyeleksi lalu lintas jaringan pasif dari peramban, seperti pemanggilan statis `.well-known` atau `favicon.ico`, dan meresponsnya secara langsung. Mekanisme ini krusial untuk mencegah pemborosan siklus komputasi _server_ dengan menghentikan eksekusi sebelum kernel utama aplikasi dimuat ke dalam memori.
- **`cacert.pem`**: Bundel repositori sertifikat otoritas kriptografi (CA Certificate Bundle). File ini secara esensial menyediakan rantai kepercayaan TLS/SSL yang dibutuhkan oleh komponen seperti `vehicle_check.php` saat melakukan panggilan _outbound request_, memastikan bahwa aliran pertukaran data B2B dienkripsi secara aman dan dilindungi dari intersepsi _Man-in-the-Middle_ (MITM) attack.

### Folder `/config` (Configuration Layer)

- **`config/database.php`**: Mengadopsi arsitektur _Singleton Design Pattern_ untuk membangun dan mempertahankan interkoneksi persisten dengan mesin SQL Server menggunakan ekstensi `sqlsrv`. Modul ini merekayasa parameter _Connection Pooling_, menerapkan level isolasi _Read Committed_, dan mengintegrasikan algoritma otomatis _retry connection_. Implementasi ini menjamin sistem tetap tangguh menangani lalu lintas kueri _heavy-duty_ dan tahan terhadap latensi jaringan operasional perusahaan.
- **`config/appConstants.php`**: Pusat penyimpanan nilai skalar absolut dan parameter _environment variables_. Berkas ini secara strategis mengisolasi nilai statis seperti ketetapan tarif nominal administrasi standar, tautan _endpoint_ eksternal, toleransi usia kendaraan maksimum, hingga parameter regionalisasi format angka. Pemisahan ini membebaskan modul _business logic_ dari keberadaan _hardcoded values_, sehingga pembaruan kebijakan komersial perusahaan dapat dieksekusi secara instan dan aman.
- **`config/appColors.php`**: Modul pembantu yang mendefinisikan pemetaan palet warna heksadesimal korporat. Sentralisasi ini diaplikasikan untuk menjaga konsistensi perenderan elemen _User Interface_ pada portal dealer serta memastikan keseragaman visual pada pembuatan _output_ pelaporan, seperti dokumen cetak PDF atau pengiriman templat surat elektronik.

### Folder `/models` (Data Transfer Objects & Entity)

- **`models/calculationResult.php`**: Representasi _Data Transfer Object_ (DTO) _immutable_ yang mengkapsulasi kompilasi paripurna dari seluruh matriks komputasi finansial. Objek kelas ini memecah dan menstrukturkan variabel tahapan hutang (Pokok Hutang 1 hingga Final), beban asuransi, provisi, _yield_ bunga rasional, hingga nilai angsuran absolut. Keberadaan file ini menjamin pertukaran data yang aman dan terstandardisasi dari lapisan _Service_ menuju _Presentation Layer_.
- **`models/financingCriteria.php`**: Kelas entitas yang membungkus, mendefinisikan, dan memvalidasi dimensi atribut kualitatif dari sesi simulasi yang diajukan dealer. Modul ini merekam spesifikasi teknis unit kendaraan, kategori asuransi, identitas wilayah operasional, serta batasan tenor bulan. Modul ini juga dilengkapi dengan mekanisme internal _checkValidation_ untuk mendeteksi anomali masukan kosong sebelum data dilempar ke mesin kalkulasi.
- **`models/financingDetails.php`**: Eksekutor validasi logis terhadap komponen kuantitatif transaksi finansial. File ini bertanggung jawab membersihkan dan memformat struktur masukan nilai nominal harga transaksi kesepakatan (OTR/MRP) serta formasi pendanaan awal atau _Down Payment_ (DP), mengonversi persentase menjadi fraksi desimal sistem untuk mencegah galat aritmatika di tahap lanjutan.

### Folder `/providers` (State & Orchestration)

- **`providers/financingProvider.php`**: Modul orkestrator sentral yang diarsiteki dengan konsep _Facade Pattern_. Kelas ini bertindak sebagai manajer _state_ sesi operasional yang menghubungkan interaktivitas formulir UI, repositori pangkalan data, dan modul eksekusi matematika. _Provider_ ini secara aktif meninjau kepatuhan nilai MRP pengajuan dealer, mengawasi toleransi selisih harga dibandingkan dengan indeks buku referensi wilayah, dan menginstruksikan modul kalkulasi untuk memproses _request_ berdasarkan model data yang telah disterilisasi.

### Folder `/services` (Business Logic & Core Engines)

- **`services/calculationService.php`**: Merupakan _Core Engine_ atau otak arsitektur utama yang menjalankan deret algoritma matematika pembiayaan secara hierarkis. Modul ini mengeksekusi pembentukan struktur piutang tahap demi tahap (secara sekuensial): mereduksi OTR dengan uang muka untuk mencari Pokok Hutang awal, menambahkan beban administrasi dan fidusia, mengkalkulasi persentase perlindungan asuransi (aset fisik dan jiwa), dan menghimpun biaya provisi untuk membentuk _Total Pokok Hutang Final_.
- **`services/effectiveRateService.php`**: Mesin komputasi aktuaria kompleks yang ditugaskan penuh untuk merekonstruksi parameter suku bunga perbankan dasar menjadi beban bunga operasional. Modul ini memuat logika persimpangan kondisional (_special rate rules_) yang diaktivasi berdasarkan umur objek agunan, sebelum akhirnya melakukan konversi algoritma nilai waktu uang (_Time Value of Money_) dari format Suku Bunga Efektif (skema anuitas) ke dalam format Suku Bunga Flat (tetap), dibedakan secara matematis antara metode pelunasan ADDB dan ADDM.
- **`services/premiInsurance.php`**: Kalkulator sistem manajemen risiko yang menetapkan beban tanggungan asuransi fisik kendaraan bermotor. Skrip ini mengimplementasikan logika bersarang (_nested logic loop_) untuk memproyeksikan depresiasi _Market Retail Price_ unit secara tahunan mengikuti tenor. Nilai susut tersebut lalu dikalikan dengan parameter _rate_ spesifik otoritas jasa keuangan berdasarkan matriks Zona Teritori (1, 2, atau 3) dan jenis proteksinya (Komprehensif, TLO, atau Kombinasi).
- **`services/lifeInsuranceCalculator.php`**: Penentu kelayakan dan nilai aktuaria perlindungan jiwa debitur. Layanan ini mengalkulasi probabilitas risiko mortalitas pemohon dengan membaca indeks persilangan matriks antara usia klien, panjang durasi kontrak pinjaman, dan kategori kendaraan (komersial atau konvensional). Persentase hasil diekstraksi dan dikalikan terhadap nilai basis Pokok Hutang 3 untuk membentuk biaya premi asuransi jiwa akhir.
- **`services/provisiCalculator.php`**: Penanggung jawab perhitungan tarif bea perizinan operasional pembukaan fasilitas kredit (_Provision Fee_). Sistem ini memuat aturan percabangan yang memisahkan klasifikasi kendaraan penumpang (_Passenger_) dengan kendaraan berat produktif (_Commercial_). Nilai persentase provisi tersebut mutlak dieksekusi sebagai faktor pengali terhadap nilai pinjaman fundamental (Pokok Hutang 1).
- **`services/databaseService.php`**: Lapisan abstraksi interaksi khusus (DAO) yang menerjemahkan instruksi sistem ke dalam kueri sintaks SQL menuju _Data Warehouse_ (SFI_DWH). Guna menghalau penurunan performa aplikasi akibat beban _query_ statis yang berulang (_redundant query processing_), file ini dilengkapi dengan _local in-memory array caching_ yang dikendalikan oleh _Time to Live_ (TTL), memastikan latensi respon yang cepat saat portal memuat daftar puluhan ribu referensi merek dan model kendaraan.
- **`services/dealerRecordService.php`**: Komponen infrastruktur telemetri dan log audit. Modul ini secara asinkron menangkap data setiap manipulasi simulasi dari terminal operasional dan menyimpannya secara persisten ke pangkalan data relasional melalui pemanggilan _Stored Procedure_ `SP_Dealer_Record`. Rekaman historis ini menjembatani aktivitas _front-end_ dengan kebutuhan analisis intelijen bisnis (BI), menyediakan metrik valid seperti rasio pengajuan yang berhasil versus ditolak berdasarkan anomali parameter LTV.
- **`services/localDataService.php`**: Entitas _Fallback Repository_ yang mendistribusikan berkas data luring (_offline_) atau residu _cache_ sekunder. Modul ini berfungsi menjamin ketersediaan akses data terbatas ketika jalur komunikasi sistem menuju peladen DWH SQL Server mengalami guncangan koneksi atau _timeout_, sehingga dealer tetap dapat melakukan rendering hierarki menu referensi form (merek/model).

### Folder `/utils` (Utility & Helper)

- **`utils/currencyFormatter.php`**: Modul pembantu (_helper string manipulation_) yang didedikasikan untuk sinkronisasi antarmuka masukan nilai mata uang. Kelas statis ini menangani konversi dua arah (_two-way normalization binding_): mengubah string visual format Rupiah ("Rp 12.500.000") menjadi bilangan _integer/float_ yang sah bagi _backend engine_ PHP, serta memulihkan data komputasi akhir kembali ke struktur desimal yang representatif untuk diinjeksikan ke _Document Object Model_ (DOM) klien.
- **`utils/dropdownOptions.php`**: Algoritma pelengkap yang mengeksekusi otomatisasi dekoding identitas demografis kendaraan. Modul ini memiliki kecerdasan logika untuk mengurai karakter awal kode pelat nomor pendaftaran kendaraan dan memetakannya secara presisi ke dalam struktur indeks Zona Asuransi OJK yang berlaku. Otomatisasi ini secara signifikan menurunkan risiko inkonsistensi atau _human error_ penginputan data asuransi oleh personel dealer di lapangan.

### Folder `/assets` (Presentation Layer & Client-Side Scripts)

- **`assets/js/financing-form.js`**: Lapisan penggerak dinamika antarmuka klien berbasis JavaScript. Skrip ini menjalin komunikasi asinkron (AJAX) dengan peladen, menangkap _event handler_ secara kontinu dari masukan pengguna, meregenerasi struktur _cascading dropdown_ seketika tanpa melakukan penyegaran halaman, serta bertanggung jawab penuh menyinkronkan komponen rasio UI, misalnya integrasi dinamis pergerakan _slider_ input terhadap perubahan numerik persentase DP.
- **`assets/views/` (Partials Global)**: Mencakup `header.php`, `styles.php`, `scripts.php`, dan `alert.php`. Fraksinasi berkas komponen HTML ini memisahkan deklarasi _meta-tag_, pemuatan gaya visual CSS, injeksi pustaka JavaScript, dan modul notifikasi _toast_ (respons sukses/gagal), memastikan struktur rangka kerangka halaman selalu di-maintain dengan mudah dan konsisten secara arsitektural.
- **`assets/views/` (Partials Form)**: Mencakup `form_kriteria.php`, `form_detail.php`, dan `financing-form.php`. Mengisolasi tata letak formulir menjadi fragmen modular. `form_kriteria.php` menyajikan input seleksi teknis aset, sementara `form_detail.php` mengelola masukan komersial spesifik (OTR dan nilai agunan). _Separation of concerns_ ini meminimalkan distorsi tata letak saat perenderan dinamis oleh PHP.
- **`assets/views/` (Partials Result)**: Terdiri atas `result_main.php` dan `result_detail.php`. Komponen antarmuka yang mengikat (binding) objek dari _CalculationResult_. `result_main.php` memberikan visualisasi absolut untuk variabel tagihan bulanan utama (cicilan), sedangkan `result_detail.php` bertugas mengurasi dekonstruksi transparan terkait komposisi struktur eskalasi pokok hutang dan alokasi premi asuransi menyerupai standar _print-out_ manifes sistem kredit perbankan korporat.
- **`assets/views/` (Partials Modal/Overlay)**: Terdiri atas `modal_mrp.php` dan `mrp_display.php`. Lapisan antarmuka fungsional _pop-up_ yang merekayasa penampakan nilai acuan buku _Market Retail Price_ (MRP). Elemen DOM ini menampilkan matriks evaluasi dan toleransi sistem terhadap batas pengajuan aktual pengguna dibandingkan catatan inventaris aset basis data perusahaan.

---

## 3. Alur Eksekusi dan Data Flow Sistem

**1. Interaksi Terminal & Request Initialization**
Operator dealer memasukkan spesifikasi data kendaraan serta parameter tenor operasional pada antarmuka pengguna (`form_kriteria.php` dan `form_detail.php`). Aktivitas _event listener_ pada komponen DOM ditangkap oleh mesin _front-end_ `financing-form.js`, yang kemudian menyusun kerangka _payload_ data, menjalankan sanitasi _string_ awal, dan menembakkan _asynchronous request_ (AJAX) secara terenkripsi menuju `index.php`.

**2. State Orchestration & Pengecekan Database**
`index.php` mendelegasikan otoritas _request_ kepada `FinancingProvider`. Sistem _orchestrator_ ini akan memicu _layer_ interaksi `DatabaseService` guna menarik rujukan nilai absolut MRP terkini dan batasan limit operasional kendaraan agunan yang disuplai secara interkoneksi _real-time_ dari _Data Warehouse_ SQL Server.

**3. Model Validation & Input Sanitization**
Parameter data masukan mentah dilewatkan menuju kelas entitas `FinancingCriteria` dan `FinancingDetails`. Sistem melakukan operasi pembersihan mendalam, di mana metode internal mengeksekusi rutinitas _currencyFormatter.php_ untuk menerjemahkan nilai teks Rupiah menjadi desimal murni (_float/integer_), sekaligus memastikan struktur kepatuhan _Loan to Value_ (LTV) dan batas nilai _Down Payment_ sesuai regulasi kredit yang legal.

**4. Tahapan Kalkulasi Finansial (Staged Computation)**
Model data bersih diinjeksi ke jantung perhitungan, `CalculationService`. Rangkaian komputasi linear pun dimulai: harga kendaraan agunan dipotong DP menjadi Pokok Hutang awal; biaya legal perizinan (administrasi dan fidusia) ditambahkan; kemudian diintegrasikan dengan kalkulasi modul `PremiInsurance` serta `LifeInsuranceCalculator` dan divalidasi dengan `ProvisiCalculator` untuk merampungkan formasi kompilasi akhir Pokok Hutang Final (_Base Loan Amount_).

**5. Restrukturisasi Beban Suku Bunga Aktuaria**
Basis nominal Pokok Hutang Final didelegasikan kepada `EffectiveRateService`. Mesin ini memilah algoritma antara sistem pelunasan ADDB (pembayaran belakang) dan ADDM (pembayaran dimuka), yang kemudian mentransformasikan _Effective Rate_ atau fungsi anuitas nilai waktu menjadi beban suku bunga rataan tetap (_Flat Rate_) secara matematis presisi per blok tagihan bulanan.

**6. Kompilasi Objek Data & Perekaman Telemetri**
_Engine Calculation_ lalu mendistribusikan semua variabel penyusun ke dalam sebuah kelas rekaan tidak bermutasi (_immutable DTO_) yang disebut `CalculationResult`. Bersamaan dengan pembentukan matriks nilai akhir ini, modul independen `DealerRecordService` akan mengemas spesifikasi transaksi simulasi dan mengeksekusinya ke pangkalan log riwayat (`SP_Dealer_Record`) di SQL Server, melengkapi persyaratan kewajiban regulasi _Audit Trail_ sistem perusahaan.

**7. UI Rendering & Output Presentation**
Pada langkah paripurna, representasi dari kelas komputasi yang merangkum hasil ini diserahkan kembali menuju _partial views_ (yakni `result_main.php` dan `result_detail.php`). Tumpukan skrip PHP merender format tata letak HTML terformat yang berisi dekonstruksi utang dengan akurasi persentase _real-time_ yang langsung tersaji di layar navigasi _dashboard_ dealer di titik transaksi.

---

## 4. Mekanisme Kalkulasi dan Rumus Finansial

**Pembentukan Pokok Hutang Berjenjang (Staged Financing)**
Alih-alih mengakumulasi piutang dalam eksekusi blok _single calculation_, struktur perhitungan didesain merundak (_staged_) untuk memberikan proporsi dasar perkalian yang tepat pada setiap komponen asuransi dan beban provisi.

- **Pokok Hutang 1 (PH1):** Diformulasikan dari entitas `Harga Kendaraan (OTR) - Down Payment (DP)`. Nilai ini mencerminkan ekuivalensi modal riil atau eksposur utang dasar yang secara fundamental diminta pemohon pembiayaan.
- **Pokok Hutang 2 (PH2):** Diformulasikan dari penambahan nilai `PH1 + Biaya Administrasi + Biaya Fidusia + Total Premi Asuransi Kendaraan`. Merupakan representasi akumulasi di mana korporat telah mengakomodir perlindungan aset investasi secara penuh atas risiko kerugian material serta biaya pengesahan akta otentik notaris ke dalam limit pinjaman nasabah.
- **Pokok Hutang 3 (PH3):** Diformulasikan dari integrasi nilai `PH2 + Biaya Provisi`. Komisi penyediaan dana perbankan (provisi) ini, yang umumnya berinduk dari representasi persentase PH1, diakuisisi ke dalam beban angsuran.
- **Total Pokok Hutang Final:** Diformulasikan dari penggabungan paripurna antara `PH3 + Total Premi Asuransi Jiwa`. Menghasilkan angka kumulatif yang dinamakan _Total Base Loan_, sebuah variabel sentral yang menjadi pengali tunggal terhadap indeks koefisien _Flat Rate_ suku bunga tahunan.

**Konversi Matematika Effective Rate Menjadi Flat Rate**
Kalkulasi merombak tarif persentase anuitas yang bergejolak setiap bulannya (Suku Bunga Efektif) menjadi suku bunga datar yang absolut (_Flat Rate_). Pendekatan _Present Value_ (PV) ini membelah logika penyesuaian sesuai metode pembayarannya:

- **Eksekusi ADDB (In Arrears):**
  Sistem menyusun pembagi penyusutan anuitas: $Denominator = 1 - (1 + Rate_{bulanan})^{-Tenor_{bulan}}$.
  Menghitung fraksi porsi beban per bulannya: $Numerator = \frac{Rate_{bulanan}}{Denominator} \times Tenor_{bulan}$.
  Dan meratanya dalam setahun proporsional tenor pinjaman: $Flat Rate = \frac{(Numerator - 1) \times 12}{Tenor_{bulan}}$.
- **Eksekusi ADDM (In Advance):**
  Logika aktuaria mengakomodasi ditariknya pembayaran pertama tanpa dikenakan beban porsi bunga berjalan (bulan nol): $Factor = \frac{\frac{Rate_{bulanan}}{Denominator}}{(1 + Rate_{bulanan})}$. Persentase _Flat Rate_ dihasilkan secara ekuivalen dari penyeimbangan persentase agregat anuitas tersebut dengan mendistribusikannya secara linear atas periode umur cicilan tersisa.

**Penetapan Piutang Konsumen (Net AR) dan Rincian Angsuran**
Puncak dari proses aktuaria adalah merumuskan beban kontrak final tagihan pembiayaan:

- **Total Beban Bunga (Margin Absolute):** Dikomputasikan dengan rumus: `Flat Rate × Tenor (dalam metrik tahun) × Total Pokok Hutang Final`.
- **Total Piutang Mutlak (Net Account Receivable):** Dikomputasikan dengan kalkulasi agregat absolut: `Total Pokok Hutang Final + Total Beban Bunga`.
- **Angsuran Bulanan (Installment):** Merupakan formula pembagian linear tunggal yaitu `Total Net AR ÷ Tenor (dalam metrik bulan)`.

**Konstruksi Struktur Pencairan (All-In Dealer Disbursement)**
Sistem menghitung besaran termin transfer riil atau batas _Disbursement_ tunai yang diserahkan korporat leasing ke rekening pihak internal manajemen dealer. Persamaannya adalah `(14% dari Total Bunga - Discount Refund Eksternal Dealer) + Pokok Hutang 1`. Algoritma ini secara otomatis menjalankan proses rekonsiliasi pengakuan rasio pendapatan di titik mula kredit diinisiasi.

---

## 5. Terminologi Multifinansial Terintegrasi

**Market Retail Price (MRP) / On The Road (OTR)**
OTR secara harfiah merupakan akumulasi harga akhir untuk pelepasan sebuah unit aset bermotor dari showroom purna jual kepada konsumen, di mana nilai tersebut sudah termasuk pemenuhan komponen biaya administrasi bea balik nama dan pajak legistimasi kepolisian. Dalam kerangka evaluasi operasional multifinance korporat, entitas _OTR Pengajuan_ senantiasa dikomparasikan dengan algoritma pembatas sistem yang merujuk kepada _Market Retail Price (MRP)_ pangkalan data. Praktik ini berfungsi sebagai langkah _risk-management hedging_ guna membendung kemungkinan di mana rasio pertanggungan agunan limit pinjaman atas plafon atau _Loan to Value_ (LTV) jauh melebihi ekspektasi harga buku fundamental yang riil di bursa lelang aset kendaraan bekas sekunder.

**Angsuran Dibayar Di Belakang (ADDB) dan Angsuran Dibayar Di Muka (ADDM)**
Skema strukturisasi linimasa fundamental yang memandu implementasi validasi rekonsiliasi pengakuan tagihan dan eksekusi uang muka kas. _ADDB (In Arrears)_ mendelegasikan siklus bayar bulanan perdana (_first installment run_) pada satu bulan berjalan paska ditandatanganinya validasi pelunasan fasilitas limit. Oleh karenanya, struktur tunai pada hari inisiasi semata hanya menanggung bobot agregat nilai unit awal dan persentase perlindungan tanggungan. Di sisi kontradiktif, _ADDM (In Advance)_ menggeser jadwal bayar satu perhentian waktu ke arah pendaftaran inisiasi. Konsekuensinya, total nilai kas konsumen saat transaksi tunai awal wajib mengakomodir nominal kewajiban angsuran cicilan ke-1 bersama uang muka unit dasar.

**Fidusia dan Biaya Administrasi Ekstra**
Fidusia adalah nomenklatur biaya pembebanan regulasi persetujuan administrasi pendaftaran akta penjaminan otentik notariil di kementerian atau lembaga pemerintah. Biaya pendanaan ini secara sah mendeskripsikan secara yuridis pelimpahan mandat kuasa kepemilikan aset agunan kendaraan dari pihak konsumen peminjam kepada penguasa penyedia korporat leasing (_kreditur_). Secara fungsional, ini menyerahkan status otoritas penahanan bilamana terjadinya peristiwa kemacetan hutang sistematis (_Default Case_). _Biaya Administrasi_ mewakili penagihan pengeluaran internal organisasi berbentuk beban statis, termasuk ongkos perlengkapan verifikasi perjanjian berkas secara konvensional, pajak legalisasi materiil surat perikatan, maupun alokasi manajemen telekomunikasi limit nomor virtual pelanggan di dalam pusat _backend_.

**Provisi (Provision Fee)**
Pembebanan tagihan persentase komisi seketika (_Upfront Origination Commission_) yang ditagih dari nasabah melalui fasilitas kredit berjalan. Nilai ekstraktif ini dipandang oleh pihak institusi pengelola dana multifinance sebagai substitusi pemenuhan beban imbal operasional penyediaan instrumen validasi awal lapangan survei profil risiko klien. Tarif koefisien besaran tagihan provisi, sebagaimana dihitung via sistem ini, memiliki karakteristik kalkulasi berbasis limit _Pokok Hutang Awal (PH1)_ yang difraksionasi sesuai hierarki mitigasi jenis armada agunannya; rasio persentase yang disematkan memisahkan tarif antara aset _Light Vehicle / Passenger_ yang bersifat konsumtif, dibandingkan _Heavy Duty / Commercial_ pickup operasional.

**Manajemen Asuransi Unit Eksternal: TLO, Comprehensive, dan Metode Combi**
Kategori mitigasi alokasi tingkat profil asuransi pendanaan pihak asuradur (_Insurance Loading Policy_). _Total Loss Only (TLO)_ menyediakan perlindungan ganti rugi minimalis yang diaktivasi eksklusif hanya untuk menanggung insiden klaim hilangnya aset curanmor atau destruksi absolut rangka struktur unit, yang menyebabkan susut buku bernilai destruksi depresiasi hingga rentang melebihi 75%. _Comprehensive Coverage_ mengakomodasi rasio klaim pengeluaran maksimal operasional segala wujud bentuk deformasi dari sekadar cacat material parsial hingga pertanggungan risiko fatal. Sementara itu, instrumen _Combi Hybrid (Kombinasi)_ adalah mekanisme rekursif penyusutan batas aktuaria terencana, di mana sistem menagih premi jaminan asuransi _Comprehensive_ secara ekstensif semata-mata pada kontrak 12 bulan usia berjalan, kemudian memigrasikannya secara radikal menjadi format status rasio dasar regulasi _TLO_ per siklus kontrak untuk rentang waktu sisa tahunan pembiayaannya.

**Booking Fee (Uang Tanda Jadi)**
Representasi termin dana tunai retensi yang diakui dan dilegalisasikan oleh agen koordinator eksternal (_dealership front desk_) saat inisiasi pendirian dokumen persetujuan unit _Surat Pesanan Kendaraan (SPK)_. Pendanaan pragmatis ini diserahkan pembeli dengan itikad menjaga validasi posisi hak milik ketersediaan barang inventaris supaya luput dari rotasi kuota ke tangan pembeli lain selama masa persetujuan persidangan dokumen dari divisi verifikasi penyedia dana sedang dilakukan. Secara mekanik algoritmik paska tahap konklusi pengajuan valid, besaran dari biaya reservasi ditarik secara inheren sebagai instrumen subsidi silang (pengurang rasio selisih kas riil) atas nilai kewajiban setoran deposit _Down Payment Final_ yang wajib dieksekusi pemohon ke terminal kas korporat operasional.

**Pokok Hutang Dasar (Principal Base Amount)**
Identifikasi terminologi akuntansi finansial bagi struktur volume _Principal_ (nomianal angka hutang eksak sejati). Nilai pokok dasar hutang menunjukkan skala limit agregat hutang tunggakan pendanaan pinjaman investasi korporasi riil (fasilitas likuiditas pendanaan), tanpa melibatkan dan melepaskan beban penambahan komputasi konversi struktur bunga tagihan margin tahunan (_yield/interest percentage load_). Peran fungsional _Pokok Hutang Dasar_ sangat mutlak karena nominal ini bertindak selaku variabel absolut pengali persentase dasar penetapan komputasi utilitas turunan lainnya pada lapisan perangkat peladen (contoh: kalkulator penyusutan indeks pendaftaran perlindungan jaminan asuransi aset serta perlindungan risiko kredit kesehatan personal jiwa nasabah per siklus tenor penagihannya).

**Margin atau Net Interest Yield**
Besaran variabel nilai yang merupakan proyeksi pengembalian kelayakan kapital dan keuntungan fiskal finansial untuk perusahaan fasilitator kredit (operator leasing) sehubungan layanan pengucuran rasio pinjaman limit. Indeks batas _Margin_ ini diputuskan pada ambang waktu operasional dengan mengakomodir toleransi defisit nilai mitigasi insiden kolektibilitas kredit berisiko. Secara struktur pencatatan manajemen sistem, tingkat batas persentase komersial ini perlahan diakui sebagai rasio pengakuan pendapatan berkala pada setiap siklus nasabah berhasil membayarkan kwitansi cicilan (_Income realization cycle recognized over amortized term duration_) dan terbentuk murni berlandaskan perbandingan kalkulasi diferensial beban ekuivalen _flat rate_ nasabah dan batas penyediaan rasio alokasi pendanaan dasar internal (_Funding source cost interest point_).

**Mekanisme Kontrak Refinancing**
Instrumen layanan rekondisi limit atau fasilitas injeksi likuiditas kas tunai korporasi di mana objek perikatan penjaminan berupah agunan BPKB sah aset bergerak yang nilainya telah dinyatakan lunas oleh kreditur nasabah (pelunasan eksisting). Pemohon menyetorkan validasi akta aset tersebut guna dievaluasi pada fasilitas kontrak injeksi pinjaman finansial konsumtif sekunder yang baru. Mengacu pada mekanisme model piranti komputasi yang berlaku, aplikasi operasional pembiayaan mengeksekusi parameter penyaluran pendanaan tidak kepada ekuitas eksternal pihak ketiga (seperti transaksi pembayaran ke akun pembelian aset dealer), namun menyetujui manuver pencairan _Refund Cash Loan Installment_ absolut dan eksklusif yang memusatkan transfer arus modal _Disbursement All-In_ itu langsung mengarah kembali masuk kepada akun perbankan pengguna nasabah peminjam secara akurat setelah melampaui analisis kesesuaian nilai pertanggungan pangkalan _market retail price evaluation_.

**Residual Value Asset**
Skenario metode mitigasi kalkulasi proyeksi rasio taksiran valuasi penyusutan yang diolah langsung oleh penilai asuradur (_actuarial valuer valuation limit_) guna merumuskan ekspektasi besaran _Depreciation Rate_ nilai ekonomi unit armada agunan yang dijaminkan. Penetapan ini mewakili rasio persentase sisa nilai akhir prediksi paska terlewatinya kontrak perjanjian masa fasilitas sewa pendanaan (_term span end stage_). Secara kerangka logika komputasi struktur asuransi, siklus pemrosesan algoritma pendaftaran harga pertanggungan _Total Loss Only_ (TLO) per tahap kalender dalam internal modul _service parameter_ wajib mengeksekusi referensi skala hitung penurunan pengali harga basis limit _Residual Value_ (untuk mengukur penurunan fisis aktual indeks persentase dasar aset referensi panduan regulasi Otoritas Jasa Keuangan dari fase pendirian perjanjian menuju fase purna kontrak penutupan) supaya batasan mitigasi pengeluaran dana ganti rugi oleh _finance principal_ atas klaim penjaminan objek tidak mengalami ekskalasi defisit fatal dibandingkan dengan indeks valuasi realisasi lelang penjualan _salvage_ aset aktual jaminan eksternal.
