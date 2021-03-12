<?php

namespace Tests\Unit;

use App\Project;
use App\SprintIteration;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SprintIterationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function can_calculate_new_milestone_dates()
    {
        $twoWeeksIteration = SprintIteration::factory()->create([
            'sprint_duration' => 2,
            'sprint_start_weekday' => 1//Lunes
        ]);
        $threeWeeksIteration = SprintIteration::factory()->create([
            'sprint_duration' => 3,
            'sprint_start_weekday' => 0//Domingo
        ]);
        $fourWeeksIteration = SprintIteration::factory()->create([
            'sprint_duration' => 4,
            'sprint_start_weekday' => 3//Miércoles
        ]);
        //every X weeks > 2, 3, 4
        //inicio y finalización podría ser más configurable (nosotros vamos de lunes a viernes, pero podrías cargar la semana completa: Lunes a Dom

        //TESTING FOR 2 WEEKS MILESTONES
        $milestoneStartDate = $twoWeeksIteration->getNewMilestoneStartDate('2021/01/14');
        $milestoneEndDate = $twoWeeksIteration->getNewMilestoneEndDate($milestoneStartDate);
        $this->assertEquals('2021/01/11',$milestoneStartDate);
        $this->assertEquals('2021/01/24',$milestoneEndDate);

        $milestoneStartDate = $twoWeeksIteration->getNewMilestoneStartDate('2021/01/11');
        $milestoneEndDate = $twoWeeksIteration->getNewMilestoneEndDate($milestoneStartDate);
        $this->assertEquals('2021/01/11',$milestoneStartDate);
        $this->assertEquals('2021/01/24',$milestoneEndDate);

        $milestoneStartDate = $twoWeeksIteration->getNewMilestoneStartDate('2021/01/14', true);
        $milestoneEndDate = $twoWeeksIteration->getNewMilestoneEndDate($milestoneStartDate);
        $this->assertEquals('2021/01/18',$milestoneStartDate);
        $this->assertEquals('2021/01/31',$milestoneEndDate);

        //TESTING FOR 3 WEEKS MILESTONES
        $milestoneStartDate = $threeWeeksIteration->getNewMilestoneStartDate('2021/03/22');
        $milestoneEndDate = $threeWeeksIteration->getNewMilestoneEndDate($milestoneStartDate);
        $this->assertEquals('2021/03/21',$milestoneStartDate);
        $this->assertEquals('2021/04/10',$milestoneEndDate);

        $milestoneStartDate = $threeWeeksIteration->getNewMilestoneStartDate('2021/03/22', true);
        $milestoneEndDate = $threeWeeksIteration->getNewMilestoneEndDate($milestoneStartDate);
        $this->assertEquals('2021/03/28',$milestoneStartDate);
        $this->assertEquals('2021/04/17',$milestoneEndDate);

        //TESTING FOR 4 WEEKS MILESTONES
        $milestoneStartDate = $fourWeeksIteration->getNewMilestoneStartDate('2021/05/07');
        $milestoneEndDate = $fourWeeksIteration->getNewMilestoneEndDate($milestoneStartDate);
        $this->assertEquals('2021/05/05',$milestoneStartDate);
        $this->assertEquals('2021/06/01',$milestoneEndDate);

    }


    /**
     * @test
     */
    function can_calculate_new_milestone_unique_title()
    {
        $iteration = SprintIteration::factory()->create([
            'sprint_prefix' => 'SE - ',
        ]);
        $iterationB = SprintIteration::factory()->create([
            'sprint_prefix' => 'Grassi - ',
        ]);
        $milestoneTitle = $iteration->getNewMilestoneUniqueTitle('2021/01/14');
        $this->assertEquals('SE - 14JAN21', $milestoneTitle);

        $milestoneTitle = $iterationB->getNewMilestoneUniqueTitle('2021/02/22');
        $this->assertEquals('Grassi - 22FEB21', $milestoneTitle);

        $milestoneTitle = $iteration->getNewMilestoneUniqueTitle('2022/07/05');
        $this->assertEquals('SE - 05JUL22', $milestoneTitle);
    }

    /** @test */
    function can_calculate_sprint_suggested_start_dates_based_on_weekday_and_current_date()
    {
        $mondayIteration = SprintIteration::factory()->create([
            'sprint_start_weekday' => 1//Lunes
        ]);
        $tuesdayIteration = SprintIteration::factory()->create([
            'sprint_start_weekday' => 2//Martes
        ]);
        $wednesdayIteration = SprintIteration::factory()->create([
            'sprint_start_weekday' => 3//Miércoles
        ]);
        $thursdayIteration = SprintIteration::factory()->create([
            'sprint_start_weekday' => 4//Jueves
        ]);
        $fridayIteration = SprintIteration::factory()->create([
            'sprint_start_weekday' => 5//Viernes
        ]);
        $saturdayIteration = SprintIteration::factory()->create([
            'sprint_start_weekday' => 6//Sábado
        ]);
        $sundayIteration = SprintIteration::factory()->create([
            'sprint_start_weekday' => 0//Domingo
        ]);



        $milestoneStartDates = $mondayIteration->getNewMilestoneStartDates('2021/03/23');//Martes
        $this->assertEquals('Last Monday 2021/03/22',$milestoneStartDates['last']);
        $this->assertEquals('Next Monday 2021/03/29',$milestoneStartDates['next']);

        $milestoneStartDates = $tuesdayIteration->getNewMilestoneStartDates('2021/03/23');
        $this->assertEquals(false,$milestoneStartDates);

        $milestoneStartDates = $wednesdayIteration->getNewMilestoneStartDates('2021/03/23');
        $this->assertEquals('Last Wednesday 2021/03/17',$milestoneStartDates['last']);
        $this->assertEquals('Next Wednesday 2021/03/24',$milestoneStartDates['next']);

        $milestoneStartDates = $thursdayIteration->getNewMilestoneStartDates('2021/03/23');
        $this->assertEquals('Last Thursday 2021/03/18',$milestoneStartDates['last']);
        $this->assertEquals('Next Thursday 2021/03/25',$milestoneStartDates['next']);

        $milestoneStartDates = $fridayIteration->getNewMilestoneStartDates('2021/03/23');
        $this->assertEquals('Last Friday 2021/03/19',$milestoneStartDates['last']);
        $this->assertEquals('Next Friday 2021/03/26',$milestoneStartDates['next']);

        $milestoneStartDates = $saturdayIteration->getNewMilestoneStartDates('2021/03/23');
        $this->assertEquals('Last Saturday 2021/03/20',$milestoneStartDates['last']);
        $this->assertEquals('Next Saturday 2021/03/27',$milestoneStartDates['next']);

        $milestoneStartDates = $sundayIteration->getNewMilestoneStartDates('2021/03/23');
        $this->assertEquals('Last Sunday 2021/03/21',$milestoneStartDates['last']);
        $this->assertEquals('Next Sunday 2021/03/28',$milestoneStartDates['next']);






        $currentDate = Carbon::parse('2021/02/20');
        return $currentDate->subDays($currentDate->dayOfWeek-1)->format('Y/m/d');


    }
}
