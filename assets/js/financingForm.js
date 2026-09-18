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

function escapeHtml(text) {
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

function updateCalculateButton(canCalculate) {
  const btn = document.getElementById("calculate-btn");
  if (btn) btn.disabled = !canCalculate;
}

function showError(message) {
  const errorAlert = document.getElementById("error-alert");
  const errorMessage = document.getElementById("error-message");
  if (errorAlert && errorMessage) {
    errorMessage.textContent = message;
    errorAlert.style.display = "block";
  }
}

function hideError() {
  const errorAlert = document.getElementById("error-alert");
  if (errorAlert) errorAlert.style.display = "none";
}

function hideResultSection() {
  const resultSection = document.getElementById("result-section");
  if (resultSection) resultSection.style.display = "none";
}

function resetDPPercentage() {
  const dpPercentage = document.getElementById("dp-percentage");
  if (dpPercentage) {
    dpPercentage.style.display = "none";
    dpPercentage.textContent = "";
  }
}

function hideSelisihMRP() {
  const selisihDisplay = document.getElementById("selisih-mrp-display");
  if (selisihDisplay) {
    selisihDisplay.style.display = "none";
  }
}

function updateMRPStandarDisplay(data) {
  const mrpStandarDisplay = document.getElementById("mrp-standar-display");
  const mrpStandarValue = document.getElementById("mrp-standar-value");
  const mrpListContainer = document.getElementById("mrp-list-container");
  const mrpAreaListInline = document.getElementById("mrp-area-list-inline");

  if (
    data.isHOUser &&
    data.mrpByArea &&
    Object.keys(data.mrpByArea).length > 0
  ) {
    if (mrpStandarDisplay) mrpStandarDisplay.style.display = "none";
    if (mrpListContainer && mrpAreaListInline) {
      mrpAreaListInline.innerHTML = "";

      Object.entries(data.mrpByArea).forEach(([area, mrp]) => {
        const isUnavailable = mrp === "Tidak tersedia";
        const itemDiv = document.createElement("div");
        itemDiv.className =
          "mrp-area-item" + (isUnavailable ? " unavailable" : "");
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

      if (
        data.hasMultipleArea &&
        data.mrpByArea &&
        Object.keys(data.mrpByArea).length > 0
      ) {
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

  if (
    !data.hasMultipleArea ||
    !data.mrpByArea ||
    Object.keys(data.mrpByArea).length === 0
  ) {
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

async function sendAjaxRequest(action, value = null) {
  const formData = new FormData();
  formData.append("action", action);

  if (value !== null) {
    formData.append("value", value);
  }

  const response = await fetch(window.location.href, {
    method: "POST",
    headers: {
      "X-Requested-With": "XMLHttpRequest",
    },
    body: formData,
  });

  if (!response.ok) {
    throw new Error("Jaringan tidak stabil atau ada kesalahan pada server.");
  }

  return await response.json();
}

function updateField(action, value) {
  sendAjaxRequest(action, value)
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
  const merkSelect = document.getElementById("merk");
  const modelSelect = document.getElementById("model");
  const typeSelect = document.getElementById("type");

  merkSelect.disabled = true;
  modelSelect.innerHTML = '<option value="">Mohon Tunggu...</option>';
  modelSelect.disabled = true;
  typeSelect.innerHTML = '<option value="">Pilih Type Kendaraan</option>';
  typeSelect.disabled = true;

  hideMRPStandar();
  updateCalculateButton(false);
  hideError();
  hideResultSection();

  sendAjaxRequest("update_merk", value)
    .then((data) => {
      merkSelect.disabled = false;
      if (data.success && data.data.models) {
        modelSelect.innerHTML =
          '<option value="">Pilih Model Kendaraan</option>';
        if (data.data.models.length > 0) {
          data.data.models.forEach((model) => {
            const option = document.createElement("option");
            option.value = model;
            option.textContent = model;
            modelSelect.appendChild(option);
          });
          modelSelect.disabled = false;
        } else {
          modelSelect.disabled = true;
        }
        updateCalculateButton(data.provider.canCalculate);
      } else {
        showError("Gagal memuat model kendaraan.");
        modelSelect.innerHTML =
          '<option value="">Pilih Model Kendaraan</option>';
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      merkSelect.disabled = false;
      modelSelect.innerHTML = '<option value="">Pilih Model Kendaraan</option>';
      showError("Koneksi terganggu saat memuat model. Silakan coba lagi.");
    });
}

function updateModel(value) {
  const modelSelect = document.getElementById("model");
  const typeSelect = document.getElementById("type");

  modelSelect.disabled = true;
  typeSelect.innerHTML = '<option value="">Mohon Tunggu...</option>';
  typeSelect.disabled = true;

  hideMRPStandar();
  updateCalculateButton(false);
  hideError();
  hideResultSection();

  sendAjaxRequest("update_model", value)
    .then((data) => {
      modelSelect.disabled = false;
      if (data.success && data.data.types) {
        typeSelect.innerHTML = '<option value="">Pilih Type Kendaraan</option>';
        if (data.data.types.length > 0) {
          data.data.types.forEach((type) => {
            const option = document.createElement("option");
            option.value = type;
            option.textContent = type;
            typeSelect.appendChild(option);
          });
          typeSelect.disabled = false;
        } else {
          typeSelect.disabled = true;
        }
        updateCalculateButton(data.provider.canCalculate);
      } else {
        showError("Gagal memuat type kendaraan.");
        typeSelect.innerHTML = '<option value="">Pilih Type Kendaraan</option>';
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      modelSelect.disabled = false;
      typeSelect.innerHTML = '<option value="">Pilih Type Kendaraan</option>';
      showError("Koneksi terganggu saat memuat type. Silakan coba lagi.");
    });
}

function updateType(value) {
  const typeSelect = document.getElementById("type");
  typeSelect.disabled = true;

  updateCalculateButton(false);
  hideError();
  hideResultSection();

  sendAjaxRequest("update_type", value)
    .then((data) => {
      typeSelect.disabled = false;
      if (data.success && data.data) {
        updateMRPStandarDisplay(data.data);
        updateMRPDetailModal(data.data);
        updateCalculateButton(data.provider.canCalculate);
      } else {
        showError("Data MRP tidak ditemukan.");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      typeSelect.disabled = false;
      showError("Koneksi terganggu saat memperbarui Type. Silakan coba lagi.");
    });
}

function updateTahun(value) {
  const tahunSelect = document.getElementById("tahun");
  tahunSelect.disabled = true;

  updateCalculateButton(false);
  hideError();
  hideResultSection();

  sendAjaxRequest("update_tahun", value)
    .then((data) => {
      tahunSelect.disabled = false;
      if (data.success && data.data) {
        updateMRPStandarDisplay(data.data);
        updateMRPDetailModal(data.data);
        updateCalculateButton(data.provider.canCalculate);
      } else {
        showError("Data MRP tidak ditemukan.");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      tahunSelect.disabled = false;
      showError(
        "Koneksi terganggu saat tahun diperbarui. Silakan coba lagi.",
      );
    });
}

function submitMRP(value) {
  if (!value) {
    hideSelisihMRP();
    return;
  }

  sendAjaxRequest("update_mrp", value)
    .then((data) => {
      if (data.success) {
        if (
          !data.data.isHOUser &&
          data.data.mrpDifferencePercentage &&
          data.data.mrpDifferenceNominal
        ) {
          updateSelisihMRPDisplay(
            data.data.mrpDifferencePercentage,
            data.data.mrpDifferenceNominal,
          );
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

  sendAjaxRequest("update_dp", value)
    .then((data) => {
      if (data.success) {
        if (data.data.dpPercentage) {
          const dpEl = document.getElementById("dp-percentage");
          dpEl.textContent = data.data.dpPercentage;
          dpEl.style.display = "flex";
        } else {
          resetDPPercentage();
        }

        if (data.data.tdpPreview) {
          document.getElementById("tdp-value").textContent =
            data.data.tdpPreview;
        }
        updateCalculateButton(data.provider.canCalculate);
        hideError();
        hideResultSection();
      }
    })
    .catch((error) => console.error("Error:", error));
}

function calculateFinancing() {
  const btn = document.getElementById("calculate-btn");
  if (btn) btn.disabled = true;

  sendAjaxRequest("calculate")
    .then((data) => {
      if (btn) btn.disabled = false;
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
    .catch((error) => {
      console.error("Error:", error);
      if (btn) btn.disabled = false;
      showError("Kalkulasi gagal. Periksa koneksi jaringan Anda.");
    });
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

  document.getElementById("angsuran-value").textContent =
    formatRupiah(angsuran);
  document.getElementById("all-in-value").textContent = formatRupiah(allIn);
  document.getElementById("pelunasan-value").textContent =
    formatRupiah(pelunasan);
  document.getElementById("refund-value").textContent = formatRupiah(refund);
  document.getElementById("tdp-value").textContent = formatRupiah(tdp);

  const refundCard = document.getElementById("refund-card");
  const pelunasanCard = document.getElementById("pelunasan-card");

  if (
    typeof shouldShowExtendedResults !== "undefined" &&
    shouldShowExtendedResults
  ) {
    if (refundCard) refundCard.style.display = "flex";
    if (pelunasanCard) pelunasanCard.style.display = "flex";
  } else {
    if (refundCard) refundCard.style.display = "none";
    if (pelunasanCard) pelunasanCard.style.display = "none";
  }

  document.getElementById("total-bunga-value").textContent =
    formatRupiah(totalBunga);
  document.getElementById("net-ar-value").textContent = formatRupiah(netAR);
  document.getElementById("fidusia-value").textContent = formatRupiah(fidusia);
  document.getElementById("premi-value").textContent = formatRupiah(premi);
  document.getElementById("provisi-value").textContent = formatRupiah(provisi);
  document.getElementById("life-insurance-value").textContent =
    formatRupiah(lifeIns);
  document.getElementById("ph1-value").textContent = formatRupiah(ph1);
  document.getElementById("ph2-value").textContent = formatRupiah(ph2);
  document.getElementById("ph3-value").textContent = formatRupiah(ph3);
  document.getElementById("ph-final-value").textContent = formatRupiah(phFinal);
  document.getElementById("admin-value").textContent = formatRupiah(admin);
}

function resetForm() {
  if (confirm("Apakah Anda yakin ingin mereset semua data?")) {
    sendAjaxRequest("reset")
      .then((data) => {
        if (data.success) {
          location.reload();
        }
      })
      .catch((error) => console.error("Error:", error));
  }
}

function initCustomDropdowns() {
  const selects = document.querySelectorAll(".form-group select");

  selects.forEach((select) => {
    if (select.closest(".custom-select-container")) return;

    const container = document.createElement("div");
    container.className = "custom-select-container";
    select.parentNode.insertBefore(container, select);
    container.appendChild(select);

    const trigger = document.createElement("div");
    trigger.className = "custom-select-trigger";
    trigger.setAttribute("tabindex", select.disabled ? "-1" : "0");
    if (select.disabled) trigger.classList.add("disabled");

    const triggerText = document.createElement("span");
    triggerText.className = "custom-select-text";
    trigger.appendChild(triggerText);

    const arrow = document.createElement("div");
    arrow.className = "custom-select-arrow";
    arrow.innerHTML =
      '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>';
    trigger.appendChild(arrow);

    const optionsContainer = document.createElement("div");
    optionsContainer.className = "custom-select-options";

    function syncTrigger() {
      const selectedOpt = select.options[select.selectedIndex];
      triggerText.textContent = selectedOpt ? selectedOpt.textContent : "";

      if (selectedOpt && selectedOpt.value === "") {
        triggerText.style.color = "var(--app-text-light)";
      } else {
        triggerText.style.color = "var(--app-text-primary)";
      }
    }

    function populateOptions() {
      optionsContainer.innerHTML = "";
      Array.from(select.options).forEach((option, index) => {
        const optDiv = document.createElement("div");
        optDiv.className = "custom-select-option";
        if (option.selected) optDiv.classList.add("selected");
        optDiv.textContent = option.textContent;

        optDiv.addEventListener("click", (e) => {
          e.stopPropagation();
          if (select.disabled) return;

          if (select.selectedIndex !== index) {
            select.selectedIndex = index;
            const event = new Event("change", { bubbles: true });
            select.dispatchEvent(event);
          }

          optionsContainer.classList.remove("show");
          trigger.classList.remove("active");
          syncTrigger();

          Array.from(optionsContainer.children).forEach((c) =>
            c.classList.remove("selected"),
          );
          optDiv.classList.add("selected");
        });

        optionsContainer.appendChild(optDiv);
      });
      syncTrigger();
    }

    populateOptions();
    container.appendChild(trigger);
    container.appendChild(optionsContainer);

    trigger.addEventListener("click", (e) => {
      if (select.disabled) return;
      e.stopPropagation();

      const isShowing = optionsContainer.classList.contains("show");

      document.querySelectorAll(".custom-select-options.show").forEach((el) => {
        el.classList.remove("show");
        el.previousElementSibling.classList.remove("active");
      });

      if (!isShowing) {
        optionsContainer.classList.add("show");
        trigger.classList.add("active");

        const selectedOpt = optionsContainer.querySelector(".selected");
        if (selectedOpt) {
          setTimeout(() => {
            selectedOpt.scrollIntoView({
              block: "nearest",
              behavior: "smooth",
            });
          }, 15);
        }
      }
    });

    trigger.addEventListener("keydown", (e) => {
      if (e.key === "Enter" || e.key === " ") {
        e.preventDefault();
        trigger.click();
      }
    });

    const observer = new MutationObserver((mutations) => {
      let optionsChanged = false;
      mutations.forEach((mutation) => {
        if (mutation.type === "childList") {
          optionsChanged = true;
        }
        if (
          mutation.type === "attributes" &&
          mutation.attributeName === "disabled"
        ) {
          if (select.disabled) {
            trigger.classList.add("disabled");
            trigger.classList.remove("active");
            trigger.setAttribute("tabindex", "-1");
            optionsContainer.classList.remove("show");
          } else {
            trigger.classList.remove("disabled");
            trigger.setAttribute("tabindex", "0");
          }
        }
      });

      if (optionsChanged) {
        populateOptions();
      }
    });

    observer.observe(select, { childList: true, attributes: true });

    select.style.display = "none";
  });

  document.addEventListener("click", (e) => {
    if (!e.target.closest(".custom-select-container")) {
      document.querySelectorAll(".custom-select-options.show").forEach((el) => {
        el.classList.remove("show");
        el.previousElementSibling.classList.remove("active");
      });
    }
  });
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

  initCustomDropdowns();
});
