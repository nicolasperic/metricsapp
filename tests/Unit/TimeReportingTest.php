<?php

namespace Tests\Unit;

use App\Sprint;
use App\Ticket;
use App\TicketTime;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimeReportingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function can_calculate_montly_hours()
    {
        /*
         * Organizar las horas insumidas en el Sprint
         * Por persona
         * por mes
         *      por semana
         *
         * Sprint > tiene >  User Stories > tienen Subtasks > horas trackeadas con fecha!
         *
         *
         * begin_at
         * hours
         * user_assembla_id
         *
         */
        $sprint = factory(Sprint::class)->create();

        /** @var Ticket $userstory */
        $userstory = factory(Ticket::class)->create([
            'ticket_assembla_id' => 'userstory1',
        ]);
        $subtaskA = factory(Ticket::class)->create([
            'ticket_assembla_id' => 'subtaska',
            'name'               => 'TIC-1: subtask name A',
            'is_story'           => false,
            'worked_hours'       => 5
        ]);
        factory(TicketTime::class)->create([
            'hours'              => 1.5,
            'ticket_assembla_id' => 'subtaska',
            'begin_at'           => Carbon::parse('2020-07-23'),
            'user_assembla_id'   => 'foco123',
        ]);
        factory(TicketTime::class)->create([
            'hours'              => 1.5,
            'ticket_assembla_id' => 'subtaska',
            'begin_at'           => Carbon::parse('2020-07-15'),
            'user_assembla_id'   => 'foco123',
        ]);
        factory(TicketTime::class)->create([
            'hours'              => 2,
            'ticket_assembla_id' => 'subtaska',
            'begin_at'           => Carbon::parse('2020-07-24'),
            'user_assembla_id'   => 'jona123',
        ]);
        $subtaskB = factory(Ticket::class)->create([
            'ticket_assembla_id' => 'subtaskb',
            'name'               => 'TIC-2: subtask name B',
            'is_story'           => false,
            'worked_hours'       => 3,
        ]);
        factory(TicketTime::class)->create([
            'hours'              => 1,
            'ticket_assembla_id' => 'subtaskb',
            'begin_at'           => Carbon::parse('2020-07-02'),
            'user_assembla_id'   => 'jona123',
        ]);
        factory(TicketTime::class)->create([
            'hours'              => 2,
            'ticket_assembla_id' => 'subtaskb',
            'begin_at'           => Carbon::parse('2020-07-23'),
            'user_assembla_id'   => 'nico123',
        ]);
        $subtaskC = factory(Ticket::class)->create([
            'ticket_assembla_id' => 'subtaskc',
            'name'               => 'TIC-3: subtask name C',
            'is_story'           => false,
            'worked_hours'       => 1,
        ]);
        factory(TicketTime::class)->create([
            'hours'              => 1,
            'ticket_assembla_id' => 'subtaskc',
            'begin_at'           => Carbon::parse('2020-07-08'),
            'user_assembla_id'   => 'foco123',
        ]);

        $relatedD = factory(Ticket::class)->create([
            'ticket_assembla_id' => 'relatedd',
            'name'               => 'TIC-4: realted Story D',
            'is_story'           => true,
            'worked_hours'       => 10,
        ]);
        factory(TicketTime::class)->create([
            'hours'              => 10,
            'ticket_assembla_id' => 'relatedd',
            'begin_at'           => Carbon::parse('2020-07-18'),
            'user_assembla_id'   => 'jona123',
        ]);

        $userstory->subtasks()->save($subtaskA, ['relationship' => 5]);
        $userstory->subtasks()->save($subtaskB, ['relationship' => 5]);
        $userstory->subtasks()->save($subtaskC, ['relationship' => 5]);
        $userstory->subtasks()->save($relatedD, ['relationship' => 2]);

        $this->assertEquals(9, $userstory->getSubtasksTotalWorkedHours());
        $this->assertEquals(9, $userstory->getTotalTrackedTime());

        $sprint->tickets()->saveMany([$userstory, $subtaskA, $subtaskB, $subtaskC, $relatedD]);

        //$this->assertEquals(2, $sprint->getTotalTickets());

        print $sprint->getTotalTickets().' total tickets'.PHP_EOL;

        $date = Carbon::parse('2020-07-23');
        $monday = $date->startOfWeek()->format('Y-m-d'); // monday
        $sunday = $date->endOfWeek()->format('Y-m-d');
        $weekOfYer = $date->weekOfYear;
        $month = $date->month;


        print count($sprint->tickets).' total tickets from sprint object'.PHP_EOL;

        $weeklyHours = array();//week => total XXX, users [ foco => hs]
        $montlyHours = array();//month => total XY, users [ foco => hs]
        foreach ($sprint->tickets as $ticket) {


            //dd($ticket);
            $ticketTimes = TicketTime::where('ticket_assembla_id', $ticket->ticket_assembla_id)->get();
            print $ticket->ticket_assembla_id ."----> ". count($ticketTimes).PHP_EOL;
            foreach ($ticketTimes as $ticketTime) {
                print $ticketTime->ticket_number . ' ' . $ticketTime->hours . ' ' . $ticketTime->begin_at.' '.$ticketTime->user_assembla_id.PHP_EOL;
                $date = Carbon::parse($ticketTime->begin_at);
                $monday = $date->startOfWeek()->format('Y-m-d'); // monday
                $sunday = $date->endOfWeek()->format('Y-m-d');
                $weekOfYear = $date->weekOfYear;
                $month = $date->month;
                print $ticketTime->begin_at. ' mes: '.$month.' weekOfYear '.$weekOfYear.' monday '.$monday.' sunday '.$sunday.PHP_EOL;
                //dd($ticketTime->ticket_number . ' ' . $ticketTime->hours . ' ' . $ticketTime->begin_at);

                $montlyHours = $this->_trackMonthlyHours($ticketTime, $montlyHours);
                $weeklyHours = $this->_trackWeeklyHours($ticketTime, $weeklyHours);
            }


        }

        ksort($weeklyHours);
        print print_r($weeklyHours, 1).PHP_EOL;
        ksort($montlyHours);
        print print_r($montlyHours, 1).PHP_EOL;
    }

    private function _trackMonthlyHours($ticketTime, $monthlyHours)
    {
        $date = Carbon::parse($ticketTime->begin_at);
        $month = $date->month;
        if (array_key_exists($month, $monthlyHours)) {
            $monthlyHours[$month]['total'] += $ticketTime->hours;
            $monthlyHours[$month]['label'] = $date->format('F');;
            if (array_key_exists($ticketTime->user_assembla_id, $monthlyHours[$month])) {
                //print print_r($monthlyHours,1).PHP_EOL;
                $monthlyHours[$month][$ticketTime->user_assembla_id]['hours'] += $ticketTime->hours;
                $monthlyHours[$month][$ticketTime->user_assembla_id]['tasks'] += 1;
            } else {
                $monthlyHours[$month][$ticketTime->user_assembla_id]['hours'] = $ticketTime->hours;
                $monthlyHours[$month][$ticketTime->user_assembla_id]['tasks'] = 1;
            }
        } else {
            $monthlyHours[$month]['total'] = $ticketTime->hours;
            $monthlyHours[$month][$ticketTime->user_assembla_id]['hours'] = $ticketTime->hours;
            $monthlyHours[$month][$ticketTime->user_assembla_id]['tasks'] = 1;
        }
        return $monthlyHours;
    }
    private function _trackWeeklyHours($ticketTime, $weeklyHours)
    {
        $date = Carbon::parse($ticketTime->begin_at);
        $monday = $date->startOfWeek()->format('Y-m-d'); // monday
        $sunday = $date->endOfWeek()->format('Y-m-d');
        $weekOfYear = $date->weekOfYear;

        if (array_key_exists($weekOfYear , $weeklyHours)) {
            $weeklyHours[$weekOfYear]['total'] += $ticketTime->hours;
            $weeklyHours[$weekOfYear]['total_tasks'] += 1;
            $weeklyHours[$weekOfYear]['surr_monday'] = $monday;
            $weeklyHours[$weekOfYear]['surr_sunday'] = $sunday;
            if (array_key_exists($ticketTime->user_assembla_id, $weeklyHours[$weekOfYear])) {
                $weeklyHours[$weekOfYear][$ticketTime->user_assembla_id]['hours'] += $ticketTime->hours;
                $weeklyHours[$weekOfYear][$ticketTime->user_assembla_id]['tasks'] += 1;
            } else {
                $weeklyHours[$weekOfYear][$ticketTime->user_assembla_id]['hours'] = $ticketTime->hours;
                $weeklyHours[$weekOfYear][$ticketTime->user_assembla_id]['tasks'] = 1;
            }
        } else {
            $weeklyHours[$weekOfYear]['total'] = $ticketTime->hours;
            $weeklyHours[$weekOfYear]['total_tasks'] = 1;
            $weeklyHours[$weekOfYear]['surr_monday'] = $monday;
            $weeklyHours[$weekOfYear]['surr_sunday'] = $sunday;
            $weeklyHours[$weekOfYear][$ticketTime->user_assembla_id]['hours'] = $ticketTime->hours;
            $weeklyHours[$weekOfYear][$ticketTime->user_assembla_id]['tasks'] = 1;
        }
        return $weeklyHours;
    }

}
