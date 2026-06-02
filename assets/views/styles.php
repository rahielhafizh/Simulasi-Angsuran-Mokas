<style>

    /* ─── Reset & Base ───────────────────────────────────────────────── */

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        background-color: <?php echo AppColors::BACKGROUND; ?>;
        color: <?php echo AppColors::TEXT_PRIMARY; ?>;
        line-height: 1.6;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    /* ─── Header ─────────────────────────────────────────────────────── */

    .header {
        background-color: <?php echo AppColors::WHITE; ?>;
        padding: 24px 20px 20px 20px;
        margin-bottom: 16px;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        border: 1px solid #E5E7EB;
    }

    .header h1 {
        font-size: 24px;
        color: <?php echo AppColors::TEXT_PRIMARY; ?>;
        text-align: center;
        margin-bottom: 20px;
        font-weight: 600;
    }

    .header-bottom {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .dealer-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .dealer-name {
        font-size: 15px;
        color: <?php echo AppColors::TEXT_PRIMARY; ?>;
        font-weight: 600;
    }

    .dealer-area {
        font-size: 14px;
        color: #6B7280;
        font-weight: 400;
    }

    /* ─── Dropdown Menu ──────────────────────────────────────────────── */

    .menu-container {
        position: relative;
    }

    .menu-button {
        background-color: #E5E7EB;
        border: none;
        border-radius: 8px;
        padding: 10px 12px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        width: 44px;
        height: 44px;
    }

    .menu-button:hover {
        background-color: #D1D5DB;
    }

    .menu-button:active {
        background-color: #C4C8CF;
    }

    .menu-icon {
        width: 20px;
        height: 20px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 3px 0;
    }

    .menu-icon span {
        display: block;
        width: 100%;
        height: 2.5px;
        background-color: <?php echo AppColors::TEXT_PRIMARY; ?>;
        border-radius: 2px;
    }

    .dropdown-menu {
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        background-color: <?php echo AppColors::WHITE; ?>;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        min-width: 160px;
        z-index: 1000;
        display: none;
        overflow: hidden;
    }

    .dropdown-menu.show {
        display: block;
        animation: dropdownFadeIn 0.15s ease-out;
    }

    @keyframes dropdownFadeIn {
        from {
            opacity: 0;
            transform: translateY(-8px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .dropdown-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        color: <?php echo AppColors::TEXT_PRIMARY; ?>;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: background-color 0.15s;
    }

    .dropdown-item:hover {
        background-color: #F9FAFB;
    }

    .dropdown-item svg {
        width: 18px;
        height: 18px;
        flex-shrink: 0;
    }

    .dropdown-item.logout {
        color: #DC2626;
    }

    .dropdown-item.logout:hover {
        background-color: #FEF2F2;
    }

    /* ─── Vehicle Check Section ──────────────────────────────────────── */

    .vehicle-check-section {
        background-color: <?php echo AppColors::WHITE; ?>;
        padding: 20px;
        margin-bottom: 20px;
        border-radius: 12px;
        border: 1px solid <?php echo AppColors::BORDER; ?>;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.06);
    }

    .vehicle-check-title {
        font-size: 16px;
        font-weight: 700;
        text-align: center;
        color: <?php echo AppColors::TEXT_PRIMARY; ?>;
        margin-bottom: 16px;
        letter-spacing: 0.04em;
    }

    .vehicle-check-form {
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 8px;
        width: 100%;
    }

    .vehicle-check-input {
        flex: 1;
        min-width: 0;
        height: 44px;
        padding: 0 12px;
        border: 1px solid #D1D5DB;
        border-radius: 8px;
        font-size: 14px;
        background-color: <?php echo AppColors::BACKGROUND; ?>;
        color: <?php echo AppColors::TEXT_PRIMARY; ?>;
        transition: border-color 0.2s, background-color 0.2s;
        box-sizing: border-box;
    }

    .vehicle-check-input::placeholder {
        color: <?php echo AppColors::TEXT_LIGHT; ?>;
        font-size: 13px;
    }

    .vehicle-check-input:focus {
        outline: none;
        border-color: <?php echo AppColors::INFO; ?>;
        background-color: <?php echo AppColors::WHITE; ?>;
    }

    .vehicle-check-input:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .vehicle-check-btn {
        flex-shrink: 0;
        height: 44px;
        padding: 0 18px;
        background-color: #1E3A8A;
        color: <?php echo AppColors::WHITE; ?>;
        border: none;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        white-space: nowrap;
        transition: background-color 0.2s, opacity 0.2s, transform 0.1s;
    }

    .vehicle-check-btn:hover:not(:disabled) {
        background-color: #1E40AF;
    }

    .vehicle-check-btn:active:not(:disabled) {
        transform: scale(0.98);
    }

    .vehicle-check-btn:disabled {
        opacity: 0.55;
        cursor: not-allowed;
    }

    .vehicle-check-result {
        margin-top: 14px;
        padding: 14px 16px;
        border-radius: 8px;
        font-size: 14px;
        line-height: 1.5;
        border: 1px solid transparent;
    }

    .vehicle-check-result.status-success {
        background-color: #ECFDF5;
        border-color: <?php echo AppColors::SUCCESS; ?>;
        color: <?php echo AppColors::SUCCESS; ?>;
    }

    .vehicle-check-result.status-danger {
        background-color: #FEF2F2;
        border-color: <?php echo AppColors::DANGER; ?>;
        color: <?php echo AppColors::DANGER; ?>;
    }

    .vehicle-check-result-heading {
        font-weight: 700;
        margin-bottom: 4px;
    }

    .vehicle-check-certified-label {
        font-weight: 400;
    }

    .vehicle-check-certified-value {
        font-weight: 700;
    }

    /* ─── Form Section ───────────────────────────────────────────────── */

    .form-section {
        background-color: <?php echo AppColors::FORM_BACKGROUND; ?>;
        padding: 20px;
        margin-bottom: 20px;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        border: 1px solid #E5E7EB;
    }

    .form-section h2 {
        font-size: 18px;
        margin-bottom: 20px;
        text-align: center;
        color: <?php echo AppColors::TEXT_PRIMARY; ?>;
        font-weight: 600;
    }

    .form-group {
        margin-bottom: 16px;
    }

    .form-group label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 8px;
        color: <?php echo AppColors::TEXT_PRIMARY; ?>;
    }

    .form-group select,
    .form-group input[type="text"],
    .form-group input[type="number"] {
        width: 100%;
        height: 52px;
        padding: 0 16px;
        border: 1px solid <?php echo AppColors::BORDER; ?>;
        border-radius: 8px;
        font-size: 14px;
        background-color: <?php echo AppColors::WHITE; ?>;
        transition: border-color 0.2s;
    }

    .form-group select:focus,
    .form-group input:focus {
        outline: none;
        border-color: #3B82F6;
    }

    .form-group select:disabled,
    .form-group input:disabled {
        background-color: #F3F4F6;
        cursor: not-allowed;
        opacity: 0.6;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    /* ─── DP Input ───────────────────────────────────────────────────── */

    .dp-wrapper {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .dp-percentage {
        height: 52px;
        padding: 0 12px;
        background-color: #EFF6FF;
        border: 1px solid #93C5FD;
        border-radius: 8px;
        display: flex;
        align-items: center;
        font-size: 14px;
        font-weight: 500;
        color: <?php echo AppColors::PRIMARY; ?>;
        white-space: nowrap;
    }

    /* ─── Buttons ────────────────────────────────────────────────────── */

    .btn {
        padding: 14px 32px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-primary {
        background-color: <?php echo AppColors::PRIMARY; ?>;
        color: white;
    }

    .btn-primary:hover:not(:disabled) {
        background-color: #1D4ED8;
    }

    .btn-primary:disabled {
        background-color: <?php echo AppColors::DISABLED; ?>;
        cursor: not-allowed;
        opacity: 0.6;
    }

    .btn-secondary {
        background-color: #9A031E;
        color: white;
    }

    .btn-secondary:hover {
        background-color: #9E2A2B;
    }

    .btn-detail-mrp {
        padding: 8px 16px;
        background-color: #3B82F6;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-detail-mrp:hover {
        background-color: #2563EB;
    }

    .btn-actions {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        margin-top: 20px;
    }

    /* ─── Alert ──────────────────────────────────────────────────────── */

    .alert {
        padding: 12px 16px;
        margin-bottom: 16px;
        border-radius: 8px;
        background-color: #FEE2E2;
        border: 1px solid #FECACA;
        color: #DC2626;
        font-size: 14px;
    }

    /* ─── Result Section ─────────────────────────────────────────────── */

    .result-section {
        background-color: <?php echo AppColors::WHITE; ?>;
        padding: 20px;
        border-radius: 12px;
        border: 1px solid <?php echo AppColors::BORDER; ?>;
        margin-bottom: 20px;
    }

    .result-section h2 {
        font-size: 18px;
        margin-bottom: 20px;
        color: <?php echo AppColors::TEXT_PRIMARY; ?>;
        font-weight: 600;
    }

    .result-card {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        padding: 12px 0;
    }

    .result-card label {
        font-size: 14px;
        font-weight: 600;
        color: <?php echo AppColors::TEXT_PRIMARY; ?>;
    }

    .result-card .value {
        padding: 12px 16px;
        background-color: <?php echo AppColors::WHITE; ?>;
        border: 1px solid <?php echo AppColors::SUCCESS; ?>;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        color: <?php echo AppColors::SUCCESS; ?>;
        min-width: 180px;
        text-align: right;
    }

    .detail-section {
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px solid <?php echo AppColors::BORDER; ?>;
    }

    /* ─── TDP & MRP Display ──────────────────────────────────────────── */

    .tdp-display,
    .mrp-standar-display {
        padding: 20px;
        background-color: <?php echo AppColors::BACKGROUND; ?>;
        border: 1px solid <?php echo AppColors::BORDER; ?>;
        border-radius: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }

    .tdp-display label,
    .mrp-standar-display label {
        font-size: 14px;
        font-weight: 600;
        color: <?php echo AppColors::TEXT_PRIMARY; ?>;
    }

    .tdp-display .value,
    .mrp-standar-display .value {
        font-size: 14px;
        font-weight: 600;
        color: <?php echo AppColors::PRIMARY; ?>;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .mrp-standar-display.unavailable {
        background-color: #FEF3C7;
        border-color: #FCD34D;
    }

    .mrp-standar-display.unavailable .value {
        color: #D97706;
    }

    /* ─── MRP List ───────────────────────────────────────────────────── */

    .mrp-list-container {
        background-color: <?php echo AppColors::BACKGROUND; ?>;
        border: 1px solid <?php echo AppColors::BORDER; ?>;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 16px;
    }

    .mrp-list-container h3 {
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 12px;
        color: <?php echo AppColors::TEXT_PRIMARY; ?>;
    }

    .mrp-area-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 16px;
        margin-bottom: 8px;
        background-color: <?php echo AppColors::WHITE; ?>;
        border-radius: 8px;
        border: 1px solid <?php echo AppColors::BORDER; ?>;
    }

    .mrp-area-item:last-child {
        margin-bottom: 0;
    }

    .mrp-area-item.unavailable {
        background-color: #FEF3C7;
        border-color: #FCD34D;
    }

    .mrp-area-name {
        font-size: 14px;
        font-weight: 600;
        color: <?php echo AppColors::TEXT_PRIMARY; ?>;
    }

    .mrp-area-value {
        font-size: 14px;
        font-weight: 500;
        color: <?php echo AppColors::PRIMARY; ?>;
    }

    .mrp-area-item.unavailable .mrp-area-value {
        color: #D97706;
        font-style: italic;
    }

    /* ─── Selisih MRP Display ────────────────────────────────────────── */

    .selisih-mrp-display {
        padding: 20px;
        background-color: <?php echo AppColors::BACKGROUND; ?>;
        border: 1px solid <?php echo AppColors::BORDER; ?>;
        border-radius: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }

    .selisih-mrp-display label {
        font-size: 14px;
        font-weight: 600;
        color: <?php echo AppColors::TEXT_PRIMARY; ?>;
    }

    .selisih-mrp-display .value-group {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .selisih-mrp-display .percentage {
        padding: 8px 16px;
        background-color: #EFF6FF;
        border: 1px solid #93C5FD;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        color: <?php echo AppColors::PRIMARY; ?>;
    }

    .selisih-mrp-display .nominal {
        font-size: 14px;
        font-weight: 600;
        color: <?php echo AppColors::TEXT_PRIMARY; ?>;
    }

    .selisih-mrp-display.positive .percentage {
        background-color: #DCFCE7;
        border-color: #86EFAC;
        color: #16A34A;
    }

    .selisih-mrp-display.negative .percentage {
        background-color: #FEE2E2;
        border-color: #FCA5A5;
        color: #DC2626;
    }

    /* ─── Modal ──────────────────────────────────────────────────────── */

    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        justify-content: center;
        align-items: center;
    }

    .modal-overlay.active {
        display: flex;
    }

    .modal-content {
        background-color: white;
        border-radius: 12px;
        padding: 24px;
        max-width: 500px;
        width: 90%;
        max-height: 80vh;
        overflow-y: auto;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 1px solid #E5E7EB;
    }

    .modal-header h3 {
        font-size: 18px;
        font-weight: 600;
        color: <?php echo AppColors::TEXT_PRIMARY; ?>;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 24px;
        color: #6B7280;
        cursor: pointer;
        padding: 0;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        transition: all 0.2s;
    }

    .modal-close:hover {
        background-color: #F3F4F6;
        color: <?php echo AppColors::TEXT_PRIMARY; ?>;
    }

    .modal-unit-info {
        margin-bottom: 16px;
        font-size: 14px;
        color: <?php echo AppColors::TEXT_PRIMARY; ?>;
        font-weight: 500;
    }

    /* ─── Responsive: Tablet (max-width: 768px) ──────────────────────── */

    @media (max-width: 768px) {
        .header h1 {
            font-size: 20px;
            margin-bottom: 16px;
        }

        .header-bottom {
            flex-direction: row;
            justify-content: space-between;
        }

        .dealer-info {
            flex: 1;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .result-card {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }

        .result-card .value {
            width: 100%;
            text-align: left;
        }

        .btn-actions {
            flex-direction: column;
        }

        .btn {
            width: 100%;
        }
    }

    /* ─── Responsive: Mobile (max-width: 480px) ──────────────────────── */

    @media (max-width: 480px) {
        .container {
            padding: 12px;
        }

        .header {
            padding: 20px 16px 16px 16px;
        }

        .header h1 {
            font-size: 18px;
        }

        .dealer-name {
            font-size: 14px;
        }

        .dealer-area {
            font-size: 13px;
        }

        .form-section {
            padding: 16px;
        }
    }

</style>