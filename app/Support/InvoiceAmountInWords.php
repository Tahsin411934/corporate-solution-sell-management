<?php

namespace App\Support;

class InvoiceAmountInWords
{
    public static function format(string $amount, string $currency = 'BDT'): string
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '0');
        $fraction = (int) substr(str_pad($fraction, 2, '0'), 0, 2);
        return self::integer((int) $whole).' '.($currency === 'BDT' ? 'Taka' : $currency)
            .($fraction ? ' And '.self::integer($fraction).' '.($currency === 'BDT' ? 'Paisa' : 'Cents') : '').' Only';
    }

    private static function integer(int $number): string
    {
        $small = ['Zero', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
            'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        if ($number < 0) { return 'Minus '.self::integer(-$number); }
        if ($number < 20) { return $small[$number]; }
        if ($number < 100) { return $tens[intdiv($number, 10)].($number % 10 ? ' '.self::integer($number % 10) : ''); }
        foreach ([1000000000000 => 'Trillion', 1000000000 => 'Billion', 1000000 => 'Million', 1000 => 'Thousand', 100 => 'Hundred'] as $size => $label) {
            if ($number >= $size) {
                return self::integer(intdiv($number, $size)).' '.$label.($number % $size ? ' '.self::integer($number % $size) : '');
            }
        }
        return '';
    }
}
