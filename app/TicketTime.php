<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TicketTime extends Model
{
    public function ticket()
    {
        return Ticket::getTicketByAssemblaId($this->ticket_assembla_id);
    }
}
