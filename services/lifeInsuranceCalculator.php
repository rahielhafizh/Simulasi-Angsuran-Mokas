<?php

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
