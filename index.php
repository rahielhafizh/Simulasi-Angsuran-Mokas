<?php

session_start();

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once 'config/appColors.php';
require_once 'config/appConstants.php';
require_once 'config/database.php';
require_once 'models/financingCriteria.php';
require_once 'models/financingDetails.php';
require_once 'models/calculationResult.php';
require_once 'providers/financingProvider.php';
require_once 'services/databaseService.php';
require_once 'services/dealerRecordService.php';
require_once 'utils/dropdownOptions.php';
require_once 'utils/currencyFormatter.php';

$dealerId = $_SESSION['dealer_id'] ?? null;

if (!isset($_SESSION['provider']) || !isset($_SESSION['provider_dealer_id']) || $_SESSION['provider_dealer_id'] !== $dealerId) {
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');

    $action = $_POST['action'] ?? '';
    $response = ['success' => true, 'data' => null];

    try {
        switch ($action) {
            case 'update_type_angsuran':
                $provider->updateTypeAngsuran($_POST['value']);
                break;

            case 'update_kode_plat':
                $provider->updateKodePlat($_POST['value']);
                $region = DropdownOptions::getInsuranceRegion($_POST['value']);
                $provider->updateInsuranceRegion($region);
                break;

            case 'update_merk':
                $provider->updateMerk($_POST['value']);
                $response['data'] = [
                    'models' => DatabaseService::getModelsByBrand($_POST['value'])
                ];
                break;

            case 'update_model':
                $provider->updateModel($_POST['value']);
                $response['data'] = [
                    'types' => DatabaseService::getTypesByBrandAndModel($provider->finalCriteria->merk, $_POST['value'])
                ];
                break;

            case 'update_type':
                $provider->updateType($_POST['value']);

                $mrpDifferencePercentage = null;
                $mrpDifferenceNominal = null;
                if ($provider->mrpStandar > 0 && $provider->finalDetails->mrpPengajuan > 0) {
                    $nominalDiff = $provider->finalDetails->mrpPengajuan - $provider->mrpStandar;
                    $percentDiff = ($nominalDiff / $provider->mrpStandar) * 100;
                    $sign = $percentDiff >= 0 ? '+' : '';
                    $mrpDifferencePercentage = $sign . number_format($percentDiff, 1) . ' %';
                    $mrpDifferenceNominal = CurrencyFormatter::formatWithSymbol($nominalDiff);
                }

                $mrpByAreaFormatted = [];
                if (isset($provider->mrpByArea) && is_array($provider->mrpByArea)) {
                    foreach ($provider->mrpByArea as $area => $mrp) {
                        $mrpByAreaFormatted[$area] = $mrp !== null ? CurrencyFormatter::formatWithSymbol($mrp) : 'Tidak tersedia';
                    }
                }

                $unitInfo = '';
                if (!empty($provider->finalCriteria->merk) && !empty($provider->finalCriteria->model) && !empty($provider->finalCriteria->type) && !empty($provider->finalCriteria->tahun)) {
                    $unitInfo = $provider->finalCriteria->merk . ' ' . $provider->finalCriteria->model . ' ' . $provider->finalCriteria->type . ' ' . $provider->finalCriteria->tahun;
                }

                $area = $_SESSION['area_new'] ?? null;
                $isHOUser = strtoupper(trim($area ?? '')) === 'HO';
                $showDetailMRPButton = $isHOUser && count($mrpByAreaFormatted) > 0;
                $mrpStandarFormatted = null;
                if (!$isHOUser && $provider->mrpStandar) {
                    $mrpStandarFormatted = CurrencyFormatter::formatWithSymbol($provider->mrpStandar);
                }

                $responseMRPDifferencePercentage = $isHOUser ? null : $mrpDifferencePercentage;
                $responseMRPDifferenceNominal = $isHOUser ? null : $mrpDifferenceNominal;

                $response['data'] = [
                    'mrpStandar' => $provider->mrpStandar,
                    'mrpStandarMessage' => $provider->mrpStandarMessage,
                    'mrpStandarFormatted' => $mrpStandarFormatted,
                    'mrpDifferencePercentage' => $responseMRPDifferencePercentage,
                    'mrpDifferenceNominal' => $responseMRPDifferenceNominal,
                    'mrpByArea' => $mrpByAreaFormatted,
                    'hasMultipleArea' => count($mrpByAreaFormatted) > 0,
                    'showDetailMRPButton' => $showDetailMRPButton,
                    'isHOUser' => $isHOUser,
                    'unitInfo' => $unitInfo
                ];
                break;

            case 'update_tahun':
                $provider->updateTahun($_POST['value']);

                $mrpDifferencePercentage = null;
                $mrpDifferenceNominal = null;
                if ($provider->mrpStandar > 0 && $provider->finalDetails->mrpPengajuan > 0) {
                    $nominalDiff = $provider->finalDetails->mrpPengajuan - $provider->mrpStandar;
                    $percentDiff = ($nominalDiff / $provider->mrpStandar) * 100;
                    $sign = $percentDiff >= 0 ? '+' : '';
                    $mrpDifferencePercentage = $sign . number_format($percentDiff, 1) . ' %';
                    $mrpDifferenceNominal = CurrencyFormatter::formatWithSymbol($nominalDiff);
                }

                $mrpByAreaFormatted = [];
                if (isset($provider->mrpByArea) && is_array($provider->mrpByArea)) {
                    foreach ($provider->mrpByArea as $area => $mrp) {
                        $mrpByAreaFormatted[$area] = $mrp !== null ? CurrencyFormatter::formatWithSymbol($mrp) : 'Tidak tersedia';
                    }
                }

                $unitInfo = '';
                if (!empty($provider->finalCriteria->merk) && !empty($provider->finalCriteria->model) && !empty($provider->finalCriteria->type) && !empty($provider->finalCriteria->tahun)) {
                    $unitInfo = $provider->finalCriteria->merk . ' ' . $provider->finalCriteria->model . ' ' . $provider->finalCriteria->type . ' ' . $provider->finalCriteria->tahun;
                }

                $area = $_SESSION['area_new'] ?? null;
                $isHOUser = strtoupper(trim($area ?? '')) === 'HO';
                $showDetailMRPButton = $isHOUser && count($mrpByAreaFormatted) > 0;
                $mrpStandarFormatted = null;
                if (!$isHOUser && $provider->mrpStandar) {
                    $mrpStandarFormatted = CurrencyFormatter::formatWithSymbol($provider->mrpStandar);
                }

                $responseMRPDifferencePercentage = $isHOUser ? null : $mrpDifferencePercentage;
                $responseMRPDifferenceNominal = $isHOUser ? null : $mrpDifferenceNominal;

                $response['data'] = [
                    'mrpStandar' => $provider->mrpStandar,
                    'mrpStandarMessage' => $provider->mrpStandarMessage,
                    'mrpStandarFormatted' => $mrpStandarFormatted,
                    'mrpDifferencePercentage' => $responseMRPDifferencePercentage,
                    'mrpDifferenceNominal' => $responseMRPDifferenceNominal,
                    'mrpByArea' => $mrpByAreaFormatted,
                    'hasMultipleArea' => count($mrpByAreaFormatted) > 0,
                    'showDetailMRPButton' => $showDetailMRPButton,
                    'isHOUser' => $isHOUser,
                    'unitInfo' => $unitInfo
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
                $area = $_SESSION['area_new'] ?? null;
                $isHOUser = strtoupper(trim($area ?? '')) === 'HO';

                if (!$isHOUser && $provider->mrpStandar > 0 && $provider->finalDetails->mrpPengajuan > 0) {
                    $nominalDiff = $provider->finalDetails->mrpPengajuan - $provider->mrpStandar;
                    $percentDiff = ($nominalDiff / $provider->mrpStandar) * 100;
                    $sign = $percentDiff >= 0 ? '+' : '';
                    $mrpDifferencePercentage = $sign . number_format($percentDiff, 1) . ' %';
                    $mrpDifferenceNominal = CurrencyFormatter::formatWithSymbol($nominalDiff);
                }

                $response['data'] = [
                    'mrpDifferencePercentage' => $mrpDifferencePercentage,
                    'mrpDifferenceNominal' => $mrpDifferenceNominal,
                    'isHOUser' => $isHOUser
                ];
                break;

            case 'update_dp':
                $provider->updateDP(CurrencyFormatter::parse($_POST['value']));
                $response['data'] = [
                    'dpPercentage' => $provider->finalDetails->mrpPengajuan > 0 ? number_format(($provider->finalDetails->dp / $provider->finalDetails->mrpPengajuan) * 100, 1) . ' %' : null,
                    'tdpPreview' => $provider->finalDetails->dp > 0 ? CurrencyFormatter::formatWithSymbol($provider->finalDetails->dp) : 'Rp 0'
                ];
                break;

            case 'calculate':
                $provider->calculate();

                if ($provider->calculationStatus) {
                    $dealerId = $_SESSION['dealer_id'] ?? null;
                    $dealerName = $_SESSION['dealer_name'] ?? null;

                    if ($dealerId && $dealerName) {
                        $dealerBranch = $_SESSION['branch'] ?? null;
                        $dealerArea = $_SESSION['area_new'] ?? null;

                        $recordResult = DealerRecordService::recordSimulation(
                            $dealerId,
                            $dealerName,
                            $dealerBranch,
                            $dealerArea,
                            $provider->finalCriteria,
                            $provider->finalDetails,
                            $provider->mrpStandar,
                            $provider->finalResult
                        );

                        if (!$recordResult['success']) {
                            error_log('FAILED TO RECORD DEALER SIMULATION: ' . $recordResult['message']);
                        }
                    }
                }

                $response['data'] = [
                    'calculationStatus' => $provider->calculationStatus,
                    'errorValidation' => $provider->errorValidation,
                    'result' => $provider->calculationStatus ? $provider->finalResult->toArray() : null
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
            'errorValidation' => $provider->errorValidation
        ];
    } catch (Exception $e) {
        $response['success'] = false;
        $response['error'] = $e->getMessage();
    }

    echo json_encode($response);
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
            $region = DropdownOptions::getInsuranceRegion($_POST['value']);
            $provider->updateInsuranceRegion($region);
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
                $dealerId = $_SESSION['dealer_id'] ?? null;
                $dealerName = $_SESSION['dealer_name'] ?? null;

                if ($dealerId && $dealerName) {
                    $dealerBranch = $_SESSION['branch'] ?? null;
                    $dealerArea = $_SESSION['area_new'] ?? null;

                    $recordResult = DealerRecordService::recordSimulation(
                        $dealerId,
                        $dealerName,
                        $dealerBranch,
                        $dealerArea,
                        $provider->finalCriteria,
                        $provider->finalDetails,
                        $provider->mrpStandar,
                        $provider->finalResult
                    );

                    if (!$recordResult['success']) {
                        error_log('FAILED TO RECORD DEALER SIMULATION: ' . $recordResult['message']);
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
$models = [];
$types = [];

if (!empty($provider->finalCriteria->merk)) {
    $models = DatabaseService::getModelsByBrand($provider->finalCriteria->merk);
}

if (!empty($provider->finalCriteria->merk) && !empty($provider->finalCriteria->model)) {
    $types = DatabaseService::getTypesByBrandAndModel(
        $provider->finalCriteria->merk,
        $provider->finalCriteria->model
    );
}

$dpPercentage = null;
if ($provider->finalDetails->mrpPengajuan > 0 && $provider->finalDetails->dp > 0) {
    $percentage = ($provider->finalDetails->dp / $provider->finalDetails->mrpPengajuan) * 100;
    $dpPercentage = number_format($percentage, 1) . ' %';
}

$mrpDifferencePercentage = null;
$mrpDifferenceNominal = null;
if ($provider->mrpStandar > 0 && $provider->finalDetails->mrpPengajuan > 0) {
    $nominalDiff = $provider->finalDetails->mrpPengajuan - $provider->mrpStandar;
    $percentDiff = ($nominalDiff / $provider->mrpStandar) * 100;
    $sign = $percentDiff >= 0 ? '+' : '';
    $mrpDifferencePercentage = $sign . number_format($percentDiff, 1) . ' %';
    $mrpDifferenceNominal = CurrencyFormatter::formatWithSymbol($nominalDiff);
}

$mrpByAreaFormatted = [];
$hasMultipleArea = false;
$isHOUser = false;
$area = $_SESSION['area_new'] ?? null;
$isHOUser = strtoupper(trim($area ?? '')) === 'HO';

if (isset($provider->mrpByArea) && is_array($provider->mrpByArea) && count($provider->mrpByArea) > 0) {
    $hasMultipleArea = true;
    foreach ($provider->mrpByArea as $area => $mrp) {
        $mrpByAreaFormatted[$area] = $mrp !== null ? CurrencyFormatter::formatWithSymbol($mrp) : 'Tidak tersedia';
    }
}

$showDetailMRPButton = $isHOUser && count($mrpByAreaFormatted) > 0;

$shouldShowExtendedResults = false;
$areaNew = $_SESSION['area_new'] ?? null;
if ($areaNew !== null) {
    $areaNewUpper = strtoupper(trim($areaNew));
    $shouldShowExtendedResults = in_array($areaNewUpper, ['JATIM', 'SUMBAGSEL', 'SUMBAGUT&TENG'], true);
}

require_once 'assets/views/financing-form.php';
