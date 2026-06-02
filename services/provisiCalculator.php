<?php

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

        if (!isset(self::$rateProvisi[$tenorBulan])) {
            throw new Exception('Tenor ' . $tenorBulan . ' tidak tersedia');
        }

        $rates = self::$rateProvisi[$tenorBulan];
        if (!isset($rates[$unitType])) {
            throw new Exception('Tipe unit invalid');
        }

        $rate = $rates[$unitType];
        if ($rate == -1) {
            return null;
        }

        return $rate;
    }

    public static function calculateBiayaProvisi($pokokHutang1, $tenorBulan, $tipeUnit)
    {
        if ($pokokHutang1 <= 0) {
            throw new Exception('PH1 tidak boleh 0');
        }

        $rate = self::getProvisiRate($tenorBulan, $tipeUnit);

        if ($rate === null) {
            throw new Exception('Rumus Provisi untuk tenor ' . ($tenorBulan / 12) . ' tidak tersedia');
        }

        return $pokokHutang1 * $rate;
    }

    public static function provisiAvailability($tenorBulan, $tipeUnit)
    {
        try {
            $rate = self::getProvisiRate($tenorBulan, $tipeUnit);
            return $rate !== null;
        } catch (Exception $e) {
            return false;
        }
    }
}
