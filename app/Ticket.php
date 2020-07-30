<?php

namespace App;

use App\Integration\AssemblaGateway;
use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Ticket extends Model
{
    const CLOSED_STATE = 0;
    const OPEN_STATE = 1;
    protected $guarded = [];

    public static function ticketExists($assemblaId)
    {
        return self::where('ticket_assembla_id', $assemblaId)->exists();
    }

    public static function getTicketByAssemblaId($assemblaId)
    {
        return self::where('ticket_assembla_id', $assemblaId)->firstOrFail();
    }

    public function subtasks()
    {
        return $this->belongsToMany(Ticket::class, 'ticket_associations', 'ticket1_id','ticket2_id')->withPivot('relationship')->where('relationship', AssemblaGateway::STORY_RELATION);
    }

    public function parent()
    {
        $parent = false;
        $parentRelation = DB::table('ticket_associations')->where('ticket2_id', $this->id)->where('relationship', AssemblaGateway::STORY_RELATION)->get();


        if (count($parentRelation)) {
            $parent = Ticket::where('id', $parentRelation[0]->ticket1_id)->first();
        }

        return $parent;

    }

    public function getInvalidStatusSubtasks()
    {
        if ($this->is_story && $this->state === self::CLOSED_STATE) {
            //Cualquier estado distinto de done o invalid! Es considerado inconsistente
            //todo status could have a state to easily validate if its open or closed
            return $this->subtasks->whereNotIn('status', ['Done','Invalid']);
        }
        return [];
    }

    public function getSubtasksTotalWorkedHours()
    {
        return $this->subtasks()->sum('worked_hours');
    }

    public function getTotalTrackedTime()
    {
        if ($this->is_story) {
            $userStoryHours = TicketTime::where('ticket_assembla_id', $this->ticket_assembla_id)->sum('hours');

            $this->subtasks->each( function ($subtask) use (&$userStoryHours){
                $userStoryHours += TicketTime::where('ticket_assembla_id', $subtask->ticket_assembla_id)->sum('hours');
            });
            return $userStoryHours;
        }
        return TicketTime::where('ticket_assembla_id', $this->ticket_assembla_id)->sum('hours');
    }

    public function getFormattedName()
    {
        $name = $this->name;
        if (strlen($this->name) > 80) {
            $name = substr($this->name,0,80).'...';
        }
        return $name;
    }

    public function sprints()
    {
        return $this->belongsToMany(Sprint::class);
    }

    public function scopeCompleted($query)
    {
        return $query->where('state', self::CLOSED_STATE);
        //return $query->whereNotNull('completed_at')->where('state', self::CLOSED_STATE);//el ticket 385 estaba en delivered state 0 pero sin fecha de completed_at
    }

    public function scopeStarted($query)
    {
        return $query->whereNotNull('started_at');
    }
    /**
     * Returns the number of days between creating and completing the task
     *
     * @return int days between created_at and completed_at
     */
    public function getLeadTime()
    {
        if ($this->completed_at) {
            return $this->_dateDiff($this->created_at, $this->completed_at);
        }
    }

    /**
     * Returns the number of days between starting and completing the task
     *
     * @return int days between started_at and completed_at
     */
    public function getCycleTime()
    {
        if ($this->completed_at && $this->started_at) {
            return $this->_dateDiff($this->started_at, $this->completed_at);
        }
    }

    /**
     * Secondary function that will calculate the amount of days between two dates
     * @param $startingDate string must be older than the ending date
     * @param $endingDate string must be newer than the starting date
     *
     * @return int
     */
    private function _dateDiff($startingDate, $endingDate)
    {
        $startingDateTime = new DateTime($startingDate);
        $endingDateTime = new DateTime($endingDate);
        if ($startingDateTime > $endingDateTime) {
            return;
        }

        return (int)$startingDateTime->diff($endingDateTime)->format('%a');//%a -> Total number of days
    }

}
