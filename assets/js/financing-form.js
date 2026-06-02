function formatCurrency(input) {
  let value = input.value.replace(/[^\d]/g, "");
  input.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function formatRupiah(value) {
  if (value === 0 || value === "0" || value === null || value === undefined) {
    return "Rp 0";
  }

  const intValue = Math.round(parseFloat(value));
  const strValue = intValue.toString();
  const formatted = strValue.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
  return "Rp " + formatted;
}

function updateField(action, value) {
  const formData = new FormData();
  formData.append("action", action);
  formData.append("value", value);

  fetch(window.location.href, {
    method: "POST",
    headers: {
      "X-Requested-With": "XMLHttpRequest",
    },
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        updateCalculateButton(data.provider.canCalculate);
        hideError();
        hideResultSection();
      }
    })
    .catch((error) => console.error("Error:", error));
}

function updateMerk(value) {
  const formData = new FormData();
  formData.append("action", "update_merk");
  formData.append("value", value);

  fetch(window.location.href, {
    method: "POST",
    headers: {
      "X-Requested-With": "XMLHttpRequest",
    },
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.data.models) {
        const modelSelect = document.getElementById("model");
        const typeSelect = document.getElementById("type");

        modelSelect.innerHTML = '<option value="">Pilih Model Kendaraan</option>';
        data.data.models.forEach((model) => {
          const option = document.createElement("option");
          option.value = model;
          option.textContent = model;
          modelSelect.appendChild(option);
        });
        modelSelect.disabled = false;

        typeSelect.innerHTML = '<option value="">Pilih Type Kendaraan</option>';
        typeSelect.disabled = true;
        hideMRPStandar();
        updateCalculateButton(data.provider.canCalculate);
        hideError();
        hideResultSection();
      }
    })
    .catch((error) => console.error("Error:", error));
}

function updateModel(value) {
  const formData = new FormData();
  formData.append("action", "update_model");
  formData.append("value", value);

  fetch(window.location.href, {
    method: "POST",
    headers: {
      "X-Requested-With": "XMLHttpRequest",
    },
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.data.types) {
        const typeSelect = document.getElementById("type");

        typeSelect.innerHTML = '<option value="">Pilih Type Kendaraan</option>';
        data.data.types.forEach((type) => {
          const option = document.createElement("option");
          option.value = type;
          option.textContent = type;
          typeSelect.appendChild(option);
        });
        typeSelect.disabled = false;

        hideMRPStandar();
        updateCalculateButton(data.provider.canCalculate);
        hideError();
        hideResultSection();
      }
    })
    .catch((error) => console.error("Error:", error));
}

function updateType(value) {
  const formData = new FormData();
  formData.append("action", "update_type");
  formData.append("value", value);

  fetch(window.location.href, {
    method: "POST",
    headers: {
      "X-Requested-With": "XMLHttpRequest",
    },
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.data) {
        updateMRPStandarDisplay(data.data);
        updateMRPDetailModal(data.data);
        updateCalculateButton(data.provider.canCalculate);
        hideError();
        hideResultSection();
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      window.location.reload();
    });
}

function updateTahun(value) {
  const formData = new FormData();
  formData.append("action", "update_tahun");
  formData.append("value", value);

  fetch(window.location.href, {
    method: "POST",
    headers: {
      "X-Requested-With": "XMLHttpRequest",
    },
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.data) {
        updateMRPStandarDisplay(data.data);
        updateMRPDetailModal(data.data);
        updateCalculateButton(data.provider.canCalculate);
        hideError();
        hideResultSection();
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      window.location.reload();
    });
}

function updateMRPStandarDisplay(data) {
  const mrpStandarDisplay = document.getElementById("mrp-standar-display");
  const mrpStandarValue = document.getElementById("mrp-standar-value");
  const mrpListContainer = document.getElementById("mrp-list-container");
  const mrpAreaListInline = document.getElementById("mrp-area-list-inline");

  if (data.isHOUser && data.mrpByArea && Object.keys(data.mrpByArea).length > 0) {
    if (mrpStandarDisplay) mrpStandarDisplay.style.display = "none";
    if (mrpListContainer && mrpAreaListInline) {
      mrpAreaListInline.innerHTML = "";
      Object.entries(data.mrpByArea).forEach(([area, mrp]) => {
        const isUnavailable = mrp === "Tidak tersedia";
        const itemDiv = document.createElement("div");
        itemDiv.className = "mrp-area-item" + (isUnavailable ? " unavailable" : "");
        itemDiv.innerHTML = `
          <div class="mrp-area-name">MRP ${escapeHtml(area)}</div>
          <div class="mrp-area-value">${escapeHtml(mrp)}</div>
        `;
        mrpAreaListInline.appendChild(itemDiv);
      });
      mrpListContainer.style.display = "block";
    }
  } else if (data.mrpStandarMessage) {
    if (mrpListContainer) mrpListContainer.style.display = "none";
    if (mrpStandarDisplay) {
      mrpStandarDisplay.classList.add("unavailable");
      mrpStandarDisplay.classList.remove("available");
      mrpStandarValue.innerHTML = "";
      mrpStandarValue.textContent = data.mrpStandarMessage;
      mrpStandarDisplay.style.display = "flex";
    }
  } else if (data.mrpStandar && data.mrpStandarFormatted) {
    if (mrpListContainer) mrpListContainer.style.display = "none";
    if (mrpStandarDisplay && mrpStandarValue) {
      mrpStandarDisplay.classList.remove("unavailable");
      mrpStandarDisplay.classList.add("available");
      mrpStandarValue.innerHTML = "";
      mrpStandarValue.textContent = data.mrpStandarFormatted;

      if (data.hasMultipleArea && data.mrpByArea && Object.keys(data.mrpByArea).length > 0) {
        const button = document.createElement("button");
        button.type = "button";
        button.className = "btn-detail-mrp";
        button.id = "btn-detail-mrp";
        button.textContent = "Detail MRP";
        button.onclick = showMRPDetailModal;
        mrpStandarValue.appendChild(document.createTextNode(" "));
        mrpStandarValue.appendChild(button);
      }

      mrpStandarDisplay.style.display = "flex";
    }
  } else {
    hideMRPStandar();
  }
}

