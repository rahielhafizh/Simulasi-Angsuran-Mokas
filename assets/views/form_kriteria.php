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