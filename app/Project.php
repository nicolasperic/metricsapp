<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    const ESTIMATE_POINTS = 1;
    const ESTIMATE_TIME = 2;
    const ESTIMATE_SIZE = 3;//small, medium, large

    protected $guarded = [];

    public static function projectExists($assemblaId)
    {
        return self::where('project_assembla_id', $assemblaId)->exists();
    }

    public static function getProjectByAssemblaId($projectAssemblaId)
    {
        return self::where('project_assembla_id', $projectAssemblaId)->with('assemblaUsers')->first();
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function isUserOwner(User $user)
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['user_id','user_role', 'user_status'])
            ->where('user_role', User::ROLE_OWNER)
            ->where('user_status', User::STATUS_ACCEPTED)
            ->where('user_id', $user->id)
            ->count() > 0;
    }

    public function getUserRole(User $user)
    {
        $userRole = false;
        $user = $this->belongsToMany(User::class)
            ->withPivot(['user_id','user_role', 'user_status'])
            ->where('user_id', $user->id)->first();
        if ($user !== null) {
            $userRole = ucfirst($user->pivot->user_role);
        }

        return $userRole;
    }

    public function stats()
    {
        return $this->hasMany('App\Models\ProjectStat');
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

    public function getCurrentSprint()
    {
        return $this->getOpenSprints()->current()->first();
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

    public function sprintIteration()
    {
        return $this->hasOne(SprintIteration::class);
    }
}
