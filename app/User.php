<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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

    public function projects()
    {
        return $this->belongsToMany(Project::class);
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
