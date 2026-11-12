<?php

namespace App\Services;

class NumberToWords
{
    private const ONES = [
        0 => 'Zero', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
        6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
        11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
        16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen',
    ];

    private const TENS = [
        2 => 'Twenty', 3 => 'Thirty', 4 => 'Forty', 5 => 'Fifty',
        6 => 'Sixty', 7 => 'Seventy', 8 => 'Eighty', 9 => 'Ninety',
    ];

    public static function shillings(float $amount): string
    {
        $amount = round(max(0, $amount), 2);
        $whole = (int) floor($amount);
        $cents = (int) round(($amount - $whole) * 100);

        $words = self::convert($whole).' Shillings';
        if ($cents > 0) {
            $words .= ' and '.self::convert($cents).' Cents';
        }

        return $words.' Only';
    }

    public static function convert(int $number): string
    {
        if ($number < 0) {
            return 'Minus '.self::convert(abs($number));
        }
        if ($number === 0) {
            return self::ONES[0];
        }

        foreach (self::groups() as $value => $label) {
            if ($number >= $value) {
                $count = intdiv($number, $value);
                $remainder = $number % $value;
                $words = self::convert($count).' '.$label;

                return $remainder > 0 ? $words.' '.self::convert($remainder) : $words;
            }
        }

        if ($number >= 100) {
            $hundreds = intdiv($number, 100);
            $remainder = $number % 100;
            $words = self::ONES[$hundreds].' Hundred';

            return $remainder > 0 ? $words.' and '.self::convert($remainder) : $words;
        }

        if ($number >= 20) {
            $tens = intdiv($number, 10);
            $remainder = $number % 10;

            return $remainder > 0 ? self::TENS[$tens].'-'.self::ONES[$remainder] : self::TENS[$tens];
        }

        return self::ONES[$number];
    }

    private static function groups(): array
    {
        return [
            1000000000000 => 'Trillion',
            1000000000 => 'Billion',
            1000000 => 'Million',
            1000 => 'Thousand',
        ];
    }
}
