<div class="vehicle-check-section">
    <h2 class="vehicle-check-title">Cek Kendaraan</h2>

    <div class="vehicle-check-form">
        <input
            type="text"
            id="vehicleFrameInput"
            class="vehicle-check-input"
            placeholder="Masukkan Nomor Rangka Kendaraan"
            maxlength="30"
            autocomplete="off"
        />
        <button
            type="button"
            id="vehicleCheckBtn"
            class="vehicle-check-btn"
            onclick="searchVehicle()"
        >
            Cari
        </button>
    </div>

    <div id="vehicleCheckResult" class="vehicle-check-result" style="display: none;"></div>
</div>