<?php
/**
 * Created by PhpStorm.
 * User: nico
 * Date: 1/10/21
 * Time: 9:46 PM
 */

namespace App\Helper;
use Carbon\Carbon;

/**
 * Using this class for functions I've needed as helpers to reduce repeating code
 * Class Helper
 *
 * @package App\Helper
 */
class Helper {

    /**
     * If the received string length is greater than the received length value
     * we return the substring if not, no changes are made to the string var
     *
     * @param     $string
     * @param int $length
     *
     * @return string
     */
    public static function substrIf($string, $length = 80)
    {
        if (strlen($string) > $length) {
            $string = substr($string,0,$length).'...';
        }
        return $string;
    }

    public static function getPercentageClass($percentageValue)
    {
        if ($percentageValue < 30) {
            $percentageClass = 'bg-danger';
        } else if ($percentageValue >= 30 && $percentageValue < 75) {
            $percentageClass = 'bg-warning';
        } else {
            $percentageClass = 'bg-success';
        }

        return $percentageClass;
    }

    public static function getTimeDiff($time)
    {
        return $time->diffForHumans(Carbon::now());
    }

    public static function getPercentageValue($subtotal, $total, $decimals = 0)
    {
        if ($total == 0) {
            return 0;
        }
        return number_format(($subtotal / $total) * 100, $decimals);
    }
}