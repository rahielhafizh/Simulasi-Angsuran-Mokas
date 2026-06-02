<div class="result-section" id="result-section" style="display: <?php echo $provider->calculationStatus ? 'block' : 'none'; ?>;">
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
        <?php include __DIR__ . '/result_detail.php'; ?>
    </div>
</div>