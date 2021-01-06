<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function sprints()
    {
        return $this->belongsToMany(Sprint::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function lastWeekReports()
    {
        $date = Carbon::today()->subDays(7);
        return $this->reports()->where('created_at', '>=', $date)->get();
    }

    public function getOpenSprints()
    {
        return $this->sprints()->open();
    }

    public function getClosedSprints()
    {
        return $this->sprints()->closed();
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class)->withPivot('starred');
    }

    public function starredProjects()
    {
        return $this->belongsToMany(Project::class)->withPivot('starred')->where('starred', 1);
    }

    /**
     * This function will return the created tasks for the logged user
     * Tasks used to populate the Timer list
     */
    public function tasks()
    {
        return TicketTime::where('user_assembla_id', Auth::user()->user_assembla_id)->whereDate('begin_at', Carbon::today())->get();
    }

    public function hasProject($projectAssemblaId)
    {
        return $this->projects()->where('project_assembla_id', $projectAssemblaId)->count() > 0;
    }

    public function hasSprint($sprintAssemblaId)
    {
        return $this->sprints()->where('sprint_assembla_id', $sprintAssemblaId)->count() > 0;
    }
}
