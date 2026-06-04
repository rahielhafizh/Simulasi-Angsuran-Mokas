<script src="assets/js/financing-form.js"></script>
<script>
    const shouldShowExtendedResults = <?php echo $shouldShowExtendedResults ? 'true' : 'false'; ?>;

    function toggleMenu() {
        const dropdown = document.getElementById('dropdownMenu');
        dropdown.classList.toggle('show');
    }

    async function searchVehicle() {
        const input = document.getElementById('vehicleFrameInput');
        const btn = document.getElementById('vehicleCheckBtn');
        const result = document.getElementById('vehicleCheckResult');
        const nomorRangka = input.value.trim();

        if (nomorRangka === '') {
            showVehicleResult('danger', 'Hasil Pengecekan', 'Silakan masukkan Nomor Rangka Kendaraan terlebih dahulu.');
            return;
        }

        btn.disabled = true;
        input.disabled = true;
        btn.textContent = 'Harap Tunggu...';
        result.style.display = 'none';
        result.className = 'vehicle-check-result';

        try {
            const formData = new FormData();
            formData.append('nomor_rangka', nomorRangka);

            const response = await fetch('vehicle_check.php', {
                method: 'POST',
                body: formData,
            });

            if (!response.ok) {
                throw new Error('Jaringan tidak stabil. Silakan coba lagi.');
            }

            const data = await response.json();

            if (!data.success) {
                showVehicleResult('danger', 'Hasil Pengecekan', data.message ?? 'Terjadi kesalahan. Silakan coba lagi.');
                return;
            }

            if (data.found) {
                const vehicleStatus = (data.status || 'N/A').toUpperCase();
                const uiClass = (vehicleStatus === 'INACTIVE' || vehicleStatus === 'N/A')
                    ? 'danger'
                    : 'success';

                showVehicleResult(
                    uiClass,
                    'Hasil Pengecekan',
                    null,
                    data.certified_to,
                    data.status
                );
            } else {
                showVehicleResult('danger', 'Hasil Pengecekan', null, 'N/A', 'N/A');
            }
        } catch (err) {
            showVehicleResult(
                'danger',
                'Hasil Pengecekan',
                err.message ?? 'Terjadi kesalahan. Silakan coba lagi.'
            );
        } finally {
            btn.disabled = false;
            input.disabled = false;
            btn.textContent = 'Cari';
        }
    }

    function showVehicleResult(status, heading, errorMessage, certifiedTo, vehicleStatus) {
        const result = document.getElementById('vehicleCheckResult');

        result.className = 'vehicle-check-result status-' + status;

        if (errorMessage) {
            result.innerHTML =
                '<div class="vehicle-check-result-heading" style="text-align:center; margin-bottom:10px;">' +
                    escapeHtml(heading) +
                '</div>' +
                '<span class="vehicle-check-certified-label">' +
                    escapeHtml(errorMessage) +
                '</span>';
        } else {
            result.innerHTML =
                '<div class="vehicle-check-result-heading" style="text-align:center; margin-bottom:10px;">' +
                    escapeHtml(heading) +
                '</div>' +
                '<div style="margin-bottom: 6px;">' +
                    '<span class="vehicle-check-certified-label">Certified to : </span>' +
                    '<span class="vehicle-check-certified-value">' +
                        escapeHtml(certifiedTo ?? 'N/A') +
                    '</span>' +
                '</div>' +
                '<div>' +
                    '<span class="vehicle-check-certified-label">Status : </span>' +
                    '<span class="vehicle-check-certified-value" style="text-transform: uppercase;">' +
                        escapeHtml(vehicleStatus ?? 'N/A') +
                    '</span>' +
                '</div>';
        }

        result.style.display = 'block';
    }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    document.getElementById('vehicleFrameInput')?.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            searchVehicle();
        }
    });

    document.addEventListener('click', function(e) {
        const menuContainer = document.querySelector('.menu-container');
        const dropdown = document.getElementById('dropdownMenu');

        if (menuContainer && !menuContainer.contains(e.target)) {
            dropdown.classList.remove('show');
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($showDetailMRPButton && count($mrpByAreaFormatted) > 0): ?>
        const initialData = {
            mrpByArea: <?php echo json_encode($mrpByAreaFormatted, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
            hasMultipleArea: true,
            showDetailMRPButton: true,
            isHOUser: <?php echo $isHOUser ? 'true' : 'false'; ?>,
            unitInfo: <?php echo json_encode(
        (!empty($provider->finalCriteria->merk) &&
                !empty($provider->finalCriteria->model) &&
                !empty($provider->finalCriteria->type) &&
                !empty($provider->finalCriteria->tahun))
            ? $provider->finalCriteria->merk . ' '
                . $provider->finalCriteria->model . ' '
                . $provider->finalCriteria->type . ' '
                . $provider->finalCriteria->tahun
            : '',
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
    ); ?>
        };

        updateMRPDetailModal(initialData);
        <?php endif; ?>
    });
</script>