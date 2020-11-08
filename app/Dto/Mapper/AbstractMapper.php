<?php

namespace App\Dto\Mapper;

use Carbon\Carbon;

abstract class AbstractMapper
{
   
    protected static function getParsedDate($date)
    {
        if (strlen($date)) {
            $date = Carbon::parse($date);
        }

        return $date;
    }

}