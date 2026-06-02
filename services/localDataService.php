<?php

class LocalDataService
{
    private static $cache = [];

    public static function clearCache()
    {
        self::$cache = [];
    }

    private static function getDummyData()
    {
        return [
            'TOYOTA' => [
                'fields' => ['MERK', 'MODEL', 'TYPE', 'TAHUN', 'MRP', 'TDP', 'LTV'],
                'data' => [
                    ['TOYOTA', 'AVANZA', '1.3 E MT', 2020, 200000000, 40000000, 0.8],
                    ['TOYOTA', 'AVANZA', '1.3 G MT', 2020, 215000000, 43000000, 0.8],
                    ['TOYOTA', 'AVANZA', '1.5 VELOZ MT', 2021, 250000000, 50000000, 0.8],
                    ['TOYOTA', 'INNOVA', '2.0 G MT', 2020, 320000000, 64000000, 0.8],
                    ['TOYOTA', 'INNOVA', '2.4 V AT', 2021, 420000000, 84000000, 0.8],
                    ['TOYOTA', 'FORTUNER', '2.4 VRZ 4X2 AT', 2020, 480000000, 96000000, 0.8],
                    ['TOYOTA', 'FORTUNER', '2.7 SRZ 4X2 AT', 2021, 520000000, 104000000, 0.8],
                ]
            ],
            'HONDA' => [
                'fields' => ['MERK', 'MODEL', 'TYPE', 'TAHUN', 'MRP', 'TDP', 'LTV'],
                'data' => [
                    ['HONDA', 'BRIO', 'E MT', 2020, 150000000, 30000000, 0.8],
                    ['HONDA', 'BRIO', 'RS CVT', 2021, 180000000, 36000000, 0.8],
                    ['HONDA', 'MOBILIO', 'E MT', 2020, 190000000, 38000000, 0.8],
                    ['HONDA', 'MOBILIO', 'RS CVT', 2021, 220000000, 44000000, 0.8],
                    ['HONDA', 'CR-V', '1.5 TURBO CVT', 2020, 450000000, 90000000, 0.8],
                    ['HONDA', 'CR-V', '1.5 TURBO PRESTIGE', 2021, 520000000, 104000000, 0.8],
                ]
            ],
            'DAIHATSU' => [
                'fields' => ['MERK', 'MODEL', 'TYPE', 'TAHUN', 'MRP', 'TDP', 'LTV'],
                'data' => [
                    ['DAIHATSU', 'AYLA', '1.0 D MT', 2020, 110000000, 22000000, 0.8],
                    ['DAIHATSU', 'AYLA', '1.2 R MT', 2021, 130000000, 26000000, 0.8],
                    ['DAIHATSU', 'XENIA', '1.3 R MT', 2020, 190000000, 38000000, 0.8],
                    ['DAIHATSU', 'XENIA', '1.5 R AT', 2021, 220000000, 44000000, 0.8],
                    ['DAIHATSU', 'TERIOS', '1.5 X MT', 2020, 230000000, 46000000, 0.8],
                    ['DAIHATSU', 'TERIOS', '1.5 R AT', 2021, 260000000, 52000000, 0.8],
                ]
            ],
            'MITSUBISHI' => [
                'fields' => ['MERK', 'MODEL', 'TYPE', 'TAHUN', 'MRP', 'TDP', 'LTV'],
                'data' => [
                    ['MITSUBISHI', 'XPANDER', 'GLX MT', 2020, 230000000, 46000000, 0.8],
                    ['MITSUBISHI', 'XPANDER', 'ULTIMATE AT', 2021, 280000000, 56000000, 0.8],
                    ['MITSUBISHI', 'PAJERO SPORT', 'EXCEED 4X2 AT', 2020, 480000000, 96000000, 0.8],
                    ['MITSUBISHI', 'PAJERO SPORT', 'DAKAR 4X4 AT', 2021, 580000000, 116000000, 0.8],
                ]
            ],
            'SUZUKI' => [
                'fields' => ['MERK', 'MODEL', 'TYPE', 'TAHUN', 'MRP', 'TDP', 'LTV'],
                'data' => [
                    ['SUZUKI', 'ERTIGA', 'GL MT', 2020, 200000000, 40000000, 0.8],
                    ['SUZUKI', 'ERTIGA', 'GX AT', 2021, 230000000, 46000000, 0.8],
                    ['SUZUKI', 'XL7', 'BETA AT', 2020, 240000000, 48000000, 0.8],
                    ['SUZUKI', 'XL7', 'ZETA AT', 2021, 260000000, 52000000, 0.8],
                ]
            ],
        ];
    }

    public static function getBrands()
    {
        return ['DAIHATSU', 'HONDA', 'MITSUBISHI', 'SUZUKI', 'TOYOTA'];
    }

    public static function getModelsByBrand($brand)
    {
        if (empty($brand)) {
            return [];
        }

        $dummyData = self::getDummyData();
        if (!isset($dummyData[$brand])) {
            return [];
        }

        $data = $dummyData[$brand]['data'];
        $fields = $dummyData[$brand]['fields'];
        $modelIndex = array_search('MODEL', $fields);

        if ($modelIndex === false) {
            return [];
        }

        $models = [];
        foreach ($data as $row) {
            if (isset($row[$modelIndex])) {
                $model = $row[$modelIndex];
                if (!in_array($model, $models)) {
                    $models[] = $model;
                }
            }
        }

        sort($models);
        return $models;
    }

    public static function getTypesByBrandAndModel($brand, $model)
    {
        if (empty($brand) || empty($model)) {
            return [];
        }

        $dummyData = self::getDummyData();
        if (!isset($dummyData[$brand])) {
            return [];
        }

        $data = $dummyData[$brand]['data'];
        $fields = $dummyData[$brand]['fields'];
        $modelIndex = array_search('MODEL', $fields);
        $typeIndex = array_search('TYPE', $fields);

        if ($modelIndex === false || $typeIndex === false) {
            return [];
        }

        $types = [];
        foreach ($data as $row) {
            if (isset($row[$modelIndex]) && $row[$modelIndex] === $model) {
                if (isset($row[$typeIndex])) {
                    $type = $row[$typeIndex];
                    if (!in_array($type, $types)) {
                        $types[] = $type;
                    }
                }
            }
        }

        sort($types);
        return $types;
    }

    public static function getMRPData($brand, $model, $type, $year)
    {
        if (empty($brand) || empty($model) || empty($type) || empty($year)) {
            return null;
        }

        $dummyData = self::getDummyData();
        if (!isset($dummyData[$brand])) {
            return null;
        }

        $data = $dummyData[$brand]['data'];
        $fields = $dummyData[$brand]['fields'];
        $modelIndex = array_search('MODEL', $fields);
        $typeIndex = array_search('TYPE', $fields);
        $yearIndex = array_search('TAHUN', $fields);
        $mrpIndex = array_search('MRP', $fields);
        $tdpIndex = array_search('TDP', $fields);
        $ltvIndex = array_search('LTV', $fields);

        if ($modelIndex === false || $typeIndex === false || $yearIndex === false) {
            return null;
        }

        $targetYear = intval($year);

        foreach ($data as $row) {
            if (
                isset($row[$modelIndex]) && $row[$modelIndex] === $model &&
                isset($row[$typeIndex]) && $row[$typeIndex] === $type &&
                isset($row[$yearIndex]) && intval($row[$yearIndex]) === $targetYear
            ) {

                $otr = isset($row[$mrpIndex]) ? floatval($row[$mrpIndex]) : 0;

                return [
                    'mrp' => $otr,
                    'tdp' => isset($row[$tdpIndex]) ? floatval($row[$tdpIndex]) : 0,
                    'ltv' => isset($row[$ltvIndex]) ? floatval($row[$ltvIndex]) : 0,
                    'otr' => $otr,
                ];
            }
        }

        return null;
    }

    public static function calculateFidusia($otr)
    {
        if ($otr <= 0) {
            return 0;
        } elseif ($otr <= 50000000) {
            return 215000;
        } elseif ($otr <= 100000000) {
            return 265000;
        } elseif ($otr <= 249999999) {
            return 365000;
        } elseif ($otr <= 500000000) {
            return 615000;
        } elseif ($otr <= 20000000000) {
            return 1015000;
        }
        return 0;
    }
}
