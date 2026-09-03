<?php

use App\AssemblaUser;
use App\Models\ProjectStat;
use App\Project;
use App\Report;
use App\Reports\HoursByUSReport;
use App\Reports\HoursByUserReport;
use App\Reports\SprintsReport;
use App\Sprint;
use App\Ticket;
use App\TicketTime;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;

class DatabaseSeeder extends Seeder
{
    private $ticketStatus = [
        'New',
        'In Progress',
        'Accepted',
        'Completed',
    ];

    private $fibonacciStoryPoints = [1, 2, 3, 5, 8, 13];

    private $assemblaUsers = [];

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // --- Users ---
        $nperic = User::create([
            'name' => 'Nicolás',
            'email' => 'nico@example.com',
            'password' => bcrypt('Magento01'),
            'assembla_key' => 'fake_key_for_local_dev',
            'assembla_secret' => Crypt::encrypt('fake_secret_for_local_dev'),
            //'assembla_user_image' => 'https://ui-avatars.com/api/?name=Nicolas+Peric&background=4e73df&color=fff',
            'assembla_user_image' => 'https://s3.amazonaws.com/assembla-avatars/1e7f71fc/cvixt811Gr4PBcacwqjQYw:1571509138',
            'assembla_username' => 'Nicolás Peric',
            'user_assembla_id' => 'user_np_001',
        ]);

        $eperez = User::create([
            'name' => 'Elina',
            'email' => 'eperez@example.com',
            'password' => bcrypt('Magento02'),
            'assembla_key' => 'fake_key_for_local_dev',
            'assembla_secret' => Crypt::encrypt('fake_secret_for_local_dev'),
            'assembla_user_image' => 'https://ui-avatars.com/api/?name=Elina+Perez&background=1cc88a&color=fff',
            'assembla_username' => 'Elina Perez',
            'user_assembla_id' => 'user_ep_002',
        ]);

        // --- Assembla Users (team members that appear in tracked time) ---
        $this->assemblaUsers = [];
        $teamMembers = [
            ['user_np_001', 'nicoperic', 'Nicolás Peric', 'nperic@example.com'],
            ['user_ep_002', 'eperez', 'Elina Perez', 'eperez@example.com'],
            ['user_jd_003', 'jdoe', 'John Doe', 'jdoe@example.com'],
            ['user_mg_004', 'mgarcia', 'Martín García', 'mgarcia@example.com'],
            ['user_lr_005', 'lrodriguez', 'Laura Rodríguez', 'lrodriguez@example.com'],
        ];

        foreach ($teamMembers as $member) {
            $assemblaUser = AssemblaUser::create([
                'user_assembla_id' => $member[0],
                'login' => $member[1],
                'name' => $member[2],
                'email' => $member[3],
            ]);
            $this->assemblaUsers[] = $assemblaUser;
        }

        // --- Projects ---
        $sommier = Project::create([
            'name' => 'SommierCenter',
            'code' => 'SC',
            'wikiname' => 'sommiercenter',
            'project_assembla_id' => 'proj_sc_001',
            'status' => 1,
        ]);

        $rex = Project::create([
            'name' => 'Somos Rex',
            'code' => 'REX',
            'wikiname' => 'somos-rex',
            'project_assembla_id' => 'proj_rex_002',
            'status' => 1,
        ]);

        $cemaco = Project::create([
            'name' => 'Cemaco',
            'code' => 'CEM',
            'wikiname' => 'cemaco',
            'project_assembla_id' => 'proj_cem_003',
            'status' => 1,
        ]);

        // --- Associate assembla users to projects ---
        $sommier->assemblaUsers()->attach([$this->assemblaUsers[0]->id, $this->assemblaUsers[1]->id, $this->assemblaUsers[2]->id]);
        $rex->assemblaUsers()->attach([$this->assemblaUsers[0]->id, $this->assemblaUsers[3]->id, $this->assemblaUsers[4]->id]);
        $cemaco->assemblaUsers()->attach([$this->assemblaUsers[1]->id, $this->assemblaUsers[4]->id]);

        // --- Associate users to projects (with starred + syncable pivots) ---
        $sommier->users()->attach($nperic->id, ['starred' => 1, 'syncable' => 1, 'user_role' => 'owner', 'user_status' => User::STATUS_ACCEPTED]);
        $sommier->users()->attach($eperez->id, ['starred' => 0, 'syncable' => 1, 'user_role' => 'member', 'user_status' => User::STATUS_ACCEPTED]);
        $rex->users()->attach($nperic->id, ['starred' => 1, 'syncable' => 1, 'user_role' => 'owner', 'user_status' => User::STATUS_ACCEPTED]);
        $cemaco->users()->attach($eperez->id, ['starred' => 1, 'syncable' => 1, 'user_role' => 'owner', 'user_status' => User::STATUS_ACCEPTED]);

