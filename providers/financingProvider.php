<?php

require_once __DIR__ . '/../models/financingCriteria.php';
require_once __DIR__ . '/../models/financingDetails.php';
require_once __DIR__ . '/../models/calculationResult.php';
require_once __DIR__ . '/../services/calculationService.php';
require_once __DIR__ . '/../services/databaseService.php';

class FinancingProvider
{
    public $finalCriteria;
    public $finalDetails;
    public $finalResult;
    public $calculationStatus;
    public $errorValidation;
    public $insuranceRegion;
    public $mrpStandar;
    public $mrpStandarMessage;
    public $mrpByArea;
    public $discountRefund;
    public $dealerId;

    public function __construct()
    {
        $this->finalCriteria = new FinancingCriteria();
        $this->finalDetails = new FinancingDetails();
        $this->finalResult = CalculationResult::empty();
        $this->calculationStatus = false;
        $this->errorValidation = null;
        $this->insuranceRegion = null;
        $this->mrpStandar = null;
        $this->mrpStandarMessage = null;
        $this->mrpByArea = [];
        $this->discountRefund = 0;
        $this->dealerId = null;
    }

    public function updateTypeAngsuran($value)
    {
        $this->finalCriteria->typeAngsuran = $value;
        $this->resetCalculation();
    }

    public function updateKodePlat($value)
    {
        $this->finalCriteria->kodePlat = $value;
        $this->resetCalculation();
    }

    public function updateMerk($value)
    {
        $this->finalCriteria->merk = $value;
        $this->finalCriteria->model = null;
        $this->finalCriteria->type = null;
        $this->resetUnitClassification();
        $this->resetMRPStandar();
        $this->resetCalculation();
    }

    public function updateModel($value)
    {
        $this->finalCriteria->model = $value;
        $this->finalCriteria->type = null;
        $this->resetUnitClassification();
        $this->resetMRPStandar();
        $this->resetCalculation();
    }

    public function updateType($value)
    {
        $this->finalCriteria->type = $value;
        $this->loadUnitCategoryAndSegment($value);
        $this->updateMRPStandar();
        $this->resetCalculation();
    }

    public function updateTahun($value)
    {
        $this->finalCriteria->tahun = $value;
        $this->updateMRPStandar();
        $this->resetCalculation();
    }

    public function updateTenor($value)
    {
        $this->finalCriteria->tenor = $value;
        $this->resetCalculation();
    }

    public function updateNegoBunga($value)
    {
        $this->finalCriteria->negoBunga = $value;
        $this->resetCalculation();
    }

    public function updateAsuransiUnit($value)
    {
        $this->finalCriteria->asuransiUnit = $value;
        $this->resetCalculation();
    }

    public function updatePassComm($value)
    {
        $this->finalCriteria->passComm = $value;
        $this->resetCalculation();
    }

    public function updateInsuranceRegion($value)
    {
        $this->insuranceRegion = $value;
        $this->resetCalculation();
    }

    public function updateMRPPengajuan($value)
    {
        $this->finalDetails->mrpPengajuan = $value;
        $this->resetCalculation();
    }

    public function updateDP($value)
    {
        $this->finalDetails->dp = $value;
        $this->resetCalculation();
    }

    public function loadDiscountRefund($dealerId)
    {
        if (empty($dealerId)) {
            $this->discountRefund = 0;
            $this->dealerId = null;
            error_log('PROVIDER - DEALER_ID IS EMPTY, SETTING DISCOUNT TO 0');
            return;
        }

        try {
            $this->dealerId = $dealerId;
            $this->discountRefund = DatabaseService::getDealerDiscountRefund($dealerId);
            error_log('PROVIDER - LOADED DISCOUNT FOR DEALER_ID: ' . $dealerId . ' | DISCOUNT: ' . $this->discountRefund);
        } catch (Exception $e) {
            error_log('ERROR LOADING DISCOUNT REFUND IN PROVIDER: ' . $e->getMessage());
            $this->discountRefund = 0;
            $this->dealerId = null;
        }
    }

