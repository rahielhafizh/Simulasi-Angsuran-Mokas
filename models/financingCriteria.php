<?php

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
