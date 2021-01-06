<?php

namespace Tests\Unit;

use App\Report;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function can_retrieve_user_reports()
    {
        $report = factory(Report::class)->states('processed')->create();


        $user = factory(User::class)->create();
        $user->reports()->save($report);

        $this->assertEquals(count($user->reports),1);
        //$this->assertEquals($ticketWithAssemblaId->number, $ticket->number);
        //$this->assertEquals($ticketWithAssemblaId->ticket_assembla_id, $ticket->ticket_assembla_id);
    }

    /** @test */
    function can_retrieve_user_reports_from_last_week()
    {
        $reportA = factory(Report::class)->states('processed')->create([
            'created_at' => \Carbon\Carbon::today()->subDays(8),
        ]);
        $reportB = factory(Report::class)->states('processed')->create([
            'created_at' => \Carbon\Carbon::today()->subDays(2),
        ]);
        $reportC = factory(Report::class)->states('processed')->create([
            'created_at' => \Carbon\Carbon::today()->subDays(5),
        ]);
        $reportD = factory(Report::class)->states('processed')->create([
            'created_at' => \Carbon\Carbon::today()->subDays(3),
        ]);


        $user = factory(User::class)->create();
        $user->reports()->saveMany([$reportA, $reportB, $reportC, $reportD]);


        $this->assertEquals(count($user->lastWeekReports()), 3);
        //$this->assertEquals($ticketWithAssemblaId->number, $ticket->number);
        //$this->assertEquals($ticketWithAssemblaId->ticket_assembla_id, $ticket->ticket_assembla_id);
    }

    /** @test */
    function can_retrive_report_status_label()
    {
        $pending = factory(Report::class)->create([
            'created_at' => \Carbon\Carbon::today()->subDays(8),
            'status' => Report::PENDING_STATUS,
        ]);
        $processed = factory(Report::class)->create([
            'created_at' => \Carbon\Carbon::today()->subDays(8),
            'status' => Report::PROCESSED_STATUS,
        ]);
        $running  = factory(Report::class)->create([
            'created_at' => \Carbon\Carbon::today()->subDays(8),
            'status' => Report::RUNNING_STATUS,
        ]);
        $failed  = factory(Report::class)->create([
            'created_at' => \Carbon\Carbon::today()->subDays(8),
            'status' => Report::FAILED_STATUS,
        ]);

        $this->assertEquals($pending->getStatusLabel(), 'Pending');
        $this->assertEquals($processed->getStatusLabel(), 'Processed');
        $this->assertEquals($running->getStatusLabel(), 'Running');
        $this->assertEquals($failed->getStatusLabel(), 'Failed');

    }
}
