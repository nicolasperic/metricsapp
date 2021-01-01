<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AssemblaUser extends Model
{
    protected $guarded = [];

    public function projects()
    {
        return $this->belongsToMany(Project::class);
    }

    public static function getUserByAssemblaId($userAssemblaId)
    {
        return self::where('user_assembla_id', $userAssemblaId)->firstOrFail();
    }


    public static function userExists($userAssemblaId)
    {
        return self::where('user_assembla_id', $userAssemblaId)->exists();
    }
}
