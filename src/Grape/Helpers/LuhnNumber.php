<?php

namespace Potager\Grape\Helpers;

class LuhnNumber
{
    public static function validate(string $string): bool
    {
        $sum = 0;
        $length = strlen($string);
        $reverse = strrev($string);

        // Loop through the digits in reverse order
        for ($i = 0; $i < $length; $i++) {
            $digit = (int) $reverse[$i];

            // Double every second digit
            if ($i % 2 == 1) {
                $digit *= 2;
                // If doubling the digit results in a number greater than 9, subtract 9 from it
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            $sum += $digit;
        }

        // If the sum modulo 10 is 0, then the number is valid
        return $sum % 10 === 0;
    }
}