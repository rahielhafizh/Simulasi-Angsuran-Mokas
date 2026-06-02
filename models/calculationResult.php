<?php

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
        return new CalculationResult(
            0,
            0,
            0,
            0,
            0,
            [],
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0
        );
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
