<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketTime extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function ticket()
    {
        return Ticket::getTicketByAssemblaId($this->ticket_assembla_id);
    }

    public static function ticketTimeExists($assemblaId)
    {
        return self::where('ticket_time_assembla_id', $assemblaId)->exists();
    }

    public static function getTicketTimeByAssemblaId($assemblaId)
    {
        return self::where('ticket_time_assembla_id', $assemblaId)->first();
    }
}