        // --- Sprints (current + closed) ---
        // Current sprints (planner_type = 2)
        $sommierCurrentSprint = factory(Sprint::class)->create([
            'name' => 'SC Sprint Feb 2026',
            'sprint_assembla_id' => 'sprint_sc_current',
            'planner_type' => Sprint::PLANNER_TYPE_CURRENT,
            'is_active' => 1,
            'start_date' => Carbon::now()->startOfMonth(),
            'end_date' => Carbon::now()->endOfMonth(),
        ]);

        $rexCurrentSprint = factory(Sprint::class)->create([
            'name' => 'REX Sprint Feb 2026',
            'sprint_assembla_id' => 'sprint_rex_current',
            'planner_type' => Sprint::PLANNER_TYPE_CURRENT,
            'is_active' => 1,
            'start_date' => Carbon::now()->startOfMonth(),
            'end_date' => Carbon::now()->endOfMonth(),
        ]);

        $cemacoCurrentSprint = factory(Sprint::class)->create([
            'name' => 'CEM Sprint Feb 2026',
            'sprint_assembla_id' => 'sprint_cem_current',
            'planner_type' => Sprint::PLANNER_TYPE_CURRENT,
            'is_active' => 1,
            'start_date' => Carbon::now()->startOfMonth(),
            'end_date' => Carbon::now()->endOfMonth(),
        ]);

        // Closed sprints (previous months)
        $sommierClosedSprint = factory(Sprint::class)->create([
            'name' => 'SC Sprint Jan 2026',
            'sprint_assembla_id' => 'sprint_sc_jan',
            'planner_type' => Sprint::PLANNER_TYPE_NONE,
            'is_active' => 0,
            'start_date' => Carbon::now()->subMonth()->startOfMonth(),
            'end_date' => Carbon::now()->subMonth()->endOfMonth(),
        ]);

        $rexClosedSprint = factory(Sprint::class)->create([
            'name' => 'REX Sprint Jan 2026',
            'sprint_assembla_id' => 'sprint_rex_jan',
            'planner_type' => Sprint::PLANNER_TYPE_NONE,
            'is_active' => 0,
            'start_date' => Carbon::now()->subMonth()->startOfMonth(),
            'end_date' => Carbon::now()->subMonth()->endOfMonth(),
        ]);

        // --- Associate sprints to projects ---
        $sommierCurrentSprint->projects()->save($sommier);
        $rexCurrentSprint->projects()->save($rex);
        $cemacoCurrentSprint->projects()->save($cemaco);
        $sommierClosedSprint->projects()->save($sommier);
        $rexClosedSprint->projects()->save($rex);

        // --- Associate sprints to users ---
        $nperic->sprints()->attach([$sommierCurrentSprint->id, $rexCurrentSprint->id, $sommierClosedSprint->id, $rexClosedSprint->id]);
        $eperez->sprints()->attach([$sommierCurrentSprint->id, $cemacoCurrentSprint->id]);

        // --- Tickets for current sprints ---
        $sommierTickets = $this->createTicketsForSprint($sommier, $sommierCurrentSprint, rand(10, 15));
        $rexTickets = $this->createTicketsForSprint($rex, $rexCurrentSprint, rand(8, 12));
        $cemacoTickets = $this->createTicketsForSprint($cemaco, $cemacoCurrentSprint, rand(8, 12));

        // --- Tickets for closed sprints ---
        $sommierClosedTickets = $this->createTicketsForSprint($sommier, $sommierClosedSprint, rand(10, 14), true);
        $rexClosedTickets = $this->createTicketsForSprint($rex, $rexClosedSprint, rand(8, 12), true);

        // --- Create user story / subtask associations ---
        $this->createTicketAssociations($sommierTickets);
        $this->createTicketAssociations($rexTickets);
        $this->createTicketAssociations($cemacoTickets);

        // --- TicketTime records (tracked hours) ---
        $allProjects = [
            ['project' => $sommier, 'tickets' => array_merge($sommierTickets, $sommierClosedTickets)],
            ['project' => $rex, 'tickets' => array_merge($rexTickets, $rexClosedTickets)],
            ['project' => $cemaco, 'tickets' => $cemacoTickets],
        ];
        $this->createTicketTimes($allProjects);

