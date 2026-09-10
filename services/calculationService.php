<!-- calculationService.php -->
<?php

require_once __DIR__ . '/../models/financingModels.php';

use CalculationResult;

class EffectiveRateService
{
    private static $effectiveRateAwal = [
        12 => 0.19,
        24 => 0.19,
        36 => 0.21,
        48 => 0.21,
        60 => 0.21,
    ];

    private static $specialRateRules = [
        [
            'dealerIds' => ['21755'],
            'passComm' => 'PASSENGER',
            'unitSegments' => ['FAST MOVING'],
            'unitCategories' => ['CITY CAR', 'MPV', 'SEDAN', 'SUV'],
            'minTahun' => 2020,
            'rates' => [
                12 => 0.145,
                24 => 0.145,
                36 => 0.145,
                48 => 0.145,
                60 => 0.145,
            ],
        ],
    ];

    public static function getEffectiveRateAwal(
        $tenorBulan,
        $dealerId = null,
        $passComm = null,
        $unitCategoryName = null,
        $unitSegmentName = null,
        $tahun = null
    ) {
        $specialRate = self::resolveSpecialRate(
            $tenorBulan,
            $dealerId,
            $passComm,
            $unitCategoryName,
            $unitSegmentName,
            $tahun
        );

        if ($specialRate !== null) {
            return $specialRate;
        }

        return self::$effectiveRateAwal[$tenorBulan] ?? null;
    }

    private static function resolveSpecialRate(
        $tenorBulan,
        $dealerId,
        $passComm,
        $unitCategoryName,
        $unitSegmentName,
        $tahun
    ) {
        if (empty($dealerId) || empty($passComm) || empty($unitCategoryName) || empty($unitSegmentName) || empty($tahun)) {
            return null;
        }

        $dealerIdStr = (string) $dealerId;
        $passCommUp = strtoupper(trim($passComm));
        $categoryUp = strtoupper(trim($unitCategoryName));
        $segmentUp = strtoupper(trim($unitSegmentName));
        $tahunInt = intval($tahun);

        foreach (self::$specialRateRules as $rule) {
            if (!in_array($dealerIdStr, $rule['dealerIds'], true)) {
                continue;
            }

            if (strtoupper($rule['passComm']) !== $passCommUp) {
                continue;
            }

            $segmentMatch = in_array($segmentUp, array_map('strtoupper', $rule['unitSegments']), true);
            $categoryMatch = in_array($categoryUp, array_map('strtoupper', $rule['unitCategories']), true);

            if (!$segmentMatch || !$categoryMatch) {
                continue;
            }

            if ($tahunInt <= $rule['minTahun']) {
                continue;
            }

            if (!isset($rule['rates'][$tenorBulan])) {
                continue;
            }

            error_log('SPECIAL RATE APPLIED - DEALER: ' . $dealerIdStr
                . ' | CATEGORY: ' . $categoryUp
                . ' | SEGMENT: ' . $segmentUp
                . ' | TAHUN: ' . $tahunInt
                . ' | TENOR: ' . $tenorBulan
                . ' | RATE: ' . $rule['rates'][$tenorBulan]);

            return $rule['rates'][$tenorBulan];
        }

        return null;
    }

    public static function calculateEffectiveRateAkhir(
        $tenorBulan,
        $negoBungaPercentage,
        $dealerId = null,
        $passComm = null,
        $unitCategoryName = null,
        $unitSegmentName = null,
        $tahun = null
    ) {
        $rateAwal = self::getEffectiveRateAwal(
            $tenorBulan,
            $dealerId,
            $passComm,
            $unitCategoryName,
            $unitSegmentName,
            $tahun
        );

        if ($rateAwal === null) {
            return null;
        }

        return $rateAwal + $negoBungaPercentage;
    }

