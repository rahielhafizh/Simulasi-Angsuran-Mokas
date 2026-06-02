<?php

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
