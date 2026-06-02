<?php

require_once __DIR__ . '/../config/database.php';

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
