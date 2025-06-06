<?php

namespace Potager\Grape\Helpers;

class CreditCard
{

    public static function validate(string $string, ?array $providers = null): bool
    {
        $cards = [
            "amex" => "/^3[47][0-9]{13}$/",
            "bcglobal" => "/^(6541|6556)[0-9]{12}$/",
            "carte_blanche" => "/^389[0-9]{11}$/",
            "diners_club" => "/^3(?:0[0-5]|[68][0-9])[0-9]{11}$/",
            "discover" => "/^65[4-9][0-9]{13}|64[4-9][0-9]{13}|6011[0-9]{12}|(622(?:12[6-9]|1[3-9][0-9]|[2-8][0-9][0-9]|9[01][0-9]|92[0-5])[0-9]{10})$/",
            "insta_payment" => "/^63[7-9][0-9]{13}$/",
            "jcb" => "/^(?:2131|1800|35\d{3})\d{11}$/",
            "koreanloca" => "/^9[0-9]{15}$/",
            "laser" => "/^(6304|6706|6709|6771)[0-9]{12,15}$/",
            "maestro" => "/^(5018|5020|5038|6304|6759|6761|6763)[0-9]{8,15}$/",
            "mastercard" => "/^(5[1-5][0-9]{14}|2(22[1-9][0-9]{12}|2[3-9][0-9]{13}|[3-6][0-9]{14}|7[0-1][0-9]{13}|720[0-9]{12}))$/",
            "solo" => "/^(6334|6767)[0-9]{12}|(6334|6767)[0-9]{14}|(6334|6767)[0-9]{15}$/",
            "switch" => "/^(4903|4905|4911|4936|6333|6759)[0-9]{12}|(4903|4905|4911|4936|6333|6759)[0-9]{14}|(4903|4905|4911|4936|6333|6759)[0-9]{15}|564182[0-9]{10}|564182[0-9]{12}|564182[0-9]{13}|633110[0-9]{10}|633110[0-9]{12}|633110[0-9]{13}$/",
            "union_pay" => "/^(62[0-9]{14,17})$/",
            "visa" => "/^4[0-9]{12}(?:[0-9]{3})?$/",
            "visa_master" => "/^(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]{14})$/"
        ];

        $string = preg_replace('/[- ]+/', '', $string);

        if (is_array($providers)) {
            foreach ($providers as $provider) {
                if (array_key_exists($provider, $cards)) {
                    $pattern = $cards[$provider];
                    if (preg_match($pattern, $string) && LuhnNumber::validate($string)) {
                        return true;
                    }
                }
            }
        } else {
            foreach ($cards as $pattern) {
                if (preg_match($pattern, $string) && LuhnNumber::validate($string)) {
                    return true;
                }
            }
        }

        return false;
    }

}