    private function loadUnitCategoryAndSegment($type)
    {
        if (empty($type)) {
            $this->resetUnitClassification();
            return;
        }

        try {
            $result = DatabaseService::getUnitCategoryAndSegment($type);

            if ($result !== null) {
                $this->finalCriteria->unitCategoryName = $result['unitCategoryName'];
                $this->finalCriteria->unitSegmentName = $result['unitSegmentName'];
                error_log('PROVIDER - LOADED UNIT CLASSIFICATION FOR TYPE: ' . $type
                    . ' | CATEGORY: ' . ($result['unitCategoryName'] ?? 'NULL')
                    . ' | SEGMENT: ' . ($result['unitSegmentName'] ?? 'NULL'));
            } else {
                $this->resetUnitClassification();
                error_log('PROVIDER - NO UNIT CLASSIFICATION FOUND FOR TYPE: ' . $type);
            }
        } catch (Exception $e) {
            error_log('ERROR LOADING UNIT CLASSIFICATION: ' . $e->getMessage());
            $this->resetUnitClassification();
        }
    }

    private function resetUnitClassification()
    {
        $this->finalCriteria->unitCategoryName = null;
        $this->finalCriteria->unitSegmentName = null;
    }

    private function updateMRPStandar()
    {
        if (empty($this->finalCriteria->type) || empty($this->finalCriteria->tahun)) {
            error_log('UPDATE MRP STANDAR - Type or Year is empty');
            $this->resetMRPStandar();
            return;
        }

        try {
            $area = $_SESSION['area_new'] ?? null;
            $isHOUser = strtoupper(trim($area)) === 'HO';

            error_log('UPDATE MRP STANDAR - AREA: ' . ($area ?? 'NULL') . ' | IS_HO: ' . ($isHOUser ? 'YES' : 'NO'));
            error_log('UPDATE MRP STANDAR - TYPE: ' . $this->finalCriteria->type . ' | YEAR: ' . $this->finalCriteria->tahun);

            if ($isHOUser) {
                error_log('UPDATE MRP STANDAR - HO User detected, fetching all MRP by area');

                $this->mrpByArea = DatabaseService::getAllMRPByArea(
                    $this->finalCriteria->type,
                    $this->finalCriteria->tahun
                );

                error_log('UPDATE MRP STANDAR - Found ' . count($this->mrpByArea) . ' areas with MRP data');

                if (!empty($this->mrpByArea)) {
                    $validMRPs = array_filter($this->mrpByArea, function ($mrp) {
                        return $mrp !== null && $mrp > 0;
                    });

                    if (!empty($validMRPs)) {
                        $this->mrpStandar = max($validMRPs);
                        $this->mrpStandarMessage = null;
                        error_log('UPDATE MRP STANDAR - HO User MRP STANDAR set to: ' . $this->mrpStandar);
                    } else {
                        $this->mrpStandar = null;
                        $this->mrpStandarMessage = 'MRP tahun ' . $this->finalCriteria->tahun . ' tidak tersedia';
                        error_log('UPDATE MRP STANDAR - HO User no valid MRP found');
                    }
                } else {
                    $this->mrpStandar = null;
                    $this->mrpStandarMessage = 'MRP tahun ' . $this->finalCriteria->tahun . ' tidak tersedia';
                    error_log('UPDATE MRP STANDAR - HO User no MRP data found at all');
                }
            } else {
                error_log('UPDATE MRP STANDAR - Regular user, fetching MRP for area: ' . $area);

                $mrpData = DatabaseService::getMRPData(
                    $this->finalCriteria->merk,
                    $this->finalCriteria->model,
                    $this->finalCriteria->type,
                    $this->finalCriteria->tahun,
                    $area
                );

                if ($mrpData !== null) {
                    $this->mrpStandar = $mrpData['mrpStandar'];
                    $this->mrpStandarMessage = $mrpData['message'];
                    error_log('UPDATE MRP STANDAR - Regular user MRP STANDAR: ' . ($this->mrpStandar ?? 'NULL'));
                } else {
                    $this->resetMRPStandar();
                    error_log('UPDATE MRP STANDAR - Regular user no MRP data found');
                }

                $this->mrpByArea = [];
            }
        } catch (Exception $e) {
            error_log('ERROR UPDATE MRP STANDAR - ' . $e->getMessage());
            $this->resetMRPStandar();
        }
    }

    private function resetMRPStandar()
    {
        $this->mrpStandar = null;
        $this->mrpStandarMessage = null;
        $this->mrpByArea = [];
        error_log('RESET MRP STANDAR - All MRP values cleared');
    }

