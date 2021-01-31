<?php

namespace Tests\Feature\Integration;

use App\Importer\TicketImporter;
use App\Integration\AssemblaGateway;
use App\Integration\AssemblaRequest;
use App\Jobs\ProcessSprintsReport;
use App\Jobs\SyncSpaceCurrentMilestone;
use App\Jobs\SyncSpaceMilestones;
use App\Project;
use App\Reports\SprintsReport;
use App\Sprint;
use App\Ticket;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * @group integration
 *        ^any test that will test my integration with another service
 */
class MilestoneTest
    extends TestCase
{
    use RefreshDatabase;

    /**  @test */
    function can_get_all_pages_of_tickets_for_a_milestone()
    {
        $user = $this->loginWithAssemblaUser();
        $space = 'sommiercenter';
        $milestoneId = 12982775;
        $assemblaGateway = new AssemblaGateway($user);
        $page = 1;

        $queryParams = [
            'page' => $page,
            'ticket_status' => 'all',
            'sort_by' => 'id',
            'sort_order' => 'desc'
        ];

        do {
            $tickets = $assemblaGateway->getTicketsForMilestone($space, $milestoneId, $queryParams);

            if ($tickets === false) {
                break;
            }
            /*print PHP_EOL.count($result).PHP_EOL;
            foreach ($result as $ticket) {
                $isStory = ($ticket['is_story'])? 'US' : 'T';
               print $isStory.'#'.$ticket['number'].' '.$ticket['summary'].PHP_EOL;
            }*/

            $queryParams['page'] = ++$page;
        } while(count($tickets) === AssemblaRequest::PER_PAGE);

        $this->assertEquals(4, $page);
    }

    //TODO assets tarea
    function can_get_a_list_of_tickets_for_a_milestone()
    {
        $user = $this->loginWithAssemblaUser();
        ///GET /v1/spaces/[space_id]/tickets/milestone/[milestone_id]	Get the list of tickets for a milestone
        $space = 'sommiercenter';
        $milestoneId = 12982775;
        $assemblaGateway = new AssemblaGateway($user);
        $response = $assemblaGateway->getTicketsForMilestone($space, $milestoneId);

        $result = json_decode($response->getBody()->getContents(), 1);
        //print print_r($result[0], 1);
        print PHP_EOL;
        foreach ($result as $ticket) {




            print '#'.$ticket['number'].' '.$ticket['summary'].PHP_EOL;
        }

        return;
        // dd(count($result));

        $this->assertEquals(200, $response->getStatusCode());
    }


    //TODO assets tarea
    /**  */
    function can_get_all_milestones_for_a_space()
    {
        $user = $this->loginWithAssemblaUser();
        $space = 'sommiercenter';

        $assemblaGateway = new AssemblaGateway($user);
        $sprints = $assemblaGateway->getMilestonesForSpace($space);



        foreach ($sprints as $sprint) {
            print PHP_EOL.print_r($sprint, 1).PHP_EOL;
        }

        print 'Found '. count($sprints).' milestones '.PHP_EOL;


    }

    /** @test */
    function can_sync_milestone_tickets()
    {
        $user = $this->loginWithAssemblaUser();

        $project = Project::factory()->create([
            'name'                  => 'Project C',
            'wikiname'              => 'canaldeautopartes',
            'project_assembla_id'   => 'dpT43eCVCr54kBacwqjQYw'
        ]);


        //milestone id: 13040067
        //milestone name: Closed SE - Noviembre 2
        //Total tickets 7 > 904, 905, 906, 907, 908, 909, 910

        $sprint = Sprint::factory()->create([
            'project_assembla_id' => 'dpT43eCVCr54kBacwqjQYw',
            'sprint_assembla_id'  => '13040067',
        ]);

        $ticketA = Ticket::factory()->completed()->create([
            'id' => 1,
            'ticket_assembla_id' => '232538824',
            'is_story' => true,
            'number' => 908,
        ]);
        $ticketB = Ticket::factory()->completed()->create([
            'id' => 2,
            'ticket_assembla_id' => '232533924',
            'is_story' => true,
            'number' => 904,
        ]);
        $ticketC = Ticket::factory()->completed()->create([
            'id' => 3,
            'ticket_assembla_id' => '232534968',
            'is_story' => false,
            'number' => 905,
        ]);
        $ticketD = Ticket::factory()->create([
            'id' => 4,
            'ticket_assembla_id' => '232538091',
            'is_story' => false,
            'number' => 906,
        ]);
        $ticketE = Ticket::factory()->create([
            'id' => 5,
            'ticket_assembla_id' => '232538583',
            'is_story' => false,
            'number' => 907,
        ]);

        //this ticket doesn' belong to the sprint the importer should remove it
        $ticketF = Ticket::factory()->create([
            'id' => 6,
            'ticket_assembla_id' => '232538999',
            'is_story' => false,
            'number' => 911,
        ]);





        $sprint->tickets()->saveMany([$ticketA, $ticketB, $ticketC, $ticketD, $ticketE, $ticketF]);
        $sprint->projects()->save($project);

        $this->assertEquals(6, $sprint->getTotalTickets());

        //MISSING TICKETS 909 and 910 will be added by importer
        //TICKET 911 will be removed by importer
        $ticketImporter = new TicketImporter($user);
        $ticketImporter->importMilestoneTickets($sprint);


        $this->assertEquals(6, $sprint->getTotalTickets());
    }

    /** @test */
    function can_validate_hours_for_sync_job()
    {
        $sixHour = Carbon::parse("2020-01-08 06:14");
        $sevenHour = Carbon::parse("2020-01-08 07:23");
        $twelveHour = Carbon::parse("2020-01-08 12:19");
        $eighteenHour = Carbon::parse("2020-01-08 18:55");
        $twentyHour = Carbon::parse("2020-01-08 20:03");

        $this->assertTrue($sixHour->hour % 6 == 0);
        $this->assertTrue($twelveHour->hour % 6 == 0);
        $this->assertTrue($eighteenHour->hour % 6 == 0);
        $this->assertFalse($sevenHour->hour % 6 == 0);
        $this->assertFalse($twentyHour->hour % 6 == 0);

    }

    /**  */
    function can_retrieve_syncable_spaces()
    {

        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $projectA = Project::factory()->create([
            'name'                  => 'Project A',
            'wikiname'              => 'canaldeautopartes',
            'project_assembla_id'   => 'dpT43eCVCr54kBacwqjQYw'
        ]);
        $projectB = Project::factory()->create([
            'name'                  => 'Project B',
            'wikiname'              => 'banaldeautopartes',
            'project_assembla_id'   => 'dpT43eCVCr54kBacwqjQYw'
        ]);
        $projectC = Project::factory()->create([
            'name'                  => 'Project C',
            'wikiname'              => 'canaldeautopartes',
            'project_assembla_id'   => 'dpT43eCVCr54kBacwqjQYw'
        ]);
        $projectD = Project::factory()->create([
            'name'                  => 'Project D',
            'wikiname'              => 'danaldeautopartes',
            'project_assembla_id'   => 'dpT43eCVCr54kBacwqjQYw'
        ]);
        $userA->projects()->save($projectA, ['syncable' => true]);
        $userB->projects()->save($projectA, ['syncable' => true]);
        $userB->projects()->save($projectB, ['syncable' => true]);
        $userB->projects()->save($projectC, ['syncable' => true]);


        foreach ($userA->syncableProjects as $project) {
            dump('User A '.$userA->name.' '. $project->name);
        }

        foreach ($userB->syncableProjects as $project) {
            dump('User B '.$userB->name.' '. $project->name);
        }

        $projects = DB::table('projects')->get();


        foreach ($projects as $project){
            dump('Projects '.$project->name);
        }

        $syncableProjects = DB::table('projects') ->join('project_user', function ($join)
        { $join->on('projects.id', '=', 'project_user.project_id') ->where('project_user.syncable',
            '=', true); })->groupBy('projects.id')->distinct()->get();

        foreach ($syncableProjects as $project){
            //dd($project);
            $user = User::find($project->user_id);
            $projectModel = Project::find($project->project_id);

            dump('Sync PR '.$projectModel->name.' user '.$user->name);
        }


    }

    /**  */
    function can_dispatch_batch_of_jobs()
    {
        $userA = User::factory()->create();

        $projectA = Project::factory()->create([
            'name'                  => 'Project A',
            'wikiname'              => 'canaldeautopartes',
            'project_assembla_id'   => 'dpT43eCVCr54kBacwqjQYw'
        ]);
        $sprintA = Sprint::factory()->create([
            'project_assembla_id' => 'dpT43eCVCr54kBacwqjQYw',
            'name'                  => 'Sprint A',
            'sprint_assembla_id'  => '13040067',
            'is_active'              => 1,
            'planner_type'        => 2,
        ]);

        $projectA->sprints()->save($sprintA);

        $projectB = Project::factory()->create([
            'name'                  => 'Project B',
            'wikiname'              => 'banaldeautopartes',
            'project_assembla_id'   => 'dpT43eCVCr54kBacwqjQYw'
        ]);
        $sprintB = Sprint::factory()->create([
            'project_assembla_id' => 'dpT43eCVCr54kBacwqjQYw',
            'name'                  => 'Sprint B',
            'sprint_assembla_id'  => '13040067',
            'is_active'              => 1,
            'planner_type'        => 2,
        ]);
        $projectB->sprints()->save($sprintB);
        $projectC = Project::factory()->create([
            'name'                  => 'Project C',
            'wikiname'              => 'canaldeautopartes',
            'project_assembla_id'   => 'dpT43eCVCr54kBacwqjQYw'
        ]);
        $sprintC = Sprint::factory()->create([
            'project_assembla_id' => 'dpT43eCVCr54kBacwqjQYw',
            'name'                  => 'Sprint C',
            'sprint_assembla_id'  => '13040067',
            'is_active'              => 1,
            'planner_type'        => 2,
        ]);

        $projectC->sprints()->save($sprintC);
        $userA->projects()->save($projectA, ['syncable' => true]);
        $userA->projects()->save($projectB, ['syncable' => true]);
        $userA->projects()->save($projectC, ['syncable' => true]);

        print PHP_EOL;
        //TODO I would like to have a Batch of jobs and be able to determine when all finished to notify the user
        foreach ($userA->syncableProjects as $project ) {
            print $project->name.PHP_EOL;
        }
        $this->assertTrue(true);

        $jobs = $userA->syncableProjects->map(function (Project $project) use($userA)  {
            print "map inner for $userA->name and $project->name".PHP_EOL;
            return [new SyncSpaceMilestones($userA, $project),new SyncSpaceCurrentMilestone($userA, $project)];

        //    return $this->createSendMailJob($userA, $project);
        })
        ->filter()
        ->collapse()
        ->toArray();
        //dd($jobs);
    }

    function createSendMailJob($userA, $project) {
        return "JOB for $userA->name and $project->name".PHP_EOL;
    }

    /** test */
    function can_sync_a_milestone()
    {
        $userA = User::factory()->create();

        $projectA = Project::factory()->create([
            'name'                  => 'Project A',
            'wikiname'              => 'canaldeautopartes',
            'project_assembla_id'   => 'dpT43eCVCr54kBacwqjQYw'
        ]);
        $sprintA = Sprint::factory()->create([
            'project_assembla_id' => 'dpT43eCVCr54kBacwqjQYw',
            'name'                  => 'Sprint A',
            'sprint_assembla_id'  => '13040067',
            'is_active'              => 1,
            'planner_type'        => 2,
        ]);
        $sprintB = Sprint::factory()->create([
            'project_assembla_id' => 'dpT43eCVCr54kBacwqjQYw',
            'name'                  => 'Sprint B',
            'sprint_assembla_id'  => '13041228',
            'is_active'              => 1,
            'planner_type'        => 2,
        ]);
        $projectA->sprints()->saveMany([$sprintA, $sprintB]);
        $userA->projects()->save($projectA);



        $requestData['sprints'] = [
            0 => '13040067',
            1 => '13041228',
        ];
        //There' no SprintsReportFactory yet, this will fail
        $reportModel = SprintsReport::factory()->create([
            'title' => 'Milestones',
            'request_data' => serialize($requestData)
        ]);

        $userA->reports()->save($reportModel);
        ProcessSprintsReport::dispatch($requestData, $reportModel);
    }


}
