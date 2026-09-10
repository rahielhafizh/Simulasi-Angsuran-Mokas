<!-- financingForm.php -->
<?php
use DropdownOptions;
use CurrencyFormatter;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulasi Mobil Bekas</title>
    <?php require __DIR__ . '/styles.php'; ?>
</head>

<body>
    <?php
    $areaNewRaw = $_SESSION['area_new'] ?? null;
    $areaNew = strtoupper(trim($areaNewRaw ?? ''));
    // HIGHER MINIMUM DP PERCENTAGE FOR COMPANY POLICY
    $nonJawaAreas = ['KALIMANTAN', 'IBT', 'SULAWESI', 'SUMBAGSEL', 'SUMBAGUT&TENG'];
    $minDpPercentLabel = in_array($areaNew, $nonJawaAreas, true) ? '25%' : '20%';
    ?>

    <div class="container">
        <!-- HEADER SECTION -->
        <div class="header">
            <h1>Simulasi Mobil Bekas</h1>
            <div class="header-bottom">
                <div class="dealer-info">
                    <div class="dealer-name">
                        <?php echo htmlspecialchars($_SESSION['dealer_name'] ?? 'Nama Dealer', ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <div class="dealer-area">
                        <?php echo htmlspecialchars($_SESSION['area_new'] ?? 'Nama Area', ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                </div>
                <div class="menu-container">
                    <button class="menu-button" id="menuButton" onclick="toggleMenu()">
                        <div class="menu-icon"><span></span><span></span><span></span></div>
                    </button>
                    <div class="dropdown-menu" id="dropdownMenu">
                        <a href="logout.php" class="dropdown-item logout">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- SEARCH SECTION -->
        <div class="vehicle-check-section">
            <h2 class="vehicle-check-title">Cek Kendaraan</h2>
            <div class="vehicle-check-form">
                <input type="text" id="vehicleFrameInput" class="vehicle-check-input"
                    placeholder="Masukkan Nomor Rangka Kendaraan" maxlength="30" autocomplete="off" />
                <button type="button" id="vehicleCheckBtn" class="vehicle-check-btn"
                    onclick="searchVehicle()">Cari</button>
            </div>
            <div id="vehicleCheckResult" class="vehicle-check-result" style="display: none;"></div>
        </div>

        <!-- ALERT SECTION -->
        <div class="alert" id="error-alert"
            style="display: <?php echo $provider->errorValidation ? 'block' : 'none'; ?>;">
            <span
                id="error-message"><?php echo htmlspecialchars($provider->errorValidation ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
        </div>

        <!-- KRITERIA PEMBIAYAAN KENDARAAN -->
        <div class="form-section">
            <h2>KRITERIA PEMBIAYAAN KENDARAAN</h2>
            <div class="form-group">
                <label>TYPE ANGSURAN</label>
                <select id="type-angsuran" onchange="updateField('update_type_angsuran', this.value)">
                    <option value="">Pilih Type Angsuran</option>
                    <?php foreach (DropdownOptions::$typeAngsuran as $option): ?>
                        <option value="<?php echo htmlspecialchars($option, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $provider->finalCriteria->typeAngsuran === $option ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($option, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>PLAT NOMOR</label>
                <select id="kode-plat" onchange="updateField('update_kode_plat', this.value)">
                    <option value="">Pilih Kode Plat Nomor</option>
                    <?php foreach (DropdownOptions::$kodePlat as $option): ?>
                        <option value="<?php echo htmlspecialchars($option, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $provider->finalCriteria->kodePlat === $option ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($option, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>MERK</label>
                <select id="merk" onchange="updateMerk(this.value)">
                    <option value="">Pilih Merk Kendaraan</option>
                    <?php foreach ($brands as $brand): ?>
                        <option value="<?php echo htmlspecialchars($brand, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $provider->finalCriteria->merk === $brand ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($brand, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>MODEL</label>
                <select id="model" onchange="updateModel(this.value)" <?php echo empty($models) ? 'disabled' : ''; ?>>
                    <option value="">Pilih Model Kendaraan</option>
                    <?php foreach ($models as $model): ?>
                        <option value="<?php echo htmlspecialchars($model, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $provider->finalCriteria->model === $model ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($model, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>TYPE</label>
                <select id="type" onchange="updateType(this.value)" <?php echo empty($types) ? 'disabled' : ''; ?>>
                    <option value="">Pilih Type Kendaraan</option>
                    <?php foreach ($types as $type): ?>
                        <option value="<?php echo htmlspecialchars($type, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $provider->finalCriteria->type === $type ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($type, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>TAHUN</label>
                <select id="tahun" onchange="updateTahun(this.value)">
                    <option value="">Pilih Tahun Kendaraan</option>
                    <?php foreach (DropdownOptions::getTahunOptions() as $tahun): ?>
                        <option value="<?php echo htmlspecialchars($tahun, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $provider->finalCriteria->tahun === $tahun ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($tahun, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>TENOR</label>
                    <select id="tenor" onchange="updateField('update_tenor', this.value)">
                        <option value="">Pilih Tenor</option>
                        <?php foreach (DropdownOptions::$tenor as $option): ?>
                            <option value="<?php echo htmlspecialchars($option, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $provider->finalCriteria->tenor === $option ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($option, ENT_QUOTES, 'UTF-8'); ?> Bulan
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>NEGO BUNGA</label>
                    <select id="nego-bunga" onchange="updateField('update_nego_bunga', this.value)">
                        <?php foreach (DropdownOptions::$negoBunga as $option): ?>
                            <option value="<?php echo htmlspecialchars($option, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $provider->finalCriteria->negoBunga === $option ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($option, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>TIPE ASURANSI</label>
                    <select id="asuransi-unit" onchange="updateField('update_asuransi_unit', this.value)">
                        <option value="">Pilih</option>
                        <?php foreach (DropdownOptions::$asuransiUnit as $option): ?>
                            <option value="<?php echo htmlspecialchars($option, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $provider->finalCriteria->asuransiUnit === $option ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($option, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>TIPE UNIT</label>
                    <select id="pass-comm" onchange="updateField('update_pass_comm', this.value)">
                        <?php foreach (DropdownOptions::$passComm as $option): ?>
                            <option value="<?php echo htmlspecialchars($option, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $provider->finalCriteria->passComm === $option ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($option, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- DETAIL PENGAJUAN PEMBIAYAAN -->
        <div class="form-section">
            <h2>DETAIL PENGAJUAN PEMBIAYAAN</h2>

            <?php
            if ($provider->mrpStandarMessage):
                ?>
                <div class="mrp-standar-display unavailable" id="mrp-standar-display">
                    <label>MRP STANDAR</label>
                    <div class="value" id="mrp-standar-value">
                        <?php echo htmlspecialchars($provider->mrpStandarMessage, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                </div>
            <?php elseif ($isHOUser && !empty($mrpByAreaFormatted)): ?>
                <div class="mrp-list-container" id="mrp-list-container">
                    <h3>MRP STANDAR PER AREA</h3>
                    <div id="mrp-area-list-inline">
                        <?php foreach ($mrpByAreaFormatted as $area => $mrpValue): ?>
                            <?php $isUnavailable = ($mrpValue === 'Tidak tersedia'); ?>
                            <div class="mrp-area-item <?php echo $isUnavailable ? 'unavailable' : ''; ?>">
                                <div class="mrp-area-name">MRP <?php echo htmlspecialchars($area, ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="mrp-area-value"><?php echo htmlspecialchars($mrpValue, ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php elseif ($provider->mrpStandar > 0): ?>
                <div class="mrp-standar-display" id="mrp-standar-display">
                    <label>MRP STANDAR</label>
                    <div class="value" id="mrp-standar-value">
                        <?php echo CurrencyFormatter::formatWithSymbol($provider->mrpStandar); ?>
                        <?php if ($hasMultipleArea): ?>
                            <button type="button" class="btn-detail-mrp" id="btn-detail-mrp"
                                onclick="showMRPDetailModal()">Detail MRP</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="mrp-standar-display" id="mrp-standar-display" style="display: none;">
                    <label>MRP STANDAR</label>
                    <div class="value" id="mrp-standar-value"></div>
                </div>
            <?php endif; ?>

            <?php if (!$isHOUser && $mrpDifferencePercentage && $mrpDifferenceNominal): ?>
                <div class="selisih-mrp-display <?php echo strpos($mrpDifferencePercentage, '+') === 0 ? 'positive' : 'negative'; ?>"
                    id="selisih-mrp-display">
                    <label>SELISIH MRP</label>
                    <div class="value-group">
                        <div class="percentage" id="selisih-percentage">
                            <?php echo htmlspecialchars($mrpDifferencePercentage, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                        <div class="nominal" id="selisih-nominal">
                            <?php echo htmlspecialchars($mrpDifferenceNominal, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="selisih-mrp-display" id="selisih-mrp-display" style="display: none;">
                    <label>SELISIH MRP</label>
                    <div class="value-group">
                        <div class="percentage" id="selisih-percentage"></div>
                        <div class="nominal" id="selisih-nominal"></div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label>OTR PENGAJUAN</label>
                <input type="text" id="mrp-value"
                    value="<?php echo $provider->finalDetails->mrpPengajuan > 0 ? CurrencyFormatter::formatWithoutSymbol($provider->finalDetails->mrpPengajuan) : ''; ?>"
                    placeholder="Masukkan Nominal MRP" onkeyup="formatCurrency(this)" onblur="submitMRP(this.value)">
            </div>

            <div class="form-group">
                <label>DP</label>
                <div class="dp-wrapper">
                    <div class="dp-percentage" id="dp-percentage"
                        style="display: <?php echo $dpPercentage ? 'flex' : 'none'; ?>;">
                        <?php echo htmlspecialchars($dpPercentage ?? '', ENT_QUOTES, 'UTF-8'); ?>
                    </div>

                    <input type="text" id="dp-value"
                        value="<?php echo $provider->finalDetails->dp > 0 ? CurrencyFormatter::formatWithoutSymbol($provider->finalDetails->dp) : ''; ?>"
                        placeholder="Masukkan Nominal DP (Min. <?php echo htmlspecialchars($minDpPercentLabel, ENT_QUOTES, 'UTF-8'); ?>)"
                        onkeyup="formatCurrency(this)" onblur="submitDP(this.value)">
                </div>
            </div>

            <div class="tdp-display">
                <label>TDP</label>
                <div class="value" id="tdp-value">
                    <?php
                    if ($provider->calculationStatus && $provider->finalResult->tdp > 0) {
                        echo CurrencyFormatter::formatWithSymbol($provider->finalResult->tdp);
                    } elseif ($provider->finalDetails->dp > 0) {
                        echo CurrencyFormatter::formatWithSymbol($provider->finalDetails->dp);
                    } else {
                        echo 'Rp 0';
                    }
                    ?>
                </div>
            </div>

            <div class="btn-actions">
                <button type="button" class="btn btn-secondary" onclick="resetForm()">Reset</button>
                <button type="button" class="btn btn-primary" id="calculate-btn" onclick="calculateFinancing()" <?php echo !$provider->canCalculate() ? 'disabled' : ''; ?>>
                    Hitung
                </button>
            </div>
        </div>

        <!-- RESULT -->
        <div class="result-section" id="result-section"
            style="display: <?php echo $provider->calculationStatus ? 'block' : 'none'; ?>;">
            <h2>Hasil Simulasi Pembiayaan</h2>

            <div class="result-card">
                <label>ANGSURAN/BULAN</label>
                <div class="value" id="angsuran-value">
                    <?php echo CurrencyFormatter::formatWithSymbol($provider->finalResult->angsuranPerBulan); ?>
                </div>
            </div>

            <div class="result-card">
                <label>ALL IN</label>
                <div class="value" id="all-in-value">
                    <?php echo CurrencyFormatter::formatWithSymbol($provider->finalResult->allIn); ?>
                </div>
            </div>

            <?php if ($shouldShowExtendedResults): ?>
                <div class="result-card">
                    <label>REFUND</label>
                    <div class="value" id="refund-value">
                        <?php echo CurrencyFormatter::formatWithSymbol($provider->finalResult->refund); ?>
                    </div>
                </div>

                <div class="result-card">
                    <label>PELUNASAN</label>
                    <div class="value" id="pelunasan-value">
                        <?php echo CurrencyFormatter::formatWithSymbol($provider->finalResult->pelunasan); ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="result-card" id="refund-card" style="display: none;">
                    <label>REFUND</label>
                    <div class="value" id="refund-value">
                        <?php echo CurrencyFormatter::formatWithSymbol($provider->finalResult->refund); ?>
                    </div>
                </div>

                <div class="result-card" id="pelunasan-card" style="display: none;">
                    <label>PELUNASAN</label>
                    <div class="value" id="pelunasan-value">
                        <?php echo CurrencyFormatter::formatWithSymbol($provider->finalResult->pelunasan); ?>
                    </div>
                </div>
            <?php endif; ?>

            <div id="detail-section" class="detail-section" style="display: none;">
                <div class="result-card">
                    <label>Total Bunga</label>
                    <div class="value" id="total-bunga-value">
                        <?php echo CurrencyFormatter::formatWithSymbol($provider->finalResult->totalBunga); ?>
                    </div>
                </div>

                <div class="result-card">
                    <label>Net AR</label>
                    <div class="value" id="net-ar-value">
                        <?php echo CurrencyFormatter::formatWithSymbol($provider->finalResult->totalNetAR); ?>
                    </div>
                </div>

                <div class="result-card">
                    <label>Fidusia</label>
                    <div class="value" id="fidusia-value">
                        <?php echo CurrencyFormatter::formatWithSymbol($provider->finalResult->biayaFidusia); ?>
                    </div>
                </div>

                <div class="result-card">
                    <label>Premi Insurance</label>
                    <div class="value" id="premi-value">
                        <?php echo CurrencyFormatter::formatWithSymbol($provider->finalResult->totalPremiAsuransi); ?>
                    </div>
                </div>

                <div class="result-card">
                    <label>Provisi</label>
                    <div class="value" id="provisi-value">
                        <?php echo CurrencyFormatter::formatWithSymbol($provider->finalResult->biayaProvisi); ?>
                    </div>
                </div>

                <div class="result-card">
                    <label>Life Insurance</label>
                    <div class="value" id="life-insurance-value">
                        <?php echo CurrencyFormatter::formatWithSymbol($provider->finalResult->lifeInsurance); ?>
                    </div>
                </div>

                <div class="result-card">
                    <label>PH 1</label>
                    <div class="value" id="ph1-value">
                        <?php echo CurrencyFormatter::formatWithSymbol($provider->finalResult->pokokHutang1); ?>
                    </div>
                </div>

                <div class="result-card">
                    <label>PH 2</label>
                    <div class="value" id="ph2-value">
                        <?php echo CurrencyFormatter::formatWithSymbol($provider->finalResult->pokokHutang2); ?>
                    </div>
                </div>

                <div class="result-card">
                    <label>PH 3</label>
                    <div class="value" id="ph3-value">
                        <?php echo CurrencyFormatter::formatWithSymbol($provider->finalResult->pokokHutang3); ?>
                    </div>
                </div>

                <div class="result-card">
                    <label>PH Final</label>
                    <div class="value" id="ph-final-value">
                        <?php echo CurrencyFormatter::formatWithSymbol($provider->finalResult->totalPokokHutangFinal); ?>
                    </div>
                </div>

                <div class="result-card">
                    <label>Biaya Administrasi</label>
                    <div class="value" id="admin-value">
                        <?php echo CurrencyFormatter::formatWithSymbol($provider->finalResult->biayaAdministrasi); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL MRP -->
        <div id="mrp-detail-modal" class="modal-overlay">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Detail MRP Standar</h3>
                    <button type="button" class="modal-close" onclick="closeMRPDetail()">&times;</button>
                </div>
                <div id="modal-unit-info" class="modal-unit-info"></div>
                <div id="mrp-area-list"></div>
            </div>
        </div>
    </div>

    <?php require __DIR__ . '/financingAssets.php'; ?>
</body>

</html>