function hideMRPStandar() {
  const mrpStandarDisplay = document.getElementById("mrp-standar-display");
  const mrpListContainer = document.getElementById("mrp-list-container");

  if (mrpStandarDisplay) mrpStandarDisplay.style.display = "none";
  if (mrpListContainer) mrpListContainer.style.display = "none";
  hideSelisihMRP();
}

function hideSelisihMRP() {
  const selisihDisplay = document.getElementById("selisih-mrp-display");
  if (selisihDisplay) {
    selisihDisplay.style.display = "none";
  }
}

function updateSelisihMRPDisplay(percentage, nominal) {
  const selisihDisplay = document.getElementById("selisih-mrp-display");
  const selisihPercentage = document.getElementById("selisih-percentage");
  const selisihNominal = document.getElementById("selisih-nominal");

  if (selisihDisplay && selisihPercentage && selisihNominal) {
    selisihPercentage.textContent = percentage;
    selisihNominal.textContent = nominal;

    selisihDisplay.classList.remove("positive", "negative");
    if (percentage.startsWith("+")) {
      selisihDisplay.classList.add("positive");
    } else if (percentage.startsWith("-")) {
      selisihDisplay.classList.add("negative");
    }

    selisihDisplay.style.display = "flex";
  }
}

function resetDPPercentage() {
  const dpPercentage = document.getElementById("dp-percentage");
  dpPercentage.style.display = "none";
  dpPercentage.textContent = "";
}

function submitMRP(value) {
  if (!value) {
    hideSelisihMRP();
    return;
  }

  const formData = new FormData();
  formData.append("action", "update_mrp");
  formData.append("value", value);

  fetch(window.location.href, {
    method: "POST",
    headers: {
      "X-Requested-With": "XMLHttpRequest",
    },
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        if (!data.data.isHOUser && data.data.mrpDifferencePercentage && data.data.mrpDifferenceNominal) {
          updateSelisihMRPDisplay(data.data.mrpDifferencePercentage, data.data.mrpDifferenceNominal);
        } else {
          hideSelisihMRP();
        }

        updateCalculateButton(data.provider.canCalculate);
        hideError();
        hideResultSection();
      }
    })
    .catch((error) => console.error("Error:", error));
}

function submitDP(value) {
  if (!value) {
    resetDPPercentage();
    return;
  }

  const formData = new FormData();
  formData.append("action", "update_dp");
  formData.append("value", value);

  fetch(window.location.href, {
    method: "POST",
    headers: {
      "X-Requested-With": "XMLHttpRequest",
    },
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        if (data.data.dpPercentage) {
          document.getElementById("dp-percentage").textContent = data.data.dpPercentage;
          document.getElementById("dp-percentage").style.display = "flex";
        } else {
          resetDPPercentage();
        }

        if (data.data.tdpPreview) {
          document.getElementById("tdp-value").textContent = data.data.tdpPreview;
        }

        updateCalculateButton(data.provider.canCalculate);
        hideError();
        hideResultSection();
      }
    })
    .catch((error) => console.error("Error:", error));
}

function calculateFinancing() {
  const formData = new FormData();
  formData.append("action", "calculate");

  fetch(window.location.href, {
    method: "POST",
    headers: {
      "X-Requested-With": "XMLHttpRequest",
    },
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        if (data.data.errorValidation) {
          showError(data.data.errorValidation);
          document.getElementById("result-section").style.display = "none";
        } else if (data.data.calculationStatus && data.data.result) {
          hideError();
          updateResults(data.data.result);
          document.getElementById("result-section").style.display = "block";
        }
      }
    })
    .catch((error) => console.error("Error:", error));
}