        // --- Reports ---
        // Using subclass ::create() so MorphTrait boot() sets the correct type automatically.
        // request_data must be a plain array — Eloquent $casts handles JSON encoding.
        HoursByUSReport::create([
            'user_id' => $nperic->id,
            'created_at' => Carbon::today()->subDays(1),
            'request_data' => ['wikiname' => 'sommiercenter', 'space_id' => 'proj_sc_001', 'from_date' => '2026/01/01 00:00', 'to_date' => '2026/01/31 23:59'],
            'title' => 'Hours by User Story',
            'status' => Report::PENDING_STATUS,
        ]);
        HoursByUserReport::create([
            'user_id' => $nperic->id,
            'created_at' => Carbon::today()->subDays(2),
            'request_data' => ['projects' => [$sommier->id, $rex->id], 'users' => [$this->assemblaUsers[0]->user_assembla_id, $this->assemblaUsers[1]->user_assembla_id], 'from_date' => '2026/01/01 00:00', 'to_date' => '2026/01/31 23:59'],
            'title' => 'Hours by Users',
            'status' => Report::PROCESSED_STATUS,
            'body' => json_encode(['total_hours' => 160]),
            'finished_at' => Carbon::today()->subDays(2)->addHours(1),
        ]);
        HoursByUSReport::create([
            'user_id' => $nperic->id,
            'created_at' => Carbon::today()->subDays(5),
            'request_data' => ['wikiname' => 'cemaco', 'space_id' => 'proj_cem_003', 'from_date' => '2026/01/01 00:00', 'to_date' => '2026/01/31 23:59'],
            'title' => 'Hours by User Story',
            'status' => Report::PROCESSED_STATUS,
            'body' => json_encode(['total_hours' => 80]),
            'finished_at' => Carbon::today()->subDays(5)->addHours(2),
        ]);
        SprintsReport::create([
            'user_id' => $nperic->id,
            'created_at' => Carbon::today()->subDays(7),
            'request_data' => ['sprints' => [$sommierCurrentSprint->id, $rexCurrentSprint->id]],
            'title' => 'Milestones',
            'status' => Report::FAILED_STATUS,
        ]);

