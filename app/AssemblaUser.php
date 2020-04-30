<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AssemblaUser extends Model
{
    public static function getUserByAssemblaId($userAssemblaId)
    {
        return self::where('user_assembla_id', $userAssemblaId)->firstOrFail();
    }
}
