<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $guarded = [];

    public static function projectExists($assemblaId)
    {
        return self::where('project_assembla_id', $assemblaId)->exists();
    }

    public static function getProjectByAssemblaId($projectAssemblaId)
    {
        return self::where('project_assembla_id', $projectAssemblaId)->first();
    }

    public function tickets()
    {
        return $this->hasMany('App\Ticket');
    }

    public function sprints()
    {
        return $this->belongsToMany('App\Sprint');
    }

    public function getOpenSprints()
    {
        return $this->sprints()->open();
    }

    public function getClosedSprints()
    {
        return $this->sprints()->closed();
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function assemblaUsers()
    {
        return $this->belongsToMany(AssemblaUser::class);
    }
}
