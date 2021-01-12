<?php

namespace Tests\Feature\Integration;

use App\Importer\ProjectImporter;
use App\Importer\SprintImporter;
use App\Importer\TicketImporter;
use App\Integration\AssemblaGateway;
use App\Integration\AssemblaRequest;
use App\Project;
use App\Sprint;
use App\Ticket;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @group integration
 */
class ProjectTest
    extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function can_sync_projects()
    {
        $user = $this->loginWithAssemblaUser();
        $projectA = Project::factory()->create([
            'name'                  => 'AD Barbieri',
            'wikiname'              => 'AD-Barbieri',
            'project_assembla_id'   => 'ce1LaCpjCr6O96aH8tHBnc'
        ]);
        $projectB = Project::factory()->create([
            'name'                  => 'Canal de Autopartes',
            'wikiname'              => 'canaldeautopartes',
            'project_assembla_id'   => 'dpT43eCVCr54kBacwqjQYw'
        ]);
        $projectC = Project::factory()->create([
            'name'                  => 'ClubDeBeneficios',
            'wikiname'              => 'clubdebeneficios',
            'project_assembla_id'   => 'cDK4RGOoar6RfhaIC_Qgzw'
        ]);
        $projectD = Project::factory()->create([
            'name'                  => 'Not Existing Project',
            'wikiname'              => 'not-existing-project',
            'project_assembla_id'   => 'SOME_ID_NOT_EXISTING'
        ]);
        $projectE = Project::factory()->create([
            'name'                  => 'Grupo Grassi',
            'wikiname'              => 'Grupo-Grassi',
            'project_assembla_id'   => 'dTomygY3Gr6P7dbK8JiBFu'
        ]);
        $user->projects()->saveMany([$projectA, $projectB, $projectC, $projectD, $projectE]);

        $this->assertEquals(5, count($user->projects));

        $projectImporter = new ProjectImporter();
        $projectImporter->importAllAssemblaSpacesAsProjects($user);

        $this->assertEquals(14, count($user->fresh()->projects));
    }


    /** @test */
    function can_sync_project_milestones()
    {
        $user = $this->loginWithAssemblaUser();
        $project = Project::factory()->create([
            'name'                  => 'Canal de Autopartes',
            'wikiname'              => 'canaldeautopartes',
            'project_assembla_id'   => 'dpT43eCVCr54kBacwqjQYw'
        ]);


        //milestone id: 13040067
        //milestone name: Closed SE - Noviembre 2
        //Total tickets 7 > 904, 905, 906, 907, 908, 909, 910

        $sprintA = Sprint::factory()->create([
            'name' => 'Soporte Evolutivo',
            'project_assembla_id' => 'dpT43eCVCr54kBacwqjQYw',
            'sprint_assembla_id'  => '12136093',
        ]);
        $sprintB = Sprint::factory()->create([
            'name' => 'Current',
            'project_assembla_id' => 'dpT43eCVCr54kBacwqjQYw',
            'sprint_assembla_id'  => '11669313',
        ]);
        $sprintC = Sprint::factory()->create([
            'name' => 'Kanban Board',
            'project_assembla_id' => 'dpT43eCVCr54kBacwqjQYw',
            'sprint_assembla_id'  => '11768063',
        ]);
        $sprintD = Sprint::factory()->create([
            'name' => 'Diseño',
            'project_assembla_id' => 'dpT43eCVCr54kBacwqjQYw',
            'sprint_assembla_id'  => '11770923',
        ]);
        $sprintE = Sprint::factory()->create([
            'name' => 'Backlog',
            'project_assembla_id' => 'dpT43eCVCr54kBacwqjQYw',
            'sprint_assembla_id'  => '11669303',
        ]);
        $sprintF = Sprint::factory()->create([
            'name' => 'Eliminado',
            'project_assembla_id' => 'dpT43eCVCr54kBacwqjQYw',
            'sprint_assembla_id'  => '11669XXX',
        ]);

        $project->sprints()->saveMany([$sprintA,$sprintB,$sprintC,$sprintD,$sprintE, $sprintF]);
        $user->projects()->save($project);
        $user->sprints()->saveMany([$sprintA,$sprintB,$sprintC,$sprintD,$sprintE, $sprintF]);


        $this->assertEquals(6, count($project->sprints));
        $this->assertEquals(6, count($user->sprints));


        $sprintImporter = new SprintImporter($user);
        $sprintImporter->importProjectMilestonesAsSprints($project);

        $this->assertEquals(11, count($project->fresh()->sprints));
        $this->assertEquals(11, count($user->fresh()->sprints));
    }
}
