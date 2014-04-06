<?php
namespace Fandepay\Api;

class Utils
{
    /**
     * Hmac hash készítése
     * @param string $data
     * @param string $key Titkosító kulcs
     * @return string
     */
    public static function hmach($data, $key)
    {
        if (function_exists('hash_hmac')) {
            $hmac = hash_hmac('md5', $data, $key);
        } else {
            $b = 64; // byte length for md5
            if (strlen($key) > $b) {
                $key = pack("H*", md5($key));
            }
            $key = str_pad($key, $b, chr(0x00));
            $ipad = str_pad('', $b, chr(0x36));
            $opad = str_pad('', $b, chr(0x5c));
            $k_ipad = $key ^ $ipad;
            $k_opad = $key ^ $opad;

            $hmac = md5($k_opad . pack("H*", md5($k_ipad . $data)));
        }

        return $hmac;
    }

    /**
     * Többdimenziós tömb 1 dimenzióssá alakítása
     * @param array $input
     * @return array
     */
    public static function flatArray(array $input)
    {
        $out = array();
        foreach ($input as $elem) {
            if (is_array($elem)) {
                $out = array_merge($out, self::flatArray($elem));
            } else {
                if ($elem instanceof \DateTime) {
                    $elem = $elem->format('Y-m-d H:i:s');
                }

                $out[] = $elem;
            }
        }

        return $out;
    }

    /**
     * String camelcase formába konvertálása
     * @param string $str
     * @return string
     */
    public static function camelcase($str)
    {
        return str_replace(" ", "", ucwords(strtr($str, "_-", "  ")));
    }

    public static function createStrWithLengths(array $data)
    {
        $str = '';
        foreach ($data as $d) {
            $str .= strlen($d).$d;
        }

        return $str;
    }
}
