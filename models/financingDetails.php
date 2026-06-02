<?php

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
        return [
            'mrpPengajuan' => $this->mrpPengajuan,
            'dp' => $this->dp,
        ];
    }
}
