<?php

class CurrencyFormatter
{
    public static function format($value)
    {
        if ($value == 0 || $value === null) {
            return '0';
        }

        $intValue = round(floatval($value));
        return number_format($intValue, 0, ',', '.');
    }

    public static function formatWithSymbol($value)
    {
        return 'Rp ' . self::format($value);
    }

    public static function formatWithoutSymbol($value)
    {
        if ($value == 0 || $value === null) {
            return '0';
        }

        $intValue = round(floatval($value));
        return number_format($intValue, 0, ',', '.');
    }

    public static function parse($value)
    {
        if (empty($value)) {
            return 0;
        }

        $cleaned = preg_replace('/[^0-9,]/', '', $value);
        $cleaned = str_replace(',', '.', $cleaned);
        return floatval($cleaned);
    }
}