function updateResults(result) {
  const angsuran = parseInt(result.angsuranPerBulan) || 0;
  const allIn = parseInt(result.allIn) || 0;
  const pelunasan = parseInt(result.pelunasan) || 0;
  const refund = parseInt(result.refund) || 0;
  const tdp = parseInt(result.tdp) || 0;
  const totalBunga = parseInt(result.totalBunga) || 0;
  const netAR = parseInt(result.totalNetAR) || 0;
  const fidusia = parseInt(result.biayaFidusia) || 0;
  const premi = parseInt(result.totalPremiAsuransi) || 0;
  const provisi = parseInt(result.biayaProvisi) || 0;
  const lifeIns = parseInt(result.lifeInsurance) || 0;
  const ph1 = parseInt(result.pokokHutang1) || 0;
  const ph2 = parseInt(result.pokokHutang2) || 0;
  const ph3 = parseInt(result.pokokHutang3) || 0;
  const phFinal = parseInt(result.totalPokokHutangFinal) || 0;
  const admin = parseInt(result.biayaAdministrasi) || 0;

  document.getElementById("angsuran-value").textContent = formatRupiah(angsuran);
  document.getElementById("all-in-value").textContent = formatRupiah(allIn);
  document.getElementById("pelunasan-value").textContent = formatRupiah(pelunasan);
  document.getElementById("refund-value").textContent = formatRupiah(refund);
  document.getElementById("tdp-value").textContent = formatRupiah(tdp);

  const refundCard = document.getElementById("refund-card");
  const pelunasanCard = document.getElementById("pelunasan-card");

  if (typeof shouldShowExtendedResults !== 'undefined' && shouldShowExtendedResults) {
    if (refundCard) refundCard.style.display = "flex";
    if (pelunasanCard) pelunasanCard.style.display = "flex";
  } else {
    if (refundCard) refundCard.style.display = "none";
    if (pelunasanCard) pelunasanCard.style.display = "none";
  }

  document.getElementById("total-bunga-value").textContent = formatRupiah(totalBunga);
  document.getElementById("net-ar-value").textContent = formatRupiah(netAR);
  document.getElementById("fidusia-value").textContent = formatRupiah(fidusia);
  document.getElementById("premi-value").textContent = formatRupiah(premi);
  document.getElementById("provisi-value").textContent = formatRupiah(provisi);
  document.getElementById("life-insurance-value").textContent = formatRupiah(lifeIns);
  document.getElementById("ph1-value").textContent = formatRupiah(ph1);
  document.getElementById("ph2-value").textContent = formatRupiah(ph2);
  document.getElementById("ph3-value").textContent = formatRupiah(ph3);
  document.getElementById("ph-final-value").textContent = formatRupiah(phFinal);
  document.getElementById("admin-value").textContent = formatRupiah(admin);
}

function hideResultSection() {
  document.getElementById("result-section").style.display = "none";
}

function resetForm() {
  if (confirm("Apakah Anda yakin ingin mereset semua data?")) {
    const formData = new FormData();
    formData.append("action", "reset");

    fetch(window.location.href, {
      method: "POST",
      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          location.reload();
        }
      })
      .catch((error) => console.error("Error:", error));
  }
}

function updateCalculateButton(canCalculate) {
  const btn = document.getElementById("calculate-btn");
  btn.disabled = !canCalculate;
}

function showError(message) {
  const errorAlert = document.getElementById("error-alert");
  const errorMessage = document.getElementById("error-message");
  errorMessage.textContent = message;
  errorAlert.style.display = "block";
}

function hideError() {
  document.getElementById("error-alert").style.display = "none";
}

function showMRPDetail() {
  const modal = document.getElementById("mrp-detail-modal");
  if (modal) {
    modal.style.display = "flex";
    document.body.style.overflow = "hidden";
  }
}

function showMRPDetailModal() {
  showMRPDetail();
}

function closeMRPDetail() {
  const modal = document.getElementById("mrp-detail-modal");
  if (modal) {
    modal.style.display = "none";
    document.body.style.overflow = "";
  }
}

function updateMRPDetailModal(data) {
  const areaList = document.getElementById("mrp-area-list");
  const unitInfo = document.getElementById("modal-unit-info");

  if (!areaList) return;

  if (unitInfo && data.unitInfo) {
    unitInfo.textContent = data.unitInfo;
  }

  areaList.innerHTML = "";

  if (!data.hasMultipleArea || !data.mrpByArea || Object.keys(data.mrpByArea).length === 0) {
    areaList.innerHTML = `
      <div class="mrp-area-empty">
        Tidak ada data MRP untuk unit ini
      </div>
    `;
    return;
  }

  Object.entries(data.mrpByArea).forEach(([area, mrp]) => {
    const isUnavailable = mrp === "Tidak tersedia";
    const itemDiv = document.createElement("div");
    itemDiv.className = "mrp-area-item" + (isUnavailable ? " unavailable" : "");

    itemDiv.innerHTML = `
      <div class="mrp-area-name">MRP ${escapeHtml(area)}</div>
      <div class="mrp-area-value">${escapeHtml(mrp)}</div>
    `;

    areaList.appendChild(itemDiv);
  });
}

function escapeHtml(text) {
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

document.addEventListener("DOMContentLoaded", function () {
  const modal = document.getElementById("mrp-detail-modal");
  if (modal) {
    modal.addEventListener("click", function (e) {
      if (e.target === modal) {
        closeMRPDetail();
      }
    });
  }

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
      closeMRPDetail();
    }
  });
});