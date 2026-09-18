<?php

ob_start();
session_start();

if (empty($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once 'config/appConstants.php';
require_once 'config/database.php';
require_once 'models/financingModels.php';
require_once 'providers/financingProvider.php';
require_once 'services/databaseService.php';
require_once 'utils/dropdownOptions.php';
require_once 'utils/currencyFormatter.php';
require_once 'services/dealerRecordService.php';

$dealerId = $_SESSION['dealer_id'] ?? null;

if (!isset($_SESSION['provider'], $_SESSION['provider_dealer_id']) || $_SESSION['provider_dealer_id'] !== $dealerId) {
    $provider = new FinancingProvider();
    if ($dealerId !== null) {
        $provider->loadDiscountRefund($dealerId);
    }
    $_SESSION['provider'] = serialize($provider);
    $_SESSION['provider_dealer_id'] = $dealerId;
} else {
    $provider = unserialize($_SESSION['provider']);
    if ($dealerId !== null && $provider->discountRefund === 0) {
        $provider->loadDiscountRefund($dealerId);
        $_SESSION['provider'] = serialize($provider);
    }
}

$isAjaxRequest = $_SERVER['REQUEST_METHOD'] === 'POST' && strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';

if ($isAjaxRequest) {
    $action = $_POST['action'] ?? '';
    $response = ['success' => true, 'data' => null];

    try {
        switch ($action) {
            case 'update_type_angsuran':
                $provider->updateTypeAngsuran($_POST['value']);
                break;

            case 'update_kode_plat':
                $provider->updateKodePlat($_POST['value']);
                $provider->updateInsuranceRegion(DropdownOptions::getInsuranceRegion($_POST['value']));
                break;

            case 'update_merk':
                $provider->updateMerk($_POST['value']);
                $response['data'] = [
                    'models' => DatabaseService::getModelsByBrand($_POST['value']),
                ];
                break;

            case 'update_model':
                $provider->updateModel($_POST['value']);
                $response['data'] = [
                    'types' => DatabaseService::getTypesByBrandAndModel($provider->finalCriteria->merk, $_POST['value']),
                ];
                break;

            case 'update_type':
            case 'update_tahun':
                if ($action === 'update_type') {
                    $provider->updateType($_POST['value']);
                } else {
                    $provider->updateTahun($_POST['value']);
                }

                $mrpDifferencePercentage = null;
                $mrpDifferenceNominal = null;

                if ($provider->mrpStandar > 0 && $provider->finalDetails->mrpPengajuan > 0) {
                    $nominalDiff = $provider->finalDetails->mrpPengajuan - $provider->mrpStandar;
                    $percentDiff = ($nominalDiff / $provider->mrpStandar) * 100;
                    $sign = $percentDiff >= 0 ? '+' : '';
                    $mrpDifferencePercentage = sprintf('%s%.1f %%', $sign, $percentDiff);
                    $mrpDifferenceNominal = CurrencyFormatter::formatWithSymbol($nominalDiff);
                }

                $mrpByAreaFormatted = [];
                if (!empty($provider->mrpByArea) && is_array($provider->mrpByArea)) {
                    foreach ($provider->mrpByArea as $area => $mrp) {
                        $mrpByAreaFormatted[$area] = $mrp !== null ? CurrencyFormatter::formatWithSymbol($mrp) : 'Tidak tersedia';
                    }
                }

                $unitInfo = '';
                if (!empty($provider->finalCriteria->merk) && !empty($provider->finalCriteria->model) && !empty($provider->finalCriteria->type) && !empty($provider->finalCriteria->tahun)) {
                    $unitInfo = sprintf('%s %s %s %s', $provider->finalCriteria->merk, $provider->finalCriteria->model, $provider->finalCriteria->type, $provider->finalCriteria->tahun);
                }

                $sessionArea = $_SESSION['area_new'] ?? null;
                $isHOUser = strtoupper(trim($sessionArea ?? '')) === 'HO';
                $showDetailMRPButton = $isHOUser && count($mrpByAreaFormatted) > 0;
                $mrpStandarFormatted = (!$isHOUser && $provider->mrpStandar) ? CurrencyFormatter::formatWithSymbol($provider->mrpStandar) : null;

                $response['data'] = [
                    'mrpStandar' => $provider->mrpStandar,
                    'mrpStandarMessage' => $provider->mrpStandarMessage,
                    'mrpStandarFormatted' => $mrpStandarFormatted,
                    'mrpDifferencePercentage' => $isHOUser ? null : $mrpDifferencePercentage,
                    'mrpDifferenceNominal' => $isHOUser ? null : $mrpDifferenceNominal,
                    'mrpByArea' => $mrpByAreaFormatted,
                    'hasMultipleArea' => count($mrpByAreaFormatted) > 0,
                    'showDetailMRPButton' => $showDetailMRPButton,
                    'isHOUser' => $isHOUser,
                    'unitInfo' => $unitInfo,
                ];
                break;

            case 'update_tenor':
                $provider->updateTenor($_POST['value']);
                break;

            case 'update_nego_bunga':
                $provider->updateNegoBunga($_POST['value']);
                break;

            case 'update_asuransi_unit':
                $provider->updateAsuransiUnit($_POST['value']);
                break;

            case 'update_pass_comm':
                $provider->updatePassComm($_POST['value']);
                break;

            case 'update_mrp':
                $provider->updateMRPPengajuan(CurrencyFormatter::parse($_POST['value']));
                $mrpDifferencePercentage = null;
                $mrpDifferenceNominal = null;
                $sessionArea = $_SESSION['area_new'] ?? null;
                $isHOUser = strtoupper(trim($sessionArea ?? '')) === 'HO';

                if (!$isHOUser && $provider->mrpStandar > 0 && $provider->finalDetails->mrpPengajuan > 0) {
                    $nominalDiff = $provider->finalDetails->mrpPengajuan - $provider->mrpStandar;
                    $percentDiff = ($nominalDiff / $provider->mrpStandar) * 100;
                    $sign = $percentDiff >= 0 ? '+' : '';
                    $mrpDifferencePercentage = sprintf('%s%.1f %%', $sign, $percentDiff);
                    $mrpDifferenceNominal = CurrencyFormatter::formatWithSymbol($nominalDiff);
                }

                $response['data'] = [
                    'mrpDifferencePercentage' => $mrpDifferencePercentage,
                    'mrpDifferenceNominal' => $mrpDifferenceNominal,
                    'isHOUser' => $isHOUser,
                ];
                break;

            case 'update_dp':
                $provider->updateDP(CurrencyFormatter::parse($_POST['value']));
                $response['data'] = [
                    'dpPercentage' => $provider->finalDetails->mrpPengajuan > 0 ? sprintf('%.1f %%', ($provider->finalDetails->dp / $provider->finalDetails->mrpPengajuan) * 100) : null,
                    'tdpPreview' => $provider->finalDetails->dp > 0 ? CurrencyFormatter::formatWithSymbol($provider->finalDetails->dp) : 'Rp 0',
                ];
                break;

            case 'calculate':
                $provider->calculate();

                if ($provider->calculationStatus) {
                    $sessionDealerId = $_SESSION['dealer_id'] ?? null;
                    $sessionDealerName = $_SESSION['dealer_name'] ?? null;

                    if ($sessionDealerId && $sessionDealerName && class_exists('DealerRecordService')) {
                        $recordResult = DealerRecordService::recordSimulation(
                            $sessionDealerId,
                            $sessionDealerName,
                            $_SESSION['branch'] ?? null,
                            $_SESSION['area_new'] ?? null,
                            $provider->finalCriteria,
                            $provider->finalDetails,
                            $provider->mrpStandar,
                            $provider->finalResult
                        );

                        if (!$recordResult['success']) {
                            error_log(sprintf('FAILED TO RECORD DEALER SIMULATION: %s', $recordResult['message']));
                        }
                    }
                }

                $response['data'] = [
                    'calculationStatus' => $provider->calculationStatus,
                    'errorValidation' => $provider->errorValidation,
                    'result' => $provider->calculationStatus ? $provider->finalResult->toArray() : null,
                ];
                break;

            case 'reset':
                $provider->resetAll();
                if ($dealerId !== null) {
                    $provider->loadDiscountRefund($dealerId);
                }
                break;
        }

        $_SESSION['provider'] = serialize($provider);
        $response['provider'] = [
            'canCalculate' => $provider->canCalculate(),
            'calculationStatus' => $provider->calculationStatus,
            'errorValidation' => $provider->errorValidation,
        ];
    } catch (Throwable $e) {
        $response['success'] = false;
        $response['error'] = sprintf('Terjadi kesalahan sistem: %s', $e->getMessage());
        error_log(sprintf('AJAX FATAL ERROR: %s in %s on line %d', $e->getMessage(), $e->getFile(), $e->getLine()));
    }

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response, JSON_THROW_ON_ERROR);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'update_type_angsuran':
            $provider->updateTypeAngsuran($_POST['value']);
            break;
        case 'update_kode_plat':
            $provider->updateKodePlat($_POST['value']);
            $provider->updateInsuranceRegion(DropdownOptions::getInsuranceRegion($_POST['value']));
            break;
        case 'update_merk':
            $provider->updateMerk($_POST['value']);
            break;
        case 'update_model':
            $provider->updateModel($_POST['value']);
            break;
        case 'update_type':
            $provider->updateType($_POST['value']);
            break;
        case 'update_tahun':
            $provider->updateTahun($_POST['value']);
            break;
        case 'update_tenor':
            $provider->updateTenor($_POST['value']);
            break;
        case 'update_nego_bunga':
            $provider->updateNegoBunga($_POST['value']);
            break;
        case 'update_asuransi_unit':
            $provider->updateAsuransiUnit($_POST['value']);
            break;
        case 'update_pass_comm':
            $provider->updatePassComm($_POST['value']);
            break;
        case 'update_mrp':
            $provider->updateMRPPengajuan(CurrencyFormatter::parse($_POST['value']));
            break;
        case 'update_dp':
            $provider->updateDP(CurrencyFormatter::parse($_POST['value']));
            break;
        case 'calculate':
            $provider->calculate();
            if ($provider->calculationStatus) {
                $sessionDealerId = $_SESSION['dealer_id'] ?? null;
                $sessionDealerName = $_SESSION['dealer_name'] ?? null;

                if ($sessionDealerId && $sessionDealerName && class_exists('DealerRecordService')) {
                    $recordResult = DealerRecordService::recordSimulation(
                        $sessionDealerId,
                        $sessionDealerName,
                        $_SESSION['branch'] ?? null,
                        $_SESSION['area_new'] ?? null,
                        $provider->finalCriteria,
                        $provider->finalDetails,
                        $provider->mrpStandar,
                        $provider->finalResult
                    );

                    if (!$recordResult['success']) {
                        error_log(sprintf('FAILED TO RECORD DEALER SIMULATION: %s', $recordResult['message']));
                    }
                }
            }
            break;
        case 'reset':
            $provider->resetAll();
            if ($dealerId !== null) {
                $provider->loadDiscountRefund($dealerId);
            }
            break;
    }

    $_SESSION['provider'] = serialize($provider);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$brands = DatabaseService::getBrands();
$models = !empty($provider->finalCriteria->merk) ? DatabaseService::getModelsByBrand($provider->finalCriteria->merk) : [];
$types = !empty($provider->finalCriteria->merk) && !empty($provider->finalCriteria->model)
    ? DatabaseService::getTypesByBrandAndModel($provider->finalCriteria->merk, $provider->finalCriteria->model)
    : [];

$dpPercentage = null;
if ($provider->finalDetails->mrpPengajuan > 0 && $provider->finalDetails->dp > 0) {
    $dpPercentage = sprintf('%.1f %%', ($provider->finalDetails->dp / $provider->finalDetails->mrpPengajuan) * 100);
}

$mrpDifferencePercentage = null;
$mrpDifferenceNominal = null;
if ($provider->mrpStandar > 0 && $provider->finalDetails->mrpPengajuan > 0) {
    $nominalDiff = $provider->finalDetails->mrpPengajuan - $provider->mrpStandar;
    $percentDiff = ($nominalDiff / $provider->mrpStandar) * 100;
    $sign = $percentDiff >= 0 ? '+' : '';
    $mrpDifferencePercentage = sprintf('%s%.1f %%', $sign, $percentDiff);
    $mrpDifferenceNominal = CurrencyFormatter::formatWithSymbol($nominalDiff);
}

$mrpByAreaFormatted = [];
if (!empty($provider->mrpByArea) && is_array($provider->mrpByArea)) {
    foreach ($provider->mrpByArea as $areaKey => $mrpValue) {
        $mrpByAreaFormatted[$areaKey] = $mrpValue !== null ? CurrencyFormatter::formatWithSymbol($mrpValue) : 'Tidak tersedia';
    }
}

$area = $_SESSION['area_new'] ?? null;
$isHOUser = strtoupper(trim($area ?? '')) === 'HO';
$hasMultipleArea = count($mrpByAreaFormatted) > 0;
$showDetailMRPButton = $isHOUser && $hasMultipleArea;

$shouldShowExtendedResults = false;
if ($area !== null) {
    $shouldShowExtendedResults = in_array(strtoupper(trim($area)), ['JATIM', 'SUMBAGSEL', 'SUMBAGUT&TENG'], true);
}

require_once 'assets/views/financingForm.php';