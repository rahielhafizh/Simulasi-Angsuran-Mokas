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