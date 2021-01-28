<?php
/**
 * Created by PhpStorm.
 * User: nico
 * Date: 1/10/21
 * Time: 9:46 PM
 */

namespace App\Helper;
use App\Reports\HoursByUserReport;
use App\Reports\HoursByUSReport;
use App\Reports\SprintsReport;
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
        if ($percentageValue < 30) {//TODO this values should be configurable for the users
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

    /**
     * Will return last week first day based on the current date
     * @return string
     */
    public static function getLastWeekMonday()
    {
        $now = Carbon::now();
        return $now->subDays($now->dayOfWeek-1)->subWeek()->format('Y/m/d');
    }

    /**
     * Will return last week last day based on the current date
     * @return string
     */
    public static function getLastWeekSunday()
    {
        $now = Carbon::now();
        return $now->subDays($now->dayOfWeek)->format('Y/m/d');
    }

    /**
     * Will return this week first day based on the current date
     * @return mixed
     */
    public static function getThisWeekMonday()
    {
        return Carbon::now()->startOfWeek()->format('Y/m/d');
    }

    /**
     * Will return this week last day based on the current date
     * @return string
     */
    public static function getThisWeekSunday()
    {
        return Carbon::now()->endOfWeek()->format('Y/m/d');
    }

    /**
     * Will return this month first day based on the current date
     * @return string
     */
    public static function getThisMonthFirstDay()
    {
        return Carbon::now()->startOfMonth()->format('Y/m/d');
    }

    /**
     * Will return this month last day based on the current date
     * @return string
     */
    public static function getThisMonthLastDay()
    {
        return Carbon::now()->endOfMonth()->format('Y/m/d');
    }

    /**
     * Will return last month first day based on the current date
     * @return string
     */
    public static function getLastMonthFirstDay()
    {
        return Carbon::now()->startOfMonth()->subMonth()->format('Y/m/d');
    }

    /**
     * Will return last month last day based on the current date
     * @return string
     */
    public static function getLastMonthLastDay()
    {
        return Carbon::now()->subMonth()->endOfMonth()->format('Y/m/d');
    }

    //TODO this should be on each Report model when I can figure out how to load different instances classes
    public static function getReportNotificationBackground($type)
    {
        $bgClass = 'bg-info';
        switch ($type) {
            case HoursByUserReport::REPORT_TYPE:
                $bgClass = 'bg-success';
                break;
            case HoursByUSReport::REPORT_TYPE:
                $bgClass = 'bg-danger';
                break;
            case SprintsReport::REPORT_TYPE:
                $bgClass = 'bg-warning';
                break;
        }

        return $bgClass;
    }

    /**
     * This function will receive 2021/01/18 00:00 and return 2021/01/18
     * @param $dateString
     */
    public static function getDateWithoutHours($dateString)
    {
        return explode(' ',$dateString)[0];
    }
}