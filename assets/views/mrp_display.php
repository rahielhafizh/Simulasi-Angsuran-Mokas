<?php if ($provider->mrpStandarMessage): ?>
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
                    <div class="mrp-area-value"><?php echo htmlspecialchars($mrpValue, ENT_QUOTES, 'UTF-8'); ?></div>
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
                <button type="button" class="btn-detail-mrp" id="btn-detail-mrp" onclick="showMRPDetailModal()">Detail MRP</button>
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
    <div class="selisih-mrp-display <?php echo strpos($mrpDifferencePercentage, '+') === 0 ? 'positive' : 'negative'; ?>" id="selisih-mrp-display">
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