    public static function convertToFinalFlatRate($effectiveRateAkhir, $tenorBulan, $typeAngsuran)
    {
        if ($tenorBulan <= 0) {
            return null;
        }

        $monthlyRate = $effectiveRateAkhir / 12;

        try {
            if (strtoupper($typeAngsuran) === 'ADDB') {
                $denominator = 1 - pow(1 + $monthlyRate, -$tenorBulan);

                if ($denominator == 0) {
                    return null;
                }

                $numerator = ($monthlyRate / $denominator) * $tenorBulan;
                $result = (($numerator - 1) * 12) / $tenorBulan;

                return $result;
            } elseif (strtoupper($typeAngsuran) === 'ADDM') {
                $denominator = 1 - pow(1 + $monthlyRate, -$tenorBulan);

                if ($denominator == 0) {
                    return null;
                }

                $factor = ($monthlyRate / $denominator) / (1 + $monthlyRate);
                $numerator = $factor * $tenorBulan;
                $result = (($numerator - 1) * 12) / $tenorBulan;

                return $result;
            }

            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    public static function parseNegoBunga($negoBungaString)
    {
        $cleaned = str_replace(['%', ' '], '', $negoBungaString);
        $parsed = floatval($cleaned);
        return $parsed / 100;
    }

    public static function isEffectiveRateAvailable($tenorBulan)
    {
        return isset(self::$effectiveRateAwal[$tenorBulan]);
    }

    public static function getAvailableTenors()
    {
        $tenors = array_keys(self::$effectiveRateAwal);
        sort($tenors);
        return $tenors;
    }
}

class LifeInsuranceCalculator
{
    private static $lifeInsurancePassengerRate = [
        12 => 0.00352,
        18 => 0.00528,
        24 => 0.00704,
        36 => 0.01112,
        48 => 0.01547,
        60 => 0.02242,
    ];

    private static $lifeInsuranceCommercialRate = [
        12 => 0.00422,
        18 => 0.00634,
        24 => 0.00845,
        36 => 0.01334,
        48 => 0.01856,
        60 => 0.02690,
    ];

    public static function getLifeInsuranceRate($tenorBulan, $tipeUnit)
    {
        $unitType = strtoupper($tipeUnit);

        if ($unitType === 'PASSENGER') {
            return isset(self::$lifeInsurancePassengerRate[$tenorBulan])
                ? self::$lifeInsurancePassengerRate[$tenorBulan]
                : null;
        } elseif ($unitType === 'COMMERCIAL') {
            return isset(self::$lifeInsuranceCommercialRate[$tenorBulan])
                ? self::$lifeInsuranceCommercialRate[$tenorBulan]
                : null;
        }

        return null;
    }

    public static function calculateLifeInsurance($pokokHutang3, $tenorBulan, $tipeUnit)
    {
        if ($pokokHutang3 <= 0) {
            throw new Exception('PH 3 tidak boleh 0');
        }

        $rate = self::getLifeInsuranceRate($tenorBulan, $tipeUnit);

        if ($rate === null) {
            throw new Exception('Rate Life Insurance ' . $tenorBulan . ' tidak tersedia');
        }

        return $pokokHutang3 * $rate;
    }

    public static function isLifeInsuranceAvailable($tenorBulan, $tipeUnit)
    {
        $rate = self::getLifeInsuranceRate($tenorBulan, $tipeUnit);
        return $rate !== null;
    }

    public static function getAvailableTenors($tipeUnit)
    {
        $unitType = strtoupper($tipeUnit);
        if ($unitType === 'PASSENGER') {
            $tenors = array_keys(self::$lifeInsurancePassengerRate);
            sort($tenors);
            return $tenors;
        } elseif ($unitType === 'COMMERCIAL') {
            $tenors = array_keys(self::$lifeInsuranceCommercialRate);
            sort($tenors);
            return $tenors;
        }
        return [];
    }
}

class PremiInsurance
{
    private static $mrpFinalByYear = [
        1 => 1.00,
        2 => 0.85,
        3 => 0.75,
        4 => 0.65,
        5 => 0.60,
        6 => 0.55,
        7 => 0.50,
    ];

    // Additional Passenger Sebelumnya = 0.0005;
    const ADDITIONAL_PASSENGER_RATE = 0;

    public static function calculateMRPFinal($mrpPengajuan, $year)
    {
        if ($year < 1 || $year > 7)
            throw new Exception('Tahun Tenor Invalid');

        $percentage = isset(self::$mrpFinalByYear[$year]) ? self::$mrpFinalByYear[$year] : 1.0;
        return $mrpPengajuan * $percentage;
    }

    public static function calculateFullCompreInsurance($mrpFinal, $region, $unitPassenger)
    {
        if ($region < 1 || $region > 3)
            throw new Exception('Region Plat Nomor Invalid');

        if ($mrpFinal <= 125000000) {
            $baseRate = $region == 1 ? 0.0382 : ($region == 2 ? 0.0326 : 0.0253);
        } elseif ($mrpFinal <= 200000000) {
            $baseRate = $region == 1 ? 0.0267 : ($region == 2 ? 0.0247 : 0.0269);
        } elseif ($mrpFinal <= 400000000) {
            $baseRate = $region == 1 ? 0.0218 : ($region == 2 ? 0.0208 : 0.0179);
        } elseif ($mrpFinal <= 800000000) {
            $baseRate = $region == 1 ? 0.0120 : ($region == 2 ? 0.0120 : 0.0114);
        } else {
            $baseRate = 0.0105;
        }

        if ($unitPassenger)
            $baseRate += self::ADDITIONAL_PASSENGER_RATE;

        return $baseRate;
    }

    public static function calculateFullTLOInsurance($mrpFinal, $region, $unitPassenger)
    {
        if ($region < 1 || $region > 3)
            throw new Exception('Region Plat Nomor Invalid');

        if (!$unitPassenger) {
            return $region == 1 ? 0.0088 : ($region == 2 ? 0.0168 : 0.0081);
        }

        if ($mrpFinal <= 125000000) {
            return $region == 1 ? 0.0047 : ($region == 2 ? 0.0065 : 0.0051);
        } elseif ($mrpFinal <= 200000000) {
            return $region == 1 ? 0.0063 : ($region == 2 ? 0.0044 : 0.0044);
        } elseif ($mrpFinal <= 400000000) {
            return $region == 1 ? 0.0041 : ($region == 2 ? 0.0038 : 0.0029);
        } elseif ($mrpFinal <= 800000000) {
            return $region == 1 ? 0.0025 : ($region == 2 ? 0.0025 : 0.0023);
        }

        return 0.0020;
    }

    public static function calculateTarifCombi1($mrpFinal, $year, $region, $unitPassenger)
    {
        return $year == 1
            ? self::calculateFullCompreInsurance($mrpFinal, $region, $unitPassenger)
            : self::calculateFullTLOInsurance($mrpFinal, $region, $unitPassenger);
    }

    public static function calculatePremi($mrpPengajuan, $year, $region, $asuransiUnit, $tipeUnit)
    {
        if ($mrpPengajuan <= 0)
            throw new Exception('Nominal MRP Invalid');

        $mrpFinal = self::calculateMRPFinal($mrpPengajuan, $year);
        $unitPassenger = strtoupper($tipeUnit) === 'PASSENGER';
        $asuransiType = strtoupper($asuransiUnit);

        switch ($asuransiType) {
            case 'FULL COMPRE':
                return $mrpFinal * self::calculateFullCompreInsurance($mrpFinal, $region, $unitPassenger);

            case 'FULL TLO':
                return $mrpFinal * self::calculateFullTLOInsurance($mrpFinal, $region, $unitPassenger);

            case 'COMBI 1':
                return $mrpFinal * self::calculateTarifCombi1($mrpFinal, $year, $region, $unitPassenger);

            default:
                throw new Exception('Tipe Asuransi Invalid: ' . $asuransiUnit);
        }
    }

    public static function calculatePremiAsuransiTahunan($mrpPengajuan, $tenor, $region, $asuransiUnit, $tipeUnit)
    {
        if ($tenor < 1 || $tenor > 7)
            throw new Exception('Tahun Tenor Invalid');

        $result = [];
        for ($year = 1; $year <= $tenor; $year++) {
            $result[] = [
                'year' => $year,
                'mrpFinal' => self::calculateMRPFinal($mrpPengajuan, $year),
                'premi' => self::calculatePremi($mrpPengajuan, $year, $region, $asuransiUnit, $tipeUnit),
            ];
        }

        return $result;
    }

    public static function calculateTotalPremi($mrpPengajuan, $tenor, $region, $asuransiUnit, $tipeUnit)
    {
        $premiAsuransiTahunan = self::calculatePremiAsuransiTahunan($mrpPengajuan, $tenor, $region, $asuransiUnit, $tipeUnit);

        $total = 0;
        foreach ($premiAsuransiTahunan as $item)
            $total += $item['premi'];

        return $total;
    }

    public static function calculatePremiSummary($mrpPengajuan, $tenor, $region, $asuransiUnit, $tipeUnit)
    {
        $premiAsuransiTahunan = self::calculatePremiAsuransiTahunan($mrpPengajuan, $tenor, $region, $asuransiUnit, $tipeUnit);

        $totalPremi = 0;
        foreach ($premiAsuransiTahunan as $item)
            $totalPremi += $item['premi'];

        return [
            'totalPremi' => $totalPremi,
            'averagePremi' => $totalPremi / $tenor,
            'premiAsuransiTahunan' => $premiAsuransiTahunan,
        ];
    }
}

class ProvisiCalculator
{
    private static $rateProvisi = [
        12 => ['PASSENGER' => 0.02, 'COMMERCIAL' => 0.0175],
        24 => ['PASSENGER' => 0.03, 'COMMERCIAL' => 0.025],
        36 => ['PASSENGER' => 0.04, 'COMMERCIAL' => 0.03],
        48 => ['PASSENGER' => 0.05, 'COMMERCIAL' => 0.03],
        60 => ['PASSENGER' => 0.05, 'COMMERCIAL' => -1],
    ];

    public static function getProvisiRate($tenorBulan, $tipeUnit)
    {
        $unitType = strtoupper($tipeUnit);

        if (!isset(self::$rateProvisi[$tenorBulan]))
            throw new Exception('Tenor ' . $tenorBulan . ' tidak tersedia');

        $rates = self::$rateProvisi[$tenorBulan];
        if (!isset($rates[$unitType]))
            throw new Exception('Tipe unit invalid');

        $rate = $rates[$unitType];
        return $rate == -1 ? null : $rate;
    }

    public static function calculateBiayaProvisi($pokokHutang1, $tenorBulan, $tipeUnit)
    {
        if ($pokokHutang1 <= 0)
            throw new Exception('PH1 tidak boleh 0');

        $rate = self::getProvisiRate($tenorBulan, $tipeUnit);
        if ($rate === null)
            throw new Exception('Rumus Provisi untuk tenor ' . ($tenorBulan / 12) . ' tidak tersedia');

        return $pokokHutang1 * $rate;
    }

    public static function provisiAvailability($tenorBulan, $tipeUnit)
    {
        try {
            return self::getProvisiRate($tenorBulan, $tipeUnit) !== null;
        } catch (Exception $e) {
            return false;
        }
    }
}

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