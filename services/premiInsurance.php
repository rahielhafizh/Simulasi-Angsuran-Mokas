  <?php

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
        if ($year < 1 || $year > 7) {
            throw new Exception('Tahun Tenor Invalid');
        }
        $percentage = isset(self::$mrpFinalByYear[$year]) ? self::$mrpFinalByYear[$year] : 1.0;
        return $mrpPengajuan * $percentage;
    }

    public static function calculateFullCompreInsurance($mrpFinal, $region, $unitPassenger)
    {
        if ($region < 1 || $region > 3) {
            throw new Exception('Region Plat Nomor Invalid');
        }

        $baseRate = 0;

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

        if ($unitPassenger) {
            $baseRate += self::ADDITIONAL_PASSENGER_RATE;
        }

        return $baseRate;
    }

    public static function calculateFullTLOInsurance($mrpFinal, $region, $unitPassenger)
    {
        if ($region < 1 || $region > 3) {
            throw new Exception('Region Plat Nomor Invalid');
        }

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
        } else {
            return 0.0020;
        }
    }

    public static function calculateTarifCombi1($mrpFinal, $year, $region, $unitPassenger)
    {
        if ($year == 1) {
            return self::calculateFullCompreInsurance($mrpFinal, $region, $unitPassenger);
        }
        return self::calculateFullTLOInsurance($mrpFinal, $region, $unitPassenger);
    }

    public static function calculatePremi($mrpPengajuan, $year, $region, $asuransiUnit, $tipeUnit)
    {
        if ($mrpPengajuan <= 0) {
            throw new Exception('Nominal MRP Invalid');
        }

        $mrpFinal = self::calculateMRPFinal($mrpPengajuan, $year);
        $unitPassenger = strtoupper($tipeUnit) === 'PASSENGER';
        $asuransiType = strtoupper($asuransiUnit);

        switch ($asuransiType) {
            case 'FULL COMPRE':
                $tarifFullCompre = self::calculateFullCompreInsurance($mrpFinal, $region, $unitPassenger);
                return $mrpFinal * $tarifFullCompre;

            case 'FULL TLO':
                $tarifFullTLO = self::calculateFullTLOInsurance($mrpFinal, $region, $unitPassenger);
                return $mrpFinal * $tarifFullTLO;

            case 'COMBI 1':
                $tarifCombi1 = self::calculateTarifCombi1($mrpFinal, $year, $region, $unitPassenger);
                return $mrpFinal * $tarifCombi1;

            default:
                throw new Exception('Tipe Asuransi Invalid: ' . $asuransiUnit);
        }
    }

    public static function calculatePremiAsuransiTahunan($mrpPengajuan, $tenor, $region, $asuransiUnit, $tipeUnit)
    {
        if ($tenor < 1 || $tenor > 7) {
            throw new Exception('Tahun Tenor Invalid');
        }

        $result = [];
        for ($year = 1; $year <= $tenor; $year++) {
            $mrpFinal = self::calculateMRPFinal($mrpPengajuan, $year);
            $premi = self::calculatePremi($mrpPengajuan, $year, $region, $asuransiUnit, $tipeUnit);

            $result[] = [
                'year' => $year,
                'mrpFinal' => $mrpFinal,
                'premi' => $premi,
            ];
        }

        return $result;
    }

    public static function calculateTotalPremi($mrpPengajuan, $tenor, $region, $asuransiUnit, $tipeUnit)
    {
        $premiAsuransiTahunan = self::calculatePremiAsuransiTahunan($mrpPengajuan, $tenor, $region, $asuransiUnit, $tipeUnit);

        $total = 0;
        foreach ($premiAsuransiTahunan as $item) {
            $total += $item['premi'];
        }

        return $total;
    }

    public static function calculatePremiSummary($mrpPengajuan, $tenor, $region, $asuransiUnit, $tipeUnit)
    {
        $premiAsuransiTahunan = self::calculatePremiAsuransiTahunan($mrpPengajuan, $tenor, $region, $asuransiUnit, $tipeUnit);

        $totalPremi = 0;
        foreach ($premiAsuransiTahunan as $item) {
            $totalPremi += $item['premi'];
        }

        $averagePremi = $totalPremi / $tenor;

        return [
            'totalPremi' => $totalPremi,
            'averagePremi' => $averagePremi,
            'premiAsuransiTahunan' => $premiAsuransiTahunan,
        ];
    }
}
