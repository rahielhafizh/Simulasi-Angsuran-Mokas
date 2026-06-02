<?php

require_once __DIR__ . '/../config/database.php';

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
            $currentYear = (int)date('Y');
            $tahun = (int)$criteria->tahun;
            
            if ($tahun < 1900 || $tahun > ($currentYear + 1)) {
                throw new Exception('TAHUN VALUE OUT OF VALID RANGE');
            }
        }

        if ($criteria->tenor !== null && (int)$criteria->tenor <= 0) {
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
            $tahun = $criteria->tahun !== null ? (int)$criteria->tahun : null;
            $tenor = $criteria->tenor !== null ? (int)$criteria->tenor : null;

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