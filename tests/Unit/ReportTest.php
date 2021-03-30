<?php

namespace Tests\Unit;

use App\Report;
use App\Reports\HoursByUSReport;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function can_retrieve_user_reports()
    {
        $report = HoursByUSReport::factory()->processed()->create();

        $user = User::factory()->create();
        $user->reports()->save($report);

        $this->assertEquals(count($user->reports),1);
        //$this->assertEquals($ticketWithAssemblaId->number, $ticket->number);
        //$this->assertEquals($ticketWithAssemblaId->ticket_assembla_id, $ticket->ticket_assembla_id);
    }

    /** @test */
    function can_retrieve_user_reports_from_last_week()
    {
        $reportA = HoursByUSReport::factory()->processed()->create([
            'created_at' => \Carbon\Carbon::today()->subDays(8),
        ]);
        $reportB = HoursByUSReport::factory()->processed()->create([
            'created_at' => \Carbon\Carbon::today()->subDays(2),
        ]);
        $reportC = HoursByUSReport::factory()->processed()->create([
            'created_at' => \Carbon\Carbon::today()->subDays(5),
        ]);
        $reportD = HoursByUSReport::factory()->processed()->create([
            'created_at' => \Carbon\Carbon::today()->subDays(3),
        ]);


        $user = User::factory()->create();
        $user->reports()->saveMany([$reportA, $reportB, $reportC, $reportD]);


        $this->assertEquals(count($user->lastWeekReports()), 3);
        //$this->assertEquals($ticketWithAssemblaId->number, $ticket->number);
        //$this->assertEquals($ticketWithAssemblaId->ticket_assembla_id, $ticket->ticket_assembla_id);
    }

    /** @test */
    function can_retrive_report_status_label()
    {
        $pending = HoursByUSReport::factory()->create([
            'created_at' => \Carbon\Carbon::today()->subDays(8),
            'status' => Report::PENDING_STATUS,
        ]);
        $processed = HoursByUSReport::factory()->create([
            'created_at' => \Carbon\Carbon::today()->subDays(8),
            'status' => Report::PROCESSED_STATUS,
        ]);
        $running  = HoursByUSReport::factory()->create([
            'created_at' => \Carbon\Carbon::today()->subDays(8),
            'status' => Report::RUNNING_STATUS,
        ]);
        $failed  = HoursByUSReport::factory()->create([
            'created_at' => \Carbon\Carbon::today()->subDays(8),
            'status' => Report::FAILED_STATUS,
        ]);

        $this->assertEquals($pending->getStatusLabel(), 'Pending');
        $this->assertEquals($processed->getStatusLabel(), 'Processed');
        $this->assertEquals($running->getStatusLabel(), 'Running');
        $this->assertEquals($failed->getStatusLabel(), 'Failed');

    }
}