        // --- ProjectStats (historical data for charts) ---
        $this->createProjectStats($sommier);
        $this->createProjectStats($rex);
        $this->createProjectStats($cemaco);
    }

    /**
     * Create tickets and associate them with a sprint
     */
    private function createTicketsForSprint($project, $sprint, $quantity, $allClosed = false)
    {
        $tickets = [];
        $projectAssemblaUsers = $project->assemblaUsers->pluck('user_assembla_id')->toArray();
        $ticketNumber = Ticket::where('project_id', $project->id)->max('number') ?? 0;

        foreach (range(1, $quantity) as $i) {
            $ticketNumber++;
            $isStory = ($i % 3 !== 0); // 2/3 are stories, 1/3 subtasks
            $status = $allClosed ? 'Completed' : $this->ticketStatus[array_rand($this->ticketStatus)];
            $statusBasedData = [];
            $daysAgo = rand(1, 20);

            switch ($status) {
                case 'In Progress':
                case 'Accepted':
                    $statusBasedData['started_at'] = Carbon::parse($daysAgo . ' days ago');
                    break;
                case 'Completed':
                    $statusBasedData['started_at'] = Carbon::parse($daysAgo . ' days ago');
                    $statusBasedData['completed_at'] = Carbon::parse(rand(1, $daysAgo) . ' days ago');
                    $statusBasedData['state'] = Ticket::CLOSED_STATE;
                    break;
            }

            $estimate = $this->fibonacciStoryPoints[array_rand($this->fibonacciStoryPoints)];
            $workedHours = ($status === 'New') ? 0 : round(rand(1, 16) * 0.5, 1);
            $workingHours = ($status === 'Completed') ? 0 : round(rand(0, 8) * 0.5, 1);

            $ticket = factory(Ticket::class)->create(array_merge([
                'project_id' => $project->id,
                'created_at' => Carbon::parse(($daysAgo + rand(1, 5)) . ' days ago'),
                'status' => $status,
                'name' => $project->code . '-' . $ticketNumber . ': ' . $this->generateTicketSummary($isStory),
                'number' => $ticketNumber,
                'ticket_assembla_id' => 'ticket_' . $project->code . '_' . $ticketNumber,
                'is_story' => $isStory,
                'hierarchy_type' => $isStory ? Ticket::HIERARCHY_STORY : Ticket::HIERARCHY_SUBTASK,
                'estimate' => $estimate,
                'total_estimate' => $isStory ? $estimate * rand(1, 3) : $estimate,
                'total_invested_hours' => $workedHours,
                'worked_hours' => $workedHours,
                'working_hours' => $workingHours,
                'total_working_hours' => $workingHours,
                'assigned_to_user_assembla_id' => $projectAssemblaUsers[array_rand($projectAssemblaUsers)],
            ], $statusBasedData));

            $sprint->tickets()->attach($ticket->id);
            $tickets[] = $ticket;
        }

        return $tickets;
    }

    /**
     * Create story/subtask associations from ticket list
     */
    private function createTicketAssociations($tickets)
    {
        $stories = array_filter($tickets, fn($t) => $t->is_story);
        $subtasks = array_filter($tickets, fn($t) => !$t->is_story);

        foreach ($subtasks as $subtask) {
            $story = !empty($stories) ? $stories[array_rand($stories)] : null;
            if ($story) {
                $story->subtasks()->attach($subtask->id, ['relationship' => 5]);
            }
        }
    }

    /**
     * Create TicketTime records across projects to simulate tracked hours
     */
    private function createTicketTimes($allProjects)
    {
        $timeId = 1;

        foreach ($allProjects as $entry) {
            $project = $entry['project'];
            $tickets = $entry['tickets'];
            $projectAssemblaUsers = $project->assemblaUsers->pluck('user_assembla_id')->toArray();

            foreach ($tickets as $ticket) {
                if ($ticket->worked_hours <= 0) {
                    continue;
                }

                // Create 1-3 time entries per ticket
                $numEntries = rand(1, 3);
                $remainingHours = $ticket->worked_hours;

                for ($j = 0; $j < $numEntries && $remainingHours > 0; $j++) {
                    $hours = ($j === $numEntries - 1) ? $remainingHours : round(min($remainingHours, rand(1, 4) * 0.5), 1);
                    $remainingHours -= $hours;
                    $daysAgo = rand(0, 25);
                    $beginAt = Carbon::now()->subDays($daysAgo)->setHour(rand(9, 16));

                    TicketTime::create([
                        'ticket_time_assembla_id' => $timeId++,
                        'description' => 'Working on ' . $ticket->name,
                        'hours' => $hours,
                        'begin_at' => $beginAt,
                        'end_at' => (clone $beginAt)->addMinutes((int)($hours * 60)),
                        'ticket_number' => $ticket->number,
                        'ticket_assembla_id' => $ticket->ticket_assembla_id,
                        'project_assembla_id' => $project->project_assembla_id,
                        'user_assembla_id' => $projectAssemblaUsers[array_rand($projectAssemblaUsers)],
                    ]);
                }
            }
        }
    }

    /**
     * Create ProjectStats for the last 6 months (for charts)
     */
    private function createProjectStats($project)
    {
        $projectAssemblaUsers = $project->assemblaUsers;

        for ($i = 0; $i < 6; $i++) {
            $date = Carbon::now()->subMonths($i);
            $fromDate = $date->copy()->startOfMonth();
            $toDate = $date->copy()->endOfMonth();
            $workedHours = round(rand(80, 280) + rand(0, 99) / 100, 2);
            $totalTasks = rand(30, 120);

            // Build per-user hours breakdown
            $usersHoursData = [];
            $remainingHours = $workedHours;
            $userCount = $projectAssemblaUsers->count();

            foreach ($projectAssemblaUsers as $idx => $assemblaUser) {
                $isLast = ($idx === $userCount - 1);
                $userHours = $isLast ? round($remainingHours, 2) : round(rand(10, (int)($remainingHours / max(1, $userCount - $idx))) + rand(0, 99) / 100, 2);
                $userHours = min($userHours, $remainingHours);
                $remainingHours -= $userHours;

                $usersHoursData[$assemblaUser->user_assembla_id] = [
                    'total_hours' => $userHours,
                    'label' => $assemblaUser->name,
                ];
            }

            ProjectStat::create([
                'project_id' => $project->id,
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'range_type' => ProjectStat::MONTH_RANGE_TYPE,
                'year' => $date->year,
                'month' => $date->month,
                'week' => null,
                'worked_hours' => $workedHours,
                'total_tasks' => $totalTasks,
                'users_hours' => json_encode($usersHoursData),
            ]);
        }
    }

    private function generateTicketSummary($isStory)
    {
        $stories = [
            'Implement checkout flow redesign',
            'Add product filtering by category',
            'Update user profile page layout',
            'Create order tracking dashboard',
            'Integrate payment gateway',
            'Build inventory management module',
            'Redesign navigation menu',
            'Add bulk import functionality',
            'Implement search autocomplete',
            'Create admin reporting panel',
            'Add multi-language support',
            'Optimize image loading performance',
        ];

        $subtasks = [
            'Write unit tests for validation',
            'Fix CSS alignment on mobile',
            'Update API endpoint documentation',
            'Add error handling for edge case',
            'Refactor database queries',
            'Update translation strings',
            'Fix pagination offset bug',
            'Add loading spinner component',
            'Update email template styling',
            'Configure caching layer',
        ];

        $list = $isStory ? $stories : $subtasks;
        return $list[array_rand($list)];
    }
}
