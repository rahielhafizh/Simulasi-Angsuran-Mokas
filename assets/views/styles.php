<style>
    :root {
        --app-primary: #2563EB;
        --app-secondary: #6B7280;
        --app-success: #10B981;
        --app-danger: #EF4444;
        --app-warning: #F59E0B;
        --app-info: #3B82F6;
        --app-background: #F3F4F6;
        --app-form-background: #FAFAFA;
        --app-white: #FFFFFF;
        --app-text-primary: #1F2937;
        --app-text-secondary: #6B7280;
        --app-text-light: #9CA3AF;
        --app-border: #E5E7EB;
        --app-disabled: #F3F4F6;
    }

    /* ─── Reset & Base ───────────────────────────────────────────────── */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        background-color: var(--app-background);
        color: var(--app-text-primary);
        line-height: 1.6;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    /* ─── Login Page ─────────────────────────────────────────────────── */
    .login-page-body {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
    }

    .login-container {
        background-color: var(--app-white);
        padding: 2.5rem;
        border-radius: 1rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        width: 100%;
        max-width: 400px;
    }

    .login-header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .login-header h1 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--app-text-primary);
        margin-bottom: 0.5rem;
    }

    .login-header p {
        color: var(--app-text-secondary);
        font-size: 0.875rem;
    }

    .login-form-group {
        margin-bottom: 1.5rem;
    }

    .login-form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--app-text-primary);
    }

    .login-form-input {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid var(--app-border);
        border-radius: 0.5rem;
        font-size: 1rem;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .login-form-input:focus {
        outline: none;
        border-color: var(--app-primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .login-btn-submit {
        width: 100%;
        padding: 0.75rem;
        background-color: var(--app-primary);
        color: var(--app-white);
        border: none;
        border-radius: 0.5rem;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .login-btn-submit:hover:not(:disabled) {
        background-color: #1D4ED8;
    }

    .login-btn-submit:disabled {
        background-color: var(--app-text-secondary);
        cursor: not-allowed;
    }

    .login-loading {
        display: none;
        text-align: center;
        margin-top: 0.5rem;
        color: var(--app-text-secondary);
        font-size: 0.875rem;
    }

    .login-loading.active {
        display: block;
    }

    /* ─── Header ─────────────────────────────────────────────────────── */
    .header {
        background-color: var(--app-white);
        padding: 24px 20px 20px 20px;
        margin-bottom: 16px;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        border: 1px solid #E5E7EB;
    }

    .header h1 {
        font-size: 24px;
        color: var(--app-text-primary);
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
        color: var(--app-text-primary);
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
        background-color: var(--app-text-primary);
        border-radius: 2px;
    }

    .dropdown-menu {
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        background-color: var(--app-white);
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
        color: var(--app-text-primary);
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
        background-color: var(--app-white);
        padding: 20px;
        margin-bottom: 20px;
        border-radius: 12px;
        border: 1px solid var(--app-border);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.06);
    }

    .vehicle-check-title {
        font-size: 16px;
        font-weight: 700;
        text-align: center;
        color: var(--app-text-primary);
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
        background-color: var(--app-background);
        color: var(--app-text-primary);
        transition: border-color 0.2s, background-color 0.2s;
        box-sizing: border-box;
    }

    .vehicle-check-input::placeholder {
        color: var(--app-text-light);
        font-size: 13px;
    }

    .vehicle-check-input:focus {
        outline: none;
        border-color: var(--app-info);
        background-color: var(--app-white);
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
        color: var(--app-white);
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
        border-color: var(--app-success);
        color: var(--app-success);
    }

    .vehicle-check-result.status-danger {
        background-color: #FEF2F2;
        border-color: var(--app-danger);
        color: var(--app-danger);
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
        background-color: var(--app-form-background);
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
        color: var(--app-text-primary);
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
        color: var(--app-text-primary);
    }

    /* Dipertahankan untuk fallback native elements (misal input text/number) */
    .form-group select,
    .form-group input[type="text"],
    .form-group input[type="number"] {
        width: 100%;
        height: 52px;
        padding: 0 16px;
        border: 1px solid var(--app-border);
        border-radius: 8px;
        font-size: 14px;
        background-color: var(--app-white);
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

    /* ─── Custom UI Dropdown (Modern Styling) ────────────────────────── */
    .custom-select-container {
        position: relative;
        width: 100%;
    }

    .custom-select-trigger {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        height: 52px;
        padding: 0 16px;
        border: 1px solid var(--app-border);
        border-radius: 8px;
        font-size: 14px;
        background-color: var(--app-white);
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        user-select: none;
        outline: none;
    }

    .custom-select-text {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        padding-right: 12px;
    }

    .custom-select-trigger:hover:not(.disabled) {
        border-color: #9CA3AF;
    }

    .custom-select-trigger:focus-visible:not(.disabled) {
        border-color: var(--app-info);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
    }

    .custom-select-trigger.active {
        border-color: var(--app-info);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .custom-select-trigger.disabled {
        background-color: var(--app-disabled);
        cursor: not-allowed;
        opacity: 0.6;
        color: var(--app-text-light);
    }

    .custom-select-arrow {
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        flex-shrink: 0;
    }

    .custom-select-trigger.active .custom-select-arrow {
        transform: rotate(180deg);
    }

    .custom-select-arrow svg {
        width: 18px;
        height: 18px;
        stroke: var(--app-text-secondary);
    }

    .custom-select-options {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        right: 0;
        background-color: var(--app-white);
        border: 1px solid var(--app-border);
        border-radius: 8px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        max-height: 260px;
        overflow-y: auto;
        z-index: 50;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .custom-select-options.show {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .custom-select-options::-webkit-scrollbar {
        width: 6px;
    }

    .custom-select-options::-webkit-scrollbar-track {
        background: transparent;
        margin: 4px 0;
    }

    .custom-select-options::-webkit-scrollbar-thumb {
        background-color: #D1D5DB;
        border-radius: 8px;
    }

    .custom-select-options::-webkit-scrollbar-thumb:hover {
        background-color: #9CA3AF;
    }

    .custom-select-option {
        padding: 12px 16px;
        font-size: 14px;
        color: var(--app-text-primary);
        cursor: pointer;
        transition: background-color 0.15s ease, color 0.15s ease;
        display: flex;
        align-items: center;
        line-height: 1.4;
        word-break: break-word;
    }

    .custom-select-option:first-child {
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
    }

    .custom-select-option:last-child {
        border-bottom-left-radius: 8px;
        border-bottom-right-radius: 8px;
    }

    .custom-select-option:hover {
        background-color: #EFF6FF;
        color: var(--app-info);
    }

    .custom-select-option.selected {
        background-color: #E0E7FF;
        color: #1E40AF;
        font-weight: 600;
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
        color: var(--app-primary);
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
        background-color: var(--app-primary);
        color: white;
    }

    .btn-primary:hover:not(:disabled) {
        background-color: #1D4ED8;
    }

    .btn-primary:disabled {
        background-color: var(--app-disabled);
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
        background-color: var(--app-white);
        padding: 20px;
        border-radius: 12px;
        border: 1px solid var(--app-border);
        margin-bottom: 20px;
    }

    .result-section h2 {
        font-size: 18px;
        margin-bottom: 20px;
        color: var(--app-text-primary);
        font-weight: 600;
    }

    .result-card {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        padding: 12px 0;
        border-bottom: 1px dashed #E5E7EB;
    }

    .result-card:last-child {
        border-bottom: none;
    }

    .result-card label {
        font-size: 14px;
        font-weight: 600;
        color: var(--app-text-primary);
    }

    .result-card .value {
        padding: 12px 16px;
        background-color: var(--app-white);
        border: 1px solid var(--app-success);
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        color: var(--app-success);
        min-width: 180px;
        text-align: right;
    }

    .detail-section {
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px solid var(--app-border);
    }

    /* ─── TDP & MRP Display ──────────────────────────────────────────── */
    .tdp-display,
    .mrp-standar-display {
        padding: 20px;
        background-color: var(--app-background);
        border: 1px solid var(--app-border);
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
        color: var(--app-text-primary);
    }

    .tdp-display .value,
    .mrp-standar-display .value {
        font-size: 14px;
        font-weight: 600;
        color: var(--app-primary);
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
        background-color: var(--app-background);
        border: 1px solid var(--app-border);
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 16px;
    }

    .mrp-list-container h3 {
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 12px;
        color: var(--app-text-primary);
    }

    .mrp-area-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 16px;
        margin-bottom: 8px;
        background-color: var(--app-white);
        border-radius: 8px;
        border: 1px solid var(--app-border);
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
        color: var(--app-text-primary);
    }

    .mrp-area-value {
        font-size: 14px;
        font-weight: 500;
        color: var(--app-primary);
    }

    .mrp-area-item.unavailable .mrp-area-value {
        color: #D97706;
        font-style: italic;
    }

    /* ─── Selisih MRP Display ────────────────────────────────────────── */
    .selisih-mrp-display {
        padding: 20px;
        background-color: var(--app-background);
        border: 1px solid var(--app-border);
        border-radius: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }

    .selisih-mrp-display label {
        font-size: 14px;
        font-weight: 600;
        color: var(--app-text-primary);
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
        color: var(--app-primary);
    }

    .selisih-mrp-display .nominal {
        font-size: 14px;
        font-weight: 600;
        color: var(--app-text-primary);
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
        color: var(--app-text-primary);
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
        color: var(--app-text-primary);
    }

    .modal-unit-info {
        margin-bottom: 16px;
        font-size: 14px;
        color: var(--app-text-primary);
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