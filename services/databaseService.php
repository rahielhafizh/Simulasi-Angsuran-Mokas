<!-- databaseService.php -->
<?php

require_once __DIR__ . '/../config/database.php';

use Database;

class DatabaseService
{
    private static $cache = [];
    private static $cacheExpiry = [];
    private static $cacheTTL = 300;

    public static function clearCache()
    {
        self::$cache = [];
        self::$cacheExpiry = [];
    }

    private static function getCachedData($key)
    {
        if (isset(self::$cache[$key]) && isset(self::$cacheExpiry[$key])) {
            if (time() < self::$cacheExpiry[$key]) {
                return self::$cache[$key];
            }
            unset(self::$cache[$key]);
            unset(self::$cacheExpiry[$key]);
        }
        return null;
    }

    private static function setCachedData($key, $data, $ttl = null)
    {
        $ttl = $ttl ?? self::$cacheTTL;
        self::$cache[$key] = $data;
        self::$cacheExpiry[$key] = time() + $ttl;
    }

    public static function getBrands()
    {
        $cacheKey = 'brands';
        $cached = self::getCachedData($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        try {
            $db = Database::getInstance();
            $sql = "SELECT DISTINCT UNIT_MERK_NAME
                    FROM Dashboard_Master_Asset
                    WHERE UNIT_MERK_NAME IS NOT NULL
                    AND LTRIM(RTRIM(UNIT_MERK_NAME)) <> ''
                    ORDER BY UNIT_MERK_NAME ASC";

            $stmt = $db->query($sql);
            $results = $db->fetchAll($stmt);
            $brands = [];

            foreach ($results as $row) {
                if (!empty($row['UNIT_MERK_NAME'])) {
                    $brands[] = trim($row['UNIT_MERK_NAME']);
                }
            }

            self::setCachedData($cacheKey, $brands);
            return $brands;
        } catch (Exception $e) {
            error_log('ERROR FETCHING BRANDS : ' . $e->getMessage());
            return [];
        }
    }

    public static function getModelsByBrand($brand)
    {
        if (empty($brand)) {
            return [];
        }

        $cacheKey = 'models_' . md5($brand);
        $cached = self::getCachedData($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        try {
            $db = Database::getInstance();
            $sql = "SELECT DISTINCT UNIT_MODEL_NAME
                    FROM Dashboard_Master_Asset
                    WHERE UNIT_MERK_NAME = ?
                    AND UNIT_MODEL_NAME IS NOT NULL
                    AND LTRIM(RTRIM(UNIT_MODEL_NAME)) <> ''
                    ORDER BY UNIT_MODEL_NAME ASC";

            $stmt = $db->query($sql, [$brand]);
            $results = $db->fetchAll($stmt);
            $models = [];

            foreach ($results as $row) {
                if (!empty($row['UNIT_MODEL_NAME'])) {
                    $models[] = trim($row['UNIT_MODEL_NAME']);
                }
            }

            self::setCachedData($cacheKey, $models);
            return $models;
        } catch (Exception $e) {
            error_log('ERROR FETCHING MODELS : ' . $e->getMessage());
            return [];
        }
    }

    public static function getTypesByBrandAndModel($brand, $model)
    {
        if (empty($brand) || empty($model)) {
            return [];
        }

        $cacheKey = 'types_' . md5($brand . '_' . $model);
        $cached = self::getCachedData($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        try {
            $db = Database::getInstance();
            $sql = "SELECT DISTINCT UNIT_TYPE_NAME
                    FROM Dashboard_Master_Asset
                    WHERE UNIT_MERK_NAME = ?
                    AND UNIT_MODEL_NAME = ?
                    AND UNIT_TYPE_NAME IS NOT NULL
                    AND LTRIM(RTRIM(UNIT_TYPE_NAME)) <> ''
                    ORDER BY UNIT_TYPE_NAME ASC";

            $stmt = $db->query($sql, [$brand, $model]);
            $results = $db->fetchAll($stmt);
            $types = [];

            foreach ($results as $row) {
                if (!empty($row['UNIT_TYPE_NAME'])) {
                    $types[] = trim($row['UNIT_TYPE_NAME']);
                }
            }

            self::setCachedData($cacheKey, $types);
            return $types;
        } catch (Exception $e) {
            error_log('ERROR FETCHING TYPES : ' . $e->getMessage());
            return [];
        }
    }

    public static function getUnitCategoryAndSegment($type)
    {
        if (empty($type)) {
            return null;
        }

        $cacheKey = 'unit_cat_seg_' . md5($type);
        $cached = self::getCachedData($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        try {
            $db = Database::getInstance();
            $cleanType = preg_replace('/\s+/', ' ', trim($type));

            $sql = 'SELECT TOP 1
                        LTRIM(RTRIM(UNIT_CATEGORY_NAME)) AS UNIT_CATEGORY_NAME,
                        LTRIM(RTRIM(UNIT_SEGMENT_NAME))  AS UNIT_SEGMENT_NAME
                    FROM Dashboard_Master_Asset
                    WHERE LTRIM(RTRIM(UNIT_TYPE_NAME)) = ?
                    AND UNIT_CATEGORY_NAME IS NOT NULL
                    AND UNIT_SEGMENT_NAME  IS NOT NULL';

            $stmt = $db->query($sql, [$cleanType]);
            $row = $db->fetchOne($stmt);

            if (!$row) {
                error_log('GET UNIT CATEGORY/SEGMENT - NO DATA FOUND FOR TYPE: ' . $type);
                return null;
            }

            $result = [
                'unitCategoryName' => $row['UNIT_CATEGORY_NAME'] ?? null,
                'unitSegmentName' => $row['UNIT_SEGMENT_NAME'] ?? null,
            ];

            error_log('GET UNIT CATEGORY/SEGMENT - TYPE: ' . $type
                . ' | CATEGORY : ' . ($result['unitCategoryName'] ?? 'NULL')
                . ' | SEGMENT : ' . ($result['unitSegmentName'] ?? 'NULL'));

            self::setCachedData($cacheKey, $result, 600);
            return $result;
        } catch (Exception $e) {
            error_log('ERROR FETCHING UNIT CATEGORY/SEGMENT : ' . $e->getMessage());
            error_log('TYPE : ' . $type);
            return null;
        }
    }

    public static function getMRPStandar($type, $year, $area = null)
    {
        if (empty($type) || empty($year)) {
            return null;
        }

        $cacheKey = 'mrp_standar_' . md5($type . '_' . $year . '_' . ($area ?? ''));
        $cached = self::getCachedData($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        try {
            $db = Database::getInstance();
            $cleanType = preg_replace('/\s+/', ' ', trim($type));

            if (!empty($area)) {
                $sql = "SELECT TOP 1 UNIT_MRP, UNIT_TYPE_NAME, UNIT_TAHUN, AREA
                        FROM Dashboard_MRP_Asset
                        WHERE REPLACE(LTRIM(RTRIM(UNIT_TYPE_NAME)), '  ', ' ') = ?
                        AND UNIT_TAHUN = ?
                        AND LTRIM(RTRIM(AREA)) = ?
                        AND UNIT_MRP IS NOT NULL
                        AND UNIT_MRP > 0
                        ORDER BY UNIT_MRP DESC";

                $stmt = $db->query($sql, [$cleanType, intval($year), trim($area)]);
            } else {
                $sql = "SELECT TOP 1 UNIT_MRP, UNIT_TYPE_NAME, UNIT_TAHUN
                        FROM Dashboard_MRP_Asset
                        WHERE REPLACE(LTRIM(RTRIM(UNIT_TYPE_NAME)), '  ', ' ') = ?
                        AND UNIT_TAHUN = ?
                        AND UNIT_MRP IS NOT NULL
                        AND UNIT_MRP > 0
                        ORDER BY UNIT_MRP DESC";

                $stmt = $db->query($sql, [$cleanType, intval($year)]);
            }

            $result = $db->fetchOne($stmt);
            $mrp = null;

            if ($result && isset($result['UNIT_MRP'])) {
                $mrpValue = floatval($result['UNIT_MRP']);
                $mrp = $mrpValue > 0 ? $mrpValue : null;
            }

            self::setCachedData($cacheKey, $mrp, 600);
            return $mrp;
        } catch (Exception $e) {
            error_log('ERROR FETCHING MRP STANDAR : ' . $e->getMessage());
            error_log('TYPE : ' . $type . ' | YEAR : ' . $year . ' | AREA : ' . ($area ?? 'NULL'));
            return null;
        }
    }

    public static function getAllMRPByArea($type, $year)
    {
        if (empty($type) || empty($year)) {
            error_log('GET ALL MRP STANDAR - EMPTY TYPE/YEAR');
            return [];
        }

        $cacheKey = 'all_mrp_by_area_' . md5($type . '_' . $year);
        $cached = self::getCachedData($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        try {
            $db = Database::getInstance();
            $cleanType = preg_replace('/\s+/', ' ', trim($type));

            $sql = "SELECT DISTINCT
                        LTRIM(RTRIM(AREA)) AS AREA,
                        UNIT_MRP,
                        UNIT_TYPE_NAME,
                        UNIT_TAHUN
                    FROM Dashboard_MRP_Asset
                    WHERE REPLACE(LTRIM(RTRIM(UNIT_TYPE_NAME)), '  ', ' ') = ?
                    AND UNIT_TAHUN = ?
                    AND AREA IS NOT NULL
                    AND LTRIM(RTRIM(AREA)) <> ''
                    ORDER BY AREA ASC";

            $stmt = $db->query($sql, [$cleanType, intval($year)]);
            $results = $db->fetchAll($stmt);

            $mrpByArea = [];
            foreach ($results as $row) {
                $area = trim($row['AREA']);
                $mrp = isset($row['UNIT_MRP']) && $row['UNIT_MRP'] > 0 ? floatval($row['UNIT_MRP']) : null;

                if (!isset($mrpByArea[$area]) || ($mrp !== null && $mrp > ($mrpByArea[$area] ?? 0))) {
                    $mrpByArea[$area] = $mrp;
                }

                error_log('GET ALL MRP STANDAR - AREA : ' . $area . ' | MRP : ' . ($mrp ?? 'NULL'));
            }

            error_log('GET ALL MRP STANDAR - ' . count($mrpByArea) . ' AREA');

            self::setCachedData($cacheKey, $mrpByArea, 600);
            return $mrpByArea;
        } catch (Exception $e) {
            error_log('ERROR FETCHING ALL MRP AREA - ' . $e->getMessage());
            error_log('TYPE : ' . $type . ' | YEAR : ' . $year);
            return [];
        }
    }

    public static function getMRPData($brand, $model, $type, $year, $area = null)
    {
        if (empty($brand) || empty($model) || empty($type) || empty($year)) {
            return null;
        }

        try {
            $mrpStandar = self::getMRPStandar($type, $year, $area);

            if ($mrpStandar === null) {
                return [
                    'mrp' => 0,
                    'tdp' => 0,
                    'ltv' => 0,
                    'otr' => 0,
                    'mrpStandar' => null,
                    'message' => 'MRP tahun ' . $year . ' tidak tersedia',
                ];
            }

            return [
                'mrp' => $mrpStandar,
                'tdp' => $mrpStandar * 0.2,
                'ltv' => 0.8,
                'otr' => $mrpStandar,
                'mrpStandar' => $mrpStandar,
                'message' => null,
            ];
        } catch (Exception $e) {
            error_log('ERROR FETCHING MRP DATA : ' . $e->getMessage());
            return null;
        }
    }

    public static function getDealerDiscountRefund($dealerId)
    {
        if (empty($dealerId)) {
            error_log('[DISCOUNT] DEALER_ID IS EMPTY');
            return 0;
        }

        $cacheKey = 'discount_refund_dealer_' . $dealerId;
        $cached = self::getCachedData($cacheKey);
        if ($cached !== null) {
            error_log('[DISCOUNT] USING CACHE FOR DEALER_ID: ' . $dealerId . ' = ' . $cached);
            return $cached;
        }

        try {
            $db = Database::getInstance();
            $sql = 'SELECT DEALER_ID, DEALER_CODE, DEALER_NAME, BRANCH, DISCOUNT_REFUND
                    FROM M_AREA_DEALER_KHUSUS
                    WHERE DEALER_ID = ?';

            error_log('[DISCOUNT] QUERYING DEALER_ID: ' . $dealerId);

            $stmt = $db->query($sql, [intval($dealerId)]);
            $result = $db->fetchOne($stmt);

            $discountRefund = 0;

            if ($result) {
                error_log('[DISCOUNT] FOUND RECORD : ' . print_r($result, true));

                if (isset($result['DISCOUNT_REFUND']) && $result['DISCOUNT_REFUND'] !== null) {
                    $discountValue = floatval($result['DISCOUNT_REFUND']);
                    $discountRefund = $discountValue < 0 ? 0 : $discountValue;
                }

                error_log('[DISCOUNT] DEALER ID ' . $dealerId . ' - DISCOUNT ' . $discountRefund);
            } else {
                error_log('[DISCOUNT] NO DISCOUNT FOUND FOR DEALER ID: ' . $dealerId);
            }

            self::setCachedData($cacheKey, $discountRefund, 1800);
            return $discountRefund;
        } catch (Exception $e) {
            error_log('ERROR FETCHING DISCOUNT REFUND : ' . $e->getMessage());
            error_log('DEALER ID : ' . $dealerId);
            return 0;
        }
    }
}

class DealerRecordService
{
    private static function sanitizeText($text)
    {
        if ($text === null || $text === '') {
            return null;
        }

        $decoded = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim($decoded);
    }

    private static function validateDealerData($dealerId, $dealerName)
    {
        $cleanDealerId = self::sanitizeText($dealerId);
        $cleanDealerName = self::sanitizeText($dealerName);

        if (empty($cleanDealerId)) {
            throw new Exception('DEALER_ID CANNOT BE NULL OR EMPTY');
        }

        if (empty($cleanDealerName)) {
            throw new Exception('DEALER_NAME CANNOT BE NULL OR EMPTY');
        }

        return true;
    }

    private static function validateSimulationData($criteria, $details)
    {
        if ($criteria->tahun !== null) {
            $currentYear = (int) date('Y');
            $tahun = (int) $criteria->tahun;

            if ($tahun < 1900 || $tahun > ($currentYear + 1)) {
                throw new Exception('TAHUN VALUE OUT OF VALID RANGE');
            }
        }

        if ($criteria->tenor !== null && (int) $criteria->tenor <= 0) {
            throw new Exception('TENOR MUST BE GREATER THAN ZERO');
        }

        if ($details->mrpPengajuan < 0) {
            throw new Exception('MRP_PENGAJUAN CANNOT BE NEGATIVE');
        }

        if ($details->dp < 0) {
            throw new Exception('DOWNPAYMENT CANNOT BE NEGATIVE');
        }

        return true;
    }

    private static function parseNegoBunga($negoBungaString)
    {
        if (empty($negoBungaString)) {
            return null;
        }

        $cleaned = str_replace(['%', ' ', '+'], '', $negoBungaString);
        $parsed = floatval($cleaned);

        if ($parsed < 0 || $parsed > 100) {
            throw new Exception('NEGO_BUNGA MUST BE BETWEEN 0 AND 100');
        }

        return $parsed;
    }

    public static function recordSimulation($dealerId, $dealerName, $dealerBranch, $dealerArea, $criteria, $details, $mrpStandar, $calculationResult)
    {
        try {
            self::validateDealerData($dealerId, $dealerName);
            self::validateSimulationData($criteria, $details);

            $db = Database::getInstance();

            $negoBunga = self::parseNegoBunga($criteria->negoBunga);
            $tahun = $criteria->tahun !== null ? (int) $criteria->tahun : null;
            $tenor = $criteria->tenor !== null ? (int) $criteria->tenor : null;

            $mrpStandarValue = ($mrpStandar !== null && $mrpStandar > 0) ? $mrpStandar : 0;

            $dealerIdClean = self::sanitizeText($dealerId);
            $dealerNameClean = self::sanitizeText($dealerName);
            $dealerBranchClean = self::sanitizeText($dealerBranch);
            $dealerAreaClean = self::sanitizeText($dealerArea);

            $allIn = 0;
            $refund = 0;
            $pelunasan = 0;
            $angsuranBulan = 0;

            if ($calculationResult !== null && !$calculationResult->isEmpty()) {
                $allIn = $calculationResult->allIn > 0 ? $calculationResult->allIn : 0;
                $refund = $calculationResult->refund > 0 ? $calculationResult->refund : 0;
                $pelunasan = $calculationResult->pelunasan > 0 ? $calculationResult->pelunasan : 0;
                $angsuranBulan = $calculationResult->angsuranPerBulan > 0 ? $calculationResult->angsuranPerBulan : 0;
            }

            $sql = "{call SP_Dealer_Record(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)}";

            $params = [
                $dealerIdClean,
                $dealerNameClean,
                $dealerBranchClean,
                $dealerAreaClean,
                $criteria->typeAngsuran,
                $criteria->kodePlat,
                $criteria->merk,
                $criteria->model,
                $criteria->type,
                $tahun,
                $tenor,
                $negoBunga,
                $criteria->asuransiUnit,
                $criteria->passComm,
                $details->mrpPengajuan > 0 ? $details->mrpPengajuan : null,
                $mrpStandarValue,
                $details->dp > 0 ? $details->dp : null,
                $allIn > 0 ? $allIn : null,
                $refund > 0 ? $refund : null,
                $pelunasan > 0 ? $pelunasan : null,
                $angsuranBulan > 0 ? $angsuranBulan : null
            ];

            $stmt = $db->query($sql, $params);

            if ($stmt === false) {
                throw new Exception('FAILED TO EXECUTE SP_DEALER_RECORD');
            }

            $result = $db->fetchOne($stmt);

            if ($result && isset($result['STATUS']) && $result['STATUS'] === 'SUCCESS') {
                error_log('DEALER RECORD SAVED - DEALER_ID: ' . $dealerIdClean . ' | DEALER_AREA: ' . $dealerAreaClean . ' | MRP_STANDAR: ' . $mrpStandarValue . ' | CREATE_DATE: ' . ($result['CREATE_DATE'] ?? 'N/A'));
                return [
                    'success' => true,
                    'message' => 'SIMULATION RECORDED SUCCESSFULLY',
                    'data' => $result
                ];
            }

            throw new Exception('UNEXPECTED RESPONSE FROM SP_DEALER_RECORD');
        } catch (Exception $e) {
            error_log('ERROR RECORDING DEALER SIMULATION: ' . $e->getMessage());
            error_log('DEALER_ID: ' . ($dealerId ?? 'NULL') . ' | DEALER_NAME: ' . ($dealerName ?? 'NULL') . ' | DEALER_AREA: ' . ($dealerArea ?? 'NULL'));

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    public static function canRecordSimulation($criteria, $details)
    {
        try {
            return $criteria->checkValidation() && $details->checkValidation();
        } catch (Exception $e) {
            return false;
        }
    }
}

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
                ],
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
                ],
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
                ],
            ],
            'MITSUBISHI' => [
                'fields' => ['MERK', 'MODEL', 'TYPE', 'TAHUN', 'MRP', 'TDP', 'LTV'],
                'data' => [
                    ['MITSUBISHI', 'XPANDER', 'GLX MT', 2020, 230000000, 46000000, 0.8],
                    ['MITSUBISHI', 'XPANDER', 'ULTIMATE AT', 2021, 280000000, 56000000, 0.8],
                    ['MITSUBISHI', 'PAJERO SPORT', 'EXCEED 4X2 AT', 2020, 480000000, 96000000, 0.8],
                    ['MITSUBISHI', 'PAJERO SPORT', 'DAKAR 4X4 AT', 2021, 580000000, 116000000, 0.8],
                ],
            ],
            'SUZUKI' => [
                'fields' => ['MERK', 'MODEL', 'TYPE', 'TAHUN', 'MRP', 'TDP', 'LTV'],
                'data' => [
                    ['SUZUKI', 'ERTIGA', 'GL MT', 2020, 200000000, 40000000, 0.8],
                    ['SUZUKI', 'ERTIGA', 'GX AT', 2021, 230000000, 46000000, 0.8],
                    ['SUZUKI', 'XL7', 'BETA AT', 2020, 240000000, 48000000, 0.8],
                    ['SUZUKI', 'XL7', 'ZETA AT', 2021, 260000000, 52000000, 0.8],
                ],
            ],
        ];
    }

    public static function getBrands()
    {
        return ['DAIHATSU', 'HONDA', 'MITSUBISHI', 'SUZUKI', 'TOYOTA'];
    }

    public static function getModelsByBrand($brand)
    {
        if (empty($brand))
            return [];

        $dummyData = self::getDummyData();
        if (!isset($dummyData[$brand]))
            return [];

        $data = $dummyData[$brand]['data'];
        $fields = $dummyData[$brand]['fields'];
        $modelIndex = array_search('MODEL', $fields);

        if ($modelIndex === false)
            return [];

        $models = [];
        foreach ($data as $row) {
            if (isset($row[$modelIndex]) && !in_array($row[$modelIndex], $models)) {
                $models[] = $row[$modelIndex];
            }
        }

        sort($models);
        return $models;
    }

    public static function getTypesByBrandAndModel($brand, $model)
    {
        if (empty($brand) || empty($model))
            return [];

        $dummyData = self::getDummyData();
        if (!isset($dummyData[$brand]))
            return [];

        $data = $dummyData[$brand]['data'];
        $fields = $dummyData[$brand]['fields'];
        $modelIndex = array_search('MODEL', $fields);
        $typeIndex = array_search('TYPE', $fields);

        if ($modelIndex === false || $typeIndex === false)
            return [];

        $types = [];
        foreach ($data as $row) {
            if (isset($row[$modelIndex]) && $row[$modelIndex] === $model && isset($row[$typeIndex]) && !in_array($row[$typeIndex], $types)) {
                $types[] = $row[$typeIndex];
            }
        }

        sort($types);
        return $types;
    }

    public static function getMRPData($brand, $model, $type, $year)
    {
        if (empty($brand) || empty($model) || empty($type) || empty($year))
            return null;

        $dummyData = self::getDummyData();
        if (!isset($dummyData[$brand]))
            return null;

        $data = $dummyData[$brand]['data'];
        $fields = $dummyData[$brand]['fields'];
        $modelIndex = array_search('MODEL', $fields);
        $typeIndex = array_search('TYPE', $fields);
        $yearIndex = array_search('TAHUN', $fields);
        $mrpIndex = array_search('MRP', $fields);
        $tdpIndex = array_search('TDP', $fields);
        $ltvIndex = array_search('LTV', $fields);

        if ($modelIndex === false || $typeIndex === false || $yearIndex === false)
            return null;

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
        if ($otr <= 0)
            return 0;
        if ($otr <= 50000000)
            return 215000;
        if ($otr <= 100000000)
            return 265000;
        if ($otr <= 249999999)
            return 365000;
        if ($otr <= 500000000)
            return 615000;
        if ($otr <= 20000000000)
            return 1015000;

        return 0;
    }
}