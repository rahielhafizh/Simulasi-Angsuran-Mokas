<!-- currencyFormatter.php -->
<?php

class CurrencyFormatter
{
    public static function format($value)
    {
        if ($value === null || $value == 0)
            return '0';
        return number_format(round(floatval($value)), 0, ',', '.');
    }

    public static function formatWithSymbol($value)
    {
        return 'Rp ' . self::format($value);
    }

    public static function formatWithoutSymbol($value)
    {
        return self::format($value);
    }

    public static function parse($value)
    {
        if (empty($value))
            return 0;

        $cleaned = str_replace(',', '.', preg_replace('/[^0-9,]/', '', $value));
        return floatval($cleaned);
    }
}