<!-- dropdownOptions.php -->
<?php

class DropdownOptions
{
    public static $typeAngsuran = ['ADDB', 'ADDM'];

    public static $kodePlat = [
        'A',
        'AA',
        'AB',
        'AD',
        'AE',
        'AG',
        'B',
        'BA',
        'BB',
        'BD',
        'BE',
        'BG',
        'BH',
        'BK',
        'BL',
        'BM',
        'BN',
        'BP',
        'D',
        'DA',
        'DB',
        'DC',
        'DD',
        'DE',
        'DG',
        'DH',
        'DK',
        'DL',
        'DM',
        'DN',
        'DP',
        'DR',
        'DT',
        'DW',
        'E',
        'EA',
        'EB',
        'ED',
        'F',
        'G',
        'H',
        'K',
        'KB',
        'KH',
        'KT',
        'KU',
        'L',
        'M',
        'N',
        'P',
        'PA',
        'PB',
        'R',
        'S',
        'T',
        'W',
        'Z'
    ];

    public static $tenor = ['12', '24', '36', '48', '60'];

    public static $negoBunga = [
        '-2.00%',
        '-1.50%',
        '-1.00%',
        '-0.50%',
        '0.00%',
        '+0.50%',
        '+1.00%',
        '+1.50%',
        '+2.00%'
    ];

    const DEFAULT_NEGO_BUNGA = '0.00%';

    public static $asuransiUnit = ['FULL COMPRE', 'FULL TLO', 'COMBI 1'];

    public static $passComm = ['PASSENGER', 'COMMERCIAL'];

    const DEFAULT_PASS_COMM = 'PASSENGER';

    public static function getTahunOptions()
    {
        $currentYear = date('Y');
        $startYear = $currentYear - 11;
        $endYear = $currentYear;
        $years = [];
        for ($i = $endYear; $i >= $startYear; $i--) {
            $years[] = (string) $i;
        }
        return $years;
    }

    public static function getInsuranceRegion($kodePlat)
    {
        if (!is_string($kodePlat) || empty($kodePlat)) {
            return null;
        }

        $region1 = ['BA', 'BB', 'BD', 'BE', 'BG', 'BH', 'BK', 'BL', 'BM', 'BN', 'BP'];
        $region2 = ['A', 'B', 'D', 'E', 'F', 'T', 'Z'];
        $region3 = [
            'G',
            'H',
            'K',
            'R',
            'AA',
            'AB',
            'AD',
            'L',
            'M',
            'N',
            'P',
            'S',
            'W',
            'AE',
            'AG',
            'DH',
            'DK',
            'DR',
            'EA',
            'EB',
            'ED',
            'DA',
            'KB',
            'KH',
            'KT',
            'KU',
            'DB',
            'DC',
            'DD',
            'DL',
            'DM',
            'DN',
            'DP',
            'DT',
            'DW',
            'DE',
            'DG',
            'PA',
            'PB'
        ];

        if (in_array($kodePlat, $region1, true)) {
            return 1;
        }

        if (in_array($kodePlat, $region2, true)) {
            return 2;
        }

        if (in_array($kodePlat, $region3, true)) {
            return 3;
        }

        return null;
    }
}