    public function canCalculate()
    {
        return $this->finalCriteria->checkValidation() &&
            $this->finalDetails->checkValidation();
    }

    public function calculate()
    {
        $this->errorValidation = null;

        if (!$this->canCalculate()) {
            $this->errorValidation = 'MOHON LENGKAPI SELURUH KRITERIA PEMBIAYAAN';
            $this->calculationStatus = false;
            return;
        }

        $tenorBulan = intval($this->finalCriteria->tenor);
        $tipeUnit = $this->finalCriteria->passComm;
        $typeAngsuran = $this->finalCriteria->typeAngsuran;

        $provisiError = CalculationService::validateProvisi($tenorBulan, $tipeUnit);
        if ($provisiError !== null) {
            $this->errorValidation = strtoupper($provisiError);
            $this->calculationStatus = false;
            return;
        }

        $lifeInsuranceError = CalculationService::validateLifeInsurance($tenorBulan, $tipeUnit);
        if ($lifeInsuranceError !== null) {
            $this->errorValidation = strtoupper($lifeInsuranceError);
            $this->calculationStatus = false;
            return;
        }

        $effectiveRateError = CalculationService::validateEffectiveRate($tenorBulan, $typeAngsuran);
        if ($effectiveRateError !== null) {
            $this->errorValidation = strtoupper($effectiveRateError);
            $this->calculationStatus = false;
            return;
        }

        if ($this->insuranceRegion === null || $this->insuranceRegion < 1 || $this->insuranceRegion > 3) {
            $this->errorValidation = 'REGION ASURANSI TIDAK DITEMUKAN';
            $this->calculationStatus = false;
            return;
        }

        $areaNewRaw = $_SESSION['area_new'] ?? null;
        $areaNew = strtoupper(trim($areaNewRaw ?? ''));
        $nonJawaAreas = ['KALIMANTAN', 'IBT', 'SULAWESI', 'SUMBAGSEL', 'SUMBAGUT&TENG'];
        $jawaAreas = ['JABODETABEKSER', 'JABAR', 'JATENG', 'JATIM'];

        $dpMinimumPercent = 0.2;
        if (in_array($areaNew, $nonJawaAreas, true)) {
            $dpMinimumPercent = 0.25;
        } elseif (in_array($areaNew, $jawaAreas, true)) {
            $dpMinimumPercent = 0.2;
        }

        $dpMinimum = $this->finalDetails->mrpPengajuan * $dpMinimumPercent;
        if ($this->finalDetails->dp < $dpMinimum) {
            $minPercentLabel = (int) ($dpMinimumPercent * 100);
            $this->errorValidation = 'DP MINIMAL ' . $minPercentLabel . '% DARI MRP PENGAJUAN';
            $this->calculationStatus = false;
            return;
        }

        try {
            error_log('CALCULATE - USING DISCOUNT REFUND: ' . $this->discountRefund . ' FOR DEALER_ID: ' . ($this->dealerId ?? 'NULL'));

            $this->finalResult = CalculationService::calculate(
                $this->finalCriteria,
                $this->finalDetails,
                $this->insuranceRegion,
                $this->discountRefund,
                $this->dealerId
            );

            if ($this->finalResult->isEmpty()) {
                $this->errorValidation = 'PERHITUNGAN GAGAL, PERIKSA KEMBALI DATA YANG DIMASUKKAN';
                $this->calculationStatus = false;
            } else {
                $this->calculationStatus = true;
                $this->errorValidation = null;
                error_log('CALCULATE SUCCESS - REFUND: ' . $this->finalResult->refund);
            }
        } catch (Exception $e) {
            $this->errorValidation = 'ERROR: ' . strtoupper($e->getMessage());
            $this->calculationStatus = false;
        }
    }

    public function resetCalculation()
    {
        $this->finalResult = CalculationResult::empty();
        $this->calculationStatus = false;
        $this->errorValidation = null;
    }

    public function resetAll()
    {
        $this->finalCriteria->reset();
        $this->finalDetails->reset();
        $this->finalResult = CalculationResult::empty();
        $this->calculationStatus = false;
        $this->errorValidation = null;
        $this->insuranceRegion = null;
        $this->mrpStandar = null;
        $this->mrpStandarMessage = null;
        $this->mrpByArea = [];
    }
}
