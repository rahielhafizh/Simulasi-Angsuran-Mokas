<?php
// financingModels.php

class FinancingCriteria
{
    public $typeAngsuran;
    public $kodePlat;
    public $merk;
    public $model;
    public $type;
    public $tahun;
    public $tenor;
    public $negoBunga;
    public $asuransiUnit;
    public $passComm;
    public $unitCategoryName;
    public $unitSegmentName;

    public function __construct()
    {
        $this->typeAngsuran = null;
        $this->kodePlat = null;
        $this->merk = null;
        $this->model = null;
        $this->type = null;
        $this->tahun = null;
        $this->tenor = null;
        $this->negoBunga = '0.00%';
        $this->asuransiUnit = null;
        $this->passComm = 'PASSENGER';
        $this->unitCategoryName = null;
        $this->unitSegmentName = null;
    }

    public function checkValidation()
    {
        return !empty($this->typeAngsuran) &&
            !empty($this->kodePlat) &&
            !empty($this->merk) &&
            !empty($this->model) &&
            !empty($this->type) &&
            !empty($this->tahun) &&
            !empty($this->tenor) &&
            !empty($this->negoBunga) &&
            !empty($this->asuransiUnit) &&
            !empty($this->passComm);
    }

    public function reset()
    {
        $this->typeAngsuran = null;
        $this->kodePlat = null;
        $this->merk = null;
        $this->model = null;
        $this->type = null;
        $this->tahun = null;
        $this->tenor = null;
        $this->negoBunga = '0.00%';
        $this->asuransiUnit = null;
        $this->passComm = 'PASSENGER';
        $this->unitCategoryName = null;
        $this->unitSegmentName = null;
    }

    public function toArray()
    {
        return [
            'typeAngsuran' => $this->typeAngsuran,
            'kodePlat' => $this->kodePlat,
            'merk' => $this->merk,
            'model' => $this->model,
            'type' => $this->type,
            'tahun' => $this->tahun,
            'tenor' => $this->tenor,
            'negoBunga' => $this->negoBunga,
            'asuransiUnit' => $this->asuransiUnit,
            'passComm' => $this->passComm,
            'unitCategoryName' => $this->unitCategoryName,
            'unitSegmentName' => $this->unitSegmentName,
        ];
    }
}

class FinancingDetails
{
    public $mrpPengajuan;
    public $dp;

    public function __construct($mrpPengajuan = 0, $dp = 0)
    {
        $this->mrpPengajuan = $mrpPengajuan;
        $this->dp = $dp;
    }

    public function checkValidation()
    {
        return $this->mrpPengajuan > 0 && $this->dp >= 0;
    }

    public function reset()
    {
        $this->mrpPengajuan = 0;
        $this->dp = 0;
    }

    public function toArray()
    {
        return ['mrpPengajuan' => $this->mrpPengajuan, 'dp' => $this->dp];
    }
}

class CalculationResult
{
    public $angsuranPerBulan;
    public $allIn;
    public $pelunasan;
    public $refund;
    public $totalPremiAsuransi;
    public $premiAsuransiTahunan;
    public $pokokHutang1;
    public $pokokHutang2;
    public $pokokHutang3;
    public $totalPokokHutangFinal;
    public $biayaAdministrasi;
    public $biayaFidusia;
    public $biayaProvisi;
    public $lifeInsurance;
    public $totalBunga;
    public $totalNetAR;
    public $tdp;

    public function __construct(
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
        $biayaAdministrasi,
        $biayaFidusia,
        $biayaProvisi,
        $lifeInsurance,
        $totalBunga,
        $totalNetAR,
        $tdp
    ) {
        $this->angsuranPerBulan = round($angsuranPerBulan);
        $this->allIn = round($allIn);
        $this->pelunasan = round($pelunasan);
        $this->refund = round($refund);
        $this->totalPremiAsuransi = round($totalPremiAsuransi);
        $this->premiAsuransiTahunan = $premiAsuransiTahunan;
        $this->pokokHutang1 = round($pokokHutang1);
        $this->pokokHutang2 = round($pokokHutang2);
        $this->pokokHutang3 = round($pokokHutang3);
        $this->totalPokokHutangFinal = round($totalPokokHutangFinal);
        $this->biayaAdministrasi = round($biayaAdministrasi);
        $this->biayaFidusia = round($biayaFidusia);
        $this->biayaProvisi = round($biayaProvisi);
        $this->lifeInsurance = round($lifeInsurance);
        $this->totalBunga = round($totalBunga);
        $this->totalNetAR = round($totalNetAR);
        $this->tdp = round($tdp);
    }

    public static function empty()
    {
        // DIISI ARRAY KOSONG KARENA BUKAN NUMERIK DAN TIDAK DIBULATKAN
        return new CalculationResult(0, 0, 0, 0, 0, [], 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
    }

    public function isEmpty()
    {
        return $this->angsuranPerBulan == 0 &&
            $this->allIn == 0 &&
            $this->pelunasan == 0 &&
            $this->refund == 0 &&
            $this->totalPremiAsuransi == 0 &&
            $this->pokokHutang1 == 0 &&
            $this->pokokHutang2 == 0 &&
            $this->pokokHutang3 == 0 &&
            $this->totalPokokHutangFinal == 0 &&
            $this->totalBunga == 0 &&
            $this->totalNetAR == 0;
    }

    public function toArray()
    {
        return [
            'angsuranPerBulan' => $this->angsuranPerBulan,
            'allIn' => $this->allIn,
            'pelunasan' => $this->pelunasan,
            'refund' => $this->refund,
            'totalPremiAsuransi' => $this->totalPremiAsuransi,
            'premiAsuransiTahunan' => $this->premiAsuransiTahunan,
            'pokokHutang1' => $this->pokokHutang1,
            'pokokHutang2' => $this->pokokHutang2,
            'pokokHutang3' => $this->pokokHutang3,
            'totalPokokHutangFinal' => $this->totalPokokHutangFinal,
            'biayaAdministrasi' => $this->biayaAdministrasi,
            'biayaFidusia' => $this->biayaFidusia,
            'biayaProvisi' => $this->biayaProvisi,
            'lifeInsurance' => $this->lifeInsurance,
            'totalBunga' => $this->totalBunga,
            'totalNetAR' => $this->totalNetAR,
            'tdp' => $this->tdp,
        ];
    }
}