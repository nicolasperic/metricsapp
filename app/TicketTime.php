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
}
