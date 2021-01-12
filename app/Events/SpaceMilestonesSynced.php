<?php

namespace App\Events;

use App\Project;
use App\User;
use Illuminate\Broadcasting\InteractsWithSockets;;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SpaceMilestonesSynced
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    /**
     * @var Project
     */
    private $project;
    /**
     * @var User
     */
    private $user;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user, Project $project)
    {
        $this->project = $project;
        $this->user = $user;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }

    public function getProject()
    {
        return $this->project;
    }

    public function getUser()
    {
        return $this->user;
    }
}
