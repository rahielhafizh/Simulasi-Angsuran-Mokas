<?php

require_once __DIR__ . '/../models/calculationResult.php';
require_once __DIR__ . '/../services/effectiveRateService.php';
require_once __DIR__ . '/../services/premiInsurance.php';
require_once __DIR__ . '/../services/provisiCalculator.php';
require_once __DIR__ . '/../services/lifeInsuranceCalculator.php';

class CalculationService
{
    const BIAYA_ADMINISTRASI = 6000000;

    private static $fidusiaRanges = [
        50000000 => 215000,
        100000000 => 265000,
        249999999 => 365000,
        500000000 => 615000,
        20000000000 => 1015000,
    ];

    public static function roundToNearestThousand($value)
    {
        return round($value / 1000) * 1000.0;
    }

    public static function calculate($criteria, $details, $insuranceRegion, $discountRefund = 0, $dealerId = null)
    {
        if (!self::inputValidation($criteria, $details)) {
            return CalculationResult::empty();
        }

        if ($insuranceRegion === null || $insuranceRegion < 1 || $insuranceRegion > 3) {
            return CalculationResult::empty();
        }

        $mrpPengajuan = floatval($details->mrpPengajuan);
        $dp = floatval($details->dp);
        $tenorBulan = intval($criteria->tenor ?? 12);
        $tipeUnit = $criteria->passComm ?? 'PASSENGER';
        $typeAngsuran = $criteria->typeAngsuran ?? 'ADDB';

        $pokokHutang1 = $mrpPengajuan - $dp;

        if ($pokokHutang1 <= 0) {
            return CalculationResult::empty();
        }

        $biayaFidusia = floatval(self::calculateFidusia($mrpPengajuan));

        $totalPremiAsuransi = 0.0;
        $premiAsuransiTahunan = [];

        try {
            $tenorTahun = intval(ceil($tenorBulan / 12));
            $tenorFinal = $tenorTahun > 7 ? 7 : $tenorTahun;
            $premiSummary = PremiInsurance::calculatePremiSummary(
                $mrpPengajuan,
                $tenorFinal,
                $insuranceRegion,
                $criteria->asuransiUnit ?? 'FULL COMPRE',
                $tipeUnit
            );

            $totalPremiAsuransi = floatval($premiSummary['totalPremi']);
            $premiAsuransiTahunan = $premiSummary['premiAsuransiTahunan'];
        } catch (Exception $e) {
            $totalPremiAsuransi = 0.0;
            $premiAsuransiTahunan = [];
        }

        $pokokHutang2 = $pokokHutang1 + self::BIAYA_ADMINISTRASI + $biayaFidusia + $totalPremiAsuransi;

        $biayaProvisi = 0.0;
        try {
            $biayaProvisi = floatval(ProvisiCalculator::calculateBiayaProvisi($pokokHutang1, $tenorBulan, $tipeUnit));
        } catch (Exception $e) {
            $biayaProvisi = 0.0;
        }

        $pokokHutang3 = $pokokHutang2 + $biayaProvisi;

        $lifeInsurance = 0.0;
        try {
            $lifeInsurance = floatval(LifeInsuranceCalculator::calculateLifeInsurance($pokokHutang3, $tenorBulan, $tipeUnit));
        } catch (Exception $e) {
            $lifeInsurance = 0.0;
        }

        $totalPokokHutangFinal = $pokokHutang3 + $lifeInsurance;

        $negoBungaPercentage = EffectiveRateService::parseNegoBunga($criteria->negoBunga ?? '0.00%');

        $effectiveRateAkhir = EffectiveRateService::calculateEffectiveRateAkhir(
            $tenorBulan,
            $negoBungaPercentage,
            $dealerId,
            $criteria->passComm,
            $criteria->unitCategoryName ?? null,
            $criteria->unitSegmentName ?? null,
            $criteria->tahun ?? null
        );

        if ($effectiveRateAkhir === null) {
            return CalculationResult::empty();
        }

        $finalFlatRate = EffectiveRateService::convertToFinalFlatRate($effectiveRateAkhir, $tenorBulan, $typeAngsuran);

        if ($finalFlatRate === null) {
            return CalculationResult::empty();
        }

        $tenorTahun = floatval($tenorBulan) / 12.0;
        $totalBunga = $finalFlatRate * $tenorTahun * $totalPokokHutangFinal;
        $totalNetAR = $totalPokokHutangFinal + $totalBunga;

        $angsuranPerBulanRaw = $totalNetAR / floatval($tenorBulan);
        $angsuranPerBulan = self::roundToNearestThousand($angsuranPerBulanRaw);

        $refundBase = 0.14 * $totalBunga;
        $refund = $refundBase - floatval($discountRefund);

        if ($refund < 0) {
            $refund = 0;
        }

        $allIn = $refund + $pokokHutang1;

        $tdp = $dp;
        if (strtoupper($typeAngsuran) === 'ADDM') {
            $tdp = $dp + $angsuranPerBulan;
        }

        $pelunasan = $mrpPengajuan - $tdp;

        return new CalculationResult(
            $angsuranPerBulan,
            $allIn,
            $pelunasan,
            $refund,
            $totalPremiAsuransi,
            $premiAsuransiTahunan,
            $pokokHutang1,
            $pokokHutang2,
            $pokokHutang3,
            $totalPokokHutangFinal,
            self::BIAYA_ADMINISTRASI,
            $biayaFidusia,
            $biayaProvisi,
            $lifeInsurance,
            $totalBunga,
            $totalNetAR,
            $tdp
        );
    }

    public static function calculateFidusia($mrpPengajuan)
    {
        if ($mrpPengajuan <= 0) {
            return 0;
        }

        foreach (self::$fidusiaRanges as $threshold => $value) {
            if ($mrpPengajuan <= $threshold) {
                return $value;
            }
        }

        return 0;
    }

    public static function inputValidation($criteria, $details)
    {
        return $criteria->checkValidation() && $details->checkValidation();
    }

    public static function validateProvisi($tenorBulan, $tipeUnit)
    {
        if (!ProvisiCalculator::provisiAvailability($tenorBulan, $tipeUnit)) {
            return 'Rumus Provisi Tenor ' . ($tenorBulan / 12) . ' tidak tersedia';
        }
        return null;
    }

    public static function validateLifeInsurance($tenorBulan, $tipeUnit)
    {
        if (!LifeInsuranceCalculator::isLifeInsuranceAvailable($tenorBulan, $tipeUnit)) {
            return 'Rate Life Insurance Tenor ' . $tenorBulan . ' tidak tersedia';
        }
        return null;
    }

    public static function validateEffectiveRate($tenorBulan, $typeAngsuran)
    {
        if (!EffectiveRateService::isEffectiveRateAvailable($tenorBulan)) {
            return 'Effective Rate untuk Tenor ' . $tenorBulan . ' tidak tersedia';
        }
        return null;
    }
}
