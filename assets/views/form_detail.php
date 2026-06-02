<div class="form-section">
    <h2>DETAIL PENGAJUAN PEMBIAYAAN</h2>

    <?php include __DIR__ . '/mrp_display.php'; ?>

    <div class="form-group">
        <label>OTR PENGAJUAN</label>
        <input type="text" id="mrp-value" 
            value="<?php echo $provider->finalDetails->mrpPengajuan > 0 ? CurrencyFormatter::formatWithoutSymbol($provider->finalDetails->mrpPengajuan) : ''; ?>" 
            placeholder="Masukkan Nominal MRP" 
            onkeyup="formatCurrency(this)" 
            onblur="submitMRP(this.value)">
    </div>

    <div class="form-group">
        <label>DP</label>
        <div class="dp-wrapper">
            <div class="dp-percentage" id="dp-percentage" style="display: <?php echo $dpPercentage ? 'flex' : 'none'; ?>;">
                <?php echo htmlspecialchars($dpPercentage ?? '', ENT_QUOTES, 'UTF-8'); ?>
            </div>
            <input type="text" id="dp-value" 
                value="<?php echo $provider->finalDetails->dp > 0 ? CurrencyFormatter::formatWithoutSymbol($provider->finalDetails->dp) : ''; ?>" 
                placeholder="Masukkan Nominal DP (Min. <?php echo htmlspecialchars($minDpPercentLabel, ENT_QUOTES, 'UTF-8'); ?>)" 
                onkeyup="formatCurrency(this)" 
                onblur="submitDP(this.value)">
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
        <button type="button" class="btn btn-primary" id="calculate-btn" onclick="calculateFinancing()"
            <?php echo !$provider->canCalculate() ? 'disabled' : ''; ?>>
            Hitung
        </button>
    </div>
</div>