<?php

namespace Potager\Grape\Helpers;

class IP
{
    private static function is_ipv4(string $string): bool
    {
        $IPv4SegmentFormat = '(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])';
        $IPv4AddressFormat = "({$IPv4SegmentFormat}[.]){3}{$IPv4SegmentFormat}";
        $IPv4AddressRegExp = "/^{$IPv4AddressFormat}\$/";
        return preg_match($IPv4AddressRegExp, $string) === 1;
    }

    private static function is_ipv6(string $string): bool
    {
        $IPv4SegmentFormat = '(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])';
        $IPv4AddressFormat = "({$IPv4SegmentFormat}[.]){3}{$IPv4SegmentFormat}";

        $IPv6SegmentFormat = '(?:[0-9a-fA-F]{1,4})';
        $IPv6AddressFormat =
            "(?:{$IPv6SegmentFormat}:){7}(?:{$IPv6SegmentFormat}|:)|" .
            "(?:{$IPv6SegmentFormat}:){6}(?:{$IPv4AddressFormat}|:{$IPv6SegmentFormat}|:)|" .
            "(?:{$IPv6SegmentFormat}:){5}(?::{$IPv4AddressFormat}|(:{$IPv6SegmentFormat}){1,2}|:)|" .
            "(?:{$IPv6SegmentFormat}:){4}(?:(:{$IPv6SegmentFormat}){0,1}:{$IPv4AddressFormat}|(:{$IPv6SegmentFormat}){1,3}|:)|" .
            "(?:{$IPv6SegmentFormat}:){3}(?:(:{$IPv6SegmentFormat}){0,2}:{$IPv4AddressFormat}|(:{$IPv6SegmentFormat}){1,4}|:)|" .
            "(?:{$IPv6SegmentFormat}:){2}(?:(:{$IPv6SegmentFormat}){0,3}:{$IPv4AddressFormat}|(:{$IPv6SegmentFormat}){1,5}|:)|" .
            "(?:{$IPv6SegmentFormat}:){1}(?:(:{$IPv6SegmentFormat}){0,4}:{$IPv4AddressFormat}|(:{$IPv6SegmentFormat}){1,6}|:)|" .
            "(?::((?::{$IPv6SegmentFormat}){0,5}:{$IPv4AddressFormat}|(?::{$IPv6SegmentFormat}){1,7}|:))";

        $IPv6AddressRegExp = "/^({$IPv6AddressFormat})(%[0-9a-zA-Z.]{1,})?\$/";
        return preg_match($IPv6AddressRegExp, $string) === 1;
    }

    public static function validate(string $string, ?string $version = null): bool
    {
        if ($version === 'ipv4') {
            return self::is_ipv4($string);
        }

        if ($version === 'ipv6') {
            return self::is_ipv6($string);
        }

        return self::is_ipv4($string) || self::is_ipv6($string);
    }
}
