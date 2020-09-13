<?php

use App\Project;
use App\Sprint;
use App\Ticket;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Faker\Generator as Faker;

class DatabaseSeeder extends Seeder
{
    private $ticketStatus = [
        'New',
        'In Progress',
        //'Accepted',
        //'Develop Test',
        //'Staging Test',
        //'Production Test',
        //'Done',
        'Completed',
        //'Invalid'
    ];

    private $fibonacciStoryPoints = [1, 2, 3, 5, 8, 13];
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $faker = new Faker();
        // $this->call(UsersTableSeeder::class);
        $nperic = User::create([
            'name' => 'Nicolás',
            'email' => 'nicolasperic@gmail.com',
            'password' => bcrypt('Magento01'),
            'assembla_key' => 'a5aa5632989ec768d71d',
            'assembla_secret' => '497e452c605c29f8971aeb367e6c15a872749efe',
            'assembla_user_image' => 'https://s3.amazonaws.com/assembla-avatars/1e7f71fc/cvixt811Gr4PBcacwqjQYw:1571509138',
        ]);

        $eperez = User::create([
            'name' => 'Elina',
            'email' => 'eperez@summasolutions.com',
            'password' => bcrypt('Magento02'),
            'assembla_user_image' => 'https://s3.amazonaws.com/assembla-avatars/ac4c96a6/dNWJBO9war45rbacwqjQXA:1550599353',
        ]);
        return;




        list($sommier, $sommierSprint, $sommierTickets) = $this->_createProjectAndSprint('SommierCenter', 'SC');
        list($rex, $rexSprint, $rexTickets)= $this->_createProjectAndSprint('Somos Rex', 'REX');
        list($cemaco, $cemacoSprint, $cemacoTickets)= $this->_createProjectAndSprint('Cemaco' , 'CEM');

        $multiProjectSprint = factory(Sprint::class)->create([
            'name' => 'Rex & SC Abril 2020',
        ]);
        $multiProjectSprint->projects()->saveMany([$sommierSprint, $rexSprint]);
        $multiProjectSprint->tickets()->saveMany(array_merge($sommierTickets, $rexTickets));


        $nperic->sprints()->saveMany([$sommierSprint, $rexSprint, $multiProjectSprint]);
        $eperez->sprints()->saveMany([$sommierSprint, $cemacoSprint]);

        $sommier->users()->saveMany([$nperic, $eperez]);
        $rex->users()->save($nperic);
        $cemaco->users()->save($eperez);


        $tickets = $this->createTickets($sommier, 2);
        $userstory = $tickets[0];
        $subtask= $tickets[1];
        $userstory->subtasks()->save($subtask,['relationship' => 5]);
    }
    
    private function _createProjectAndSprint($projectName, $projectCode)
    {
        $project = Project::create([
            'name' => $projectName,
            'code' => $projectCode,
        ]);
        $tickets = $this->createTickets($project, rand(8,15));
        $sprint = factory(Sprint::class)->create([
            'name' => $projectName.' Abril 2020',
        ]);
        $sprint->projects()->save($project);
        $sprint->tickets()->saveMany($tickets);

        return [$project, $sprint, $tickets];
    }

    private function createTickets($project, $quantity)
    {
        $tickets = [];
        foreach (range(1, $quantity) as $i) {
            $status = $this->ticketStatus[array_rand($this->ticketStatus)];
            $statusBasedData = [];
            $daysAgo = rand(1,10);
            switch($status) {
                case 'In Progress':
                    $statusBasedData['started_at'] = Carbon::parse($daysAgo.' days ago');
                    break;
                case 'Completed':
                    $statusBasedData['started_at'] = Carbon::parse($daysAgo.' days ago');
                    $completedDays = $daysAgo + rand(1,10);
                    $statusBasedData['completed_at'] = Carbon::parse('+'.$completedDays .' days');
                    break;
            }


            $createdDays = $daysAgo + rand(1,5);
            $ticketData = [
                'project_id' => $project,
                'created_at' => Carbon::parse($createdDays.' days ago'),
                'status' => $status,
                'name' => $project->code.'-'.$i.': a ticket summary',
                'story_points' => $this->fibonacciStoryPoints[array_rand($this->fibonacciStoryPoints)]
            ];

            $ticket = factory(Ticket::class)->create(array_merge($ticketData, $statusBasedData));
            $tickets[] = $ticket;
        }

        return $tickets;

    }
}
