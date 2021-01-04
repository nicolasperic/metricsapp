<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Sprint extends Model
{
    protected $guarded = [];

    private $monthlyHours;
    private $weeklyHours;
    private $userHours;

    /**
     * This function will validate if there's a sprint matching the received assembla ID
     *
     * @param $assemblaId
     *
     * @return mixed
     */
    public static function sprintExists($assemblaId)
    {
        return self::where('sprint_assembla_id', $assemblaId)->exists();
    }

    /**
     * This function will return a sprint by assembla ID
     *
     * @param $sprintAssemblaId
     *
     * @return mixed
     */
    public static function getSprintByAssemblaId($sprintAssemblaId)
    {
        return self::where('sprint_assembla_id', $sprintAssemblaId)->first();
    }

    /**
     * This function returns all the tickets associated to the sprint
     *
     * @return $this
     */
    public function tickets()
    {
        return $this->belongsToMany(Ticket::class)->orderBy('story_points', 'DESC')->orderBy('number', 'DESC');
    }

    /**
     * This function returns all the projects a sprint belongs to
     * Assembla only allows a milestone/sprint to belong to one project/space.
     * We built a more flexible sprint thinking in joining many spaces to one major sprint
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function projects()
    {
        return $this->belongsToMany(Project::class);
    }

    public function scopeOpen($query)
    {
        return $query->where('is_active', 1);
        //return $query->whereNotNull('completed_at')->where('state', self::CLOSED_STATE);//el ticket 385 estaba en delivered state 0 pero sin fecha de completed_at
    }

    public function scopeClosed($query)
    {
        return $query->where('is_active', 0);
        //return $query->whereNotNull('completed_at')->where('state', self::CLOSED_STATE);//el ticket 385 estaba en delivered state 0 pero sin fecha de completed_at
    }

    public function getProjectName()
    {
        if ($this->projects->first()) {
            return $this->projects->first()->name;
        }

        return '';
    }

    public function getFormattedPlannerType()
    {//TODO this function could easily go to a Helper (how can we use a helper on blade?)
        //0 None, 1 Backlog, 2 Current
        $plannerType = '';
        if ($this->planner_type == 1) {
            $plannerType = '<span class="planner-type backlog">Backlog</span>';
        } else if ($this->planner_type == 2) {
            $plannerType = '<span class="planner-type current">Current</span>';
        }

        return $plannerType;
    }

    /**
     * This function will return the users that are assigned to the sprint
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * This function returns the total amount of worked hours for the sprint
     * It's a calculated value by adding the worked_hours attribute for all tickets that belong to the sprint
     * @return mixed
     */
    public function getTotalWorkedHours()
    {
        return $this->tickets()->sum('worked_hours');
    }

    /**
     * This function returns the total amount of working hours for the sprint.
     * It's a calculated value by adding the working_hours attribute for all tickets that belong to the sprint.
     * This value represents the Remaining work based on hours for the Sprint.
     * @return mixed
     */
    public function getTotalWorkingHours()
    {
        return $this->tickets()->sum('working_hours');
    }

    /**
     * This function returns the total amount of invested hours for the sprint
     * It's a calculaed value by adding the total_invested_hours attribute for all tickets that belong to the sprint
     *
     * @return mixed
     */
    public function getTotalInvestedHours()
    {
        return $this->tickets()->sum('total_invested_hours');
    }

    /**
     * This function returns the total amount of tickets on the sprint,
     * both stories and subtasks are considered
     *
     * @return mixed
     */
    public function getTotalTickets()
    {
        return $this->tickets()->count();
    }

    /**
     * This function returns the total amount of subtasks on the sprint
     * User stories are not considered on this calculation
     * @return mixed
     */
    public function getTotalSubtasks()
    {
        return $this->tickets()->where('is_story', false)->count();
    }

    /**
     * This function returns the total amount of user stories on the sprint
     * Subtasks are not considered on this calculation
     * @return mixed
     */
    public function getTotalStories()
    {
        return $this->tickets()->where('is_story', true)->count();
    }

    /**
     * This function will return completed tickets on the sprint
     * Both user stories and subtasks
     * A ticket is considered complete when the state is 0
     *
     * @return mixed
     */
    public function getCompletedTickets()
    {
        return $this->tickets()->completed();
    }

    /**
     * This function will return completed user stories on the sprint
     *
     * TODO ticket function is not consistent with the return value Count!
     * @return mixed
     */
    public function getCompletedStories()
    {
        return $this->tickets()->where('is_story', true)->completed()->count();
    }

    /**
     * This function will return completed subtasks on the sprint
     *
     * TODO ticket function is not consistent with the return value Count!
     * @return mixed
     */
    public function getCompletedSubtasks()
    {
        return $this->tickets()->where('is_story', false)->completed()->count();
    }

    //TODO ticket function is not consistent with the return value Count!
    public function getUserStoriesWithoutStoryPoints()
    {
        return $this->tickets()->where('story_points', 0)->where('is_story', true)->count();
    }

    /**
     * This function will return the number of US with invalid subtasks statuses
     *
     * TODO este ticket devuelve un count y no se entiende con el nombre de la fn
     * @return int
     */
    public function getUserStoriesWithInconsistentState()
    {
        $completedUserStories= $this->tickets()->completed();
        $totalTickets = $completedUserStories->count();
        if ($totalTickets) {
            $totalInconsistentUserStories = 0;
            $completedUserStories->each(function ($ticket) use (&$totalInconsistentUserStories) {
                if (count($ticket->getInvalidStatusSubtasks()) > 0) {
                    $totalInconsistentUserStories ++;
                }
            });
            return $totalInconsistentUserStories;
        }
        return 0;
    }

    /**
     * Returns the total amount of completed story points
     *
     * TODO este ticket devuelve un count y no se entiende con el nombre de la fn
     * @deprecated use getTotalCompletedEstimate instead
     * @return mixed
     */
    public function getCompletedStoryPoints()
    {
        return $this->getCompletedTickets()->sum('story_points');
    }

    /**
     * @deprecated use getTotalEstimate instead
     * @return mixed
     */
    public function getTotalStoryPoints()
    {
        return $this->tickets()->sum('story_points');
    }

    public function getTotalCompletedEstimate()
    {
        return $this->getCompletedTickets()->sum('estimate');
    }

    public function getTotalEstimate()
    {
        return $this->tickets()->sum('estimate');
    }

    public function getTotalRemainingEstimate()
    {
        return $this->getTotalEstimate() - $this->getTotalCompletedEstimate();
    }

    /**
     * @deprecated use getTotalCompletedEstimatePercentage
     * @return int|string
     */
    public function getPercentCompletedStoryPoints()
    {
        if ($this->getTotalStoryPoints() == 0)
            return 0;

        return number_format(($this->getCompletedStoryPoints() / $this->getTotalStoryPoints()) * 100, 2);
    }

    public function getTotalCompletedEstimatePercentage()
    {
        if ($this->getTotalEstimate() == 0)
            return 0;

        return number_format(($this->getTotalCompletedEstimate() / $this->getTotalEstimate()) * 100, 2);
    }

    public function getPercentCompletedStories($decimals = 0)
    {
        if ($this->getTotalStories() == 0)
            return 0;

        return number_format(($this->getCompletedStories() / $this->getTotalStories()) * 100, $decimals);
    }

    public function getPercentCompletedSubtasks($decimals = 0)
    {
        if ($this->getTotalSubtasks() == 0)
            return 0;

        return number_format($this->getCompletedSubtasks()/$this->getTotalSubtasks()*100, $decimals);

    }

    /**
     * This function will return information grouped by User Story TYPE
     * Support, Bug, Requirement, Spike, Recurrent, Empty (when not assigned)
     * @return array
     */
    public function getUserStoriesTypePercentages()
    {

        $colors = [
            'Requirement' => ['main' => '#1cc88a', 'hover' => '#17a673'],//verde
            'Support' => ['main' => '#4e73df', 'hover' => '#3b5399'],//azul
            'Bug' => ['main' => '#e74a3b', 'hover' => '#c22819'],//rojo
            'Spike' => ['main' => '#f6c23e', 'hover' => '#cea334'],//amarillo
            'Recurrent' => ['main' => '#dbd8ce', 'hover' => '#bdbab1'],//gris
            'Empty' => ['main' => '#a947c4', 'hover' => '#8d3ba3'],//violeta
        ];
        $typesUsCount= $this->belongsToMany(Ticket::class)//DB::table('tickets')
            ->select('type', DB::raw('count(*) as total'))
            ->where('is_story', true)
            ->groupBy('type')
            ->get();


        $typesHours = $this->belongsToMany(Ticket::class)//DB::table('tickets')
            ->select('type', DB::raw('sum(worked_hours) as total_invested_hours'))
            //->where('is_story', false)
            ->groupBy('type')
            ->get();

        $totalStories = $this->getTotalStories();
        $totalWorkedHours = $this->getTotalWorkedHours();

        //dd($types);
        $result = array();
        foreach ($typesUsCount as $type) {
            $label = ($type->type)? $type->type: 'Empty';

            $countPercentage = (floatval($totalStories) !== 0.0 )?number_format(($type->total / $totalStories) * 100, 2):0;

            $result[$label] = [
                'label' => $label,
                'total' => $type->total,
                'count_percentage' => $countPercentage,
                'total_invested_hours' => 0,
                'hours_percentage' => 0,
                'color' => (array_key_exists($label, $colors))?$colors[$label]: '#ABC123',
            ];
        }



        foreach ($typesHours as $type) {
            $label = ($type->type)? $type->type: 'Empty';

            $hoursPercentage = (floatval($totalWorkedHours) !== 0.0 )? number_format(($type->total_invested_hours / $totalWorkedHours) * 100, 2) : 0;

            if (array_key_exists($label, $result)) {
                $result[$label] = array_merge($result[$label],[
                    'total_invested_hours' => $type->total_invested_hours,
                    'hours_percentage' => $hoursPercentage,
                ]);
            }

        }


        return $result;
    }

    public function getAverageLeadTime()
    {
        $completedTickets = $this->getCompletedTickets();
        $totalTickets = $completedTickets->count();
        if ($totalTickets) {
            $totalLeadTime = 0;
            $completedTickets->each(function ($ticket) use (&$totalLeadTime) {
                $totalLeadTime += $ticket->getLeadTime();
            });

            return number_format($totalLeadTime/$totalTickets, 2);
        }
    }

    public function getAverageCycleTime()
    {
        $completedTickets = $this->tickets()->started()->completed();
        $totalTickets = $completedTickets->count();
        if ($totalTickets) {
            $totalCycleTime = 0;
            $completedTickets->each(function ($ticket) use (&$totalCycleTime) {
                $totalCycleTime += $ticket->getCycleTime();
            });

            return number_format($totalCycleTime/$totalTickets, 2);
        }
    }

    public function getTimeReport()
    {
        $this->weeklyHours = array();//week => total XXX, users [ foco => hs]
        $this->monthlyHours = array();//month => total XY, users => [ foco => hs]
        $this->userHours = array();//user_id => hours, tasks
        foreach ($this->tickets as $ticket) {
            $ticketTimes = TicketTime::where('ticket_assembla_id', $ticket->ticket_assembla_id)->get();

            foreach ($ticketTimes as $ticketTime) {
                $this->_trackTime($ticketTime);
            }


        }



        ksort($this->weeklyHours);
        //print print_r($weeklyHours, 1).PHP_EOL;
        ksort($this->monthlyHours);
        //print print_r($monthlyHours, 1).PHP_EOL;
        ksort($this->userHours);
        //print print_r($this-?userHours, 1).PHP_EOL;
        $this->_trackUserHours();//this function uses the monthly hours data
        //dd($this->monthlyHours);
        return array(
            'weekly_hours' => $this->weeklyHours,
            'monthly_hours' => $this->monthlyHours,
            'user_hours' => $this->userHours
        );
    }

    private function _trackTime($ticketTime)
    {
        $date = Carbon::parse($ticketTime->begin_at);
        $month = $date->month;
        $monday = Carbon::parse($ticketTime->begin_at)->startOfWeek()->format('Y-m-d'); // monday
        $sunday = Carbon::parse($ticketTime->begin_at)->endOfWeek()->format('Y-m-d');
        $weekOfYear = $date->weekOfYear;

        if (!array_key_exists($weekOfYear , $this->weeklyHours)) {
            //week data init
            $this->weeklyHours[$weekOfYear]['hours'] = 0;
            $this->weeklyHours[$weekOfYear]['tasks'] = 0;
            $this->weeklyHours[$weekOfYear]['surr_monday'] = $monday;
            $this->weeklyHours[$weekOfYear]['surr_sunday'] = $sunday;
            $this->weeklyHours[$weekOfYear]['users'] = array();
            $this->weeklyHours[$weekOfYear]['tickets'] = array();
        }
        if (!array_key_exists($month, $this->monthlyHours)) {
            //month data init
            $this->monthlyHours[$month]['hours'] = 0;
            $this->monthlyHours[$month]['tasks'] = 0;
            $this->monthlyHours[$month]['label'] = $date->format('F');
            $this->monthlyHours[$month]['users'] = array();
            $this->monthlyHours[$month]['tickets'] = array();

        }
        //TODO add logic to remove hardcoded users
        $users = [
            'c1n5gcr0Or6B_cbQarZsNG' => 'dlabate',
            'blHTwYuger44kaacwqjQYw' => 'ezegomez',
            'dUHuyGkPGr44k-acwqEsg8' => 'Pedro Rigoli',
            'aSD9Sgwzqr6OoBaH8tHBnc' => 'ealvian',
            'cQlRFOTpar35YEeJe5cbLA' => 'Aldo Bressan',
            'cxNMFQ6Fer5zdcacwqjQYw' => 'Andres Campos',
            'cC7iHg0oSr6BBdaIC_Qgzw' => 'Ariel Benítez',
            'aW_vfY1FGr6ioeaH8tHBnc' => 'Brenda Herrada',
            'aAbtrS7fKr6y_dcP_HzTya' => 'Barbara Irizaga',
            'brVttgsFOr543cdmr6QqzO' => 'Emanuel Arcos',
            'dNWJBO9war45rbacwqjQXA' => 'Elina Perez',
            'awkfUI9wer46vDacwqEsg8' => 'Esteban Savignone',
            'dzBlqaLhKr5O16acwqEsg8' => 'Esteban Campos',
            'd8r95QiVer6zj-aH8tHBnc' => 'Franco Aller',
            'ajLyFEiVir6A3ccK-zJOy8' => 'Federico Ackerley',
            'c-Z16O6uKr6QzLbK8JiBFu' => 'Ivan Fliess',
            'dDTcSCiNSr64ojaIC_Qgzw' => 'Julián García',
            'cc2NS0ZTSr4RS_acwqjQYw' => 'Jonatan Mayorano',
            'crp8MWHtur35i9eJe5cbLr' => 'Jose Maria Beltramini',
            'c5sp9uUXyr6Ok5cK-zJOy8' => 'Julieta Pisani',
            'aDiA_Cb2Wr6iNcacwqjQYw' => 'Matias Rodriguez',
            'acSpambtKr6lLldmr6CpXy' => 'Maximiliano Cipriano Raymond',
            'buOwlo1uer45NdacwqjQWU' => 'Martín Granate',
            'c6u2Cuuu4r6AFdbK8JiBFu' => 'Martin Perrotta',
            'athUCe0pCr5OFcacwqEsg8' => 'Mariano Zunini',
            'ddsWca79Wr44oYacwqjQXA' => 'Nicolas Alejandro Gandara',
            'cvixt811Gr4PBcacwqjQYw' => 'Nicolás Peric',
            'dBYqHcg2Cr5PRcdmr6CpXy' => 'Santiago Tolosa',
            'biwzyWoA0r64k4cK-zJOy8' => 'Argenis Bolivar',
            'a5Uwc0GEyr45yTacwqEsg8' => 'Alejandro Borria',
            'cKj68IVQKr6y4cbK8JiBFu' => 'Agustin Criniti',
            'coUuUSQCur6ONdbK8JiBFu' => 'Alejandro Tores',
            'cfpTrOT4Kr6ykbaIC_Qgzw' => 'Agustín Meliendrez',
            'a54TgShmar64kkcK-zJOy8' => 'Augusto Moita',
            'b2j2Q6lHmr64o1cK-zJOy8' => 'anicasio149904',
            'dwIgsm3t0r6l_dbK8JiBFu' => 'Berzi Vázquez',
            'aWV8meOPOr5Rtcdmr6CpXy' => 'Cristian Luis Torres',
            'aArQDQ0N4r6OorcK-zJOy8' => 'Carlos Augusto Aragón',
            'bON6CE_VCr6yoqaH8tHBnc' => 'Camilo Castro',
            'cIjT9Er0Kr6yolaH8tHBnc' => 'Christian Lo Iacono',
            'aeKlNgCQer6yZcaH8tHBnc' => 'Cristobal Lemoine',
            'd72yEIpfSr6ykPaH8tHBnc' => 'Conrado Maranguello',
            'bA693i8jOr6OkVaIC_Qgzw' => 'Daniel Alejandro Castro Arellano',
            'b62k1KbVCr64k7bK8JiBFu' => 'Daniel López',
            'aIf6yi5Tir6ykvaH8tHBnc' => 'Daniel Garac',
            'b7FWmaAaar6lBcacwqjQYw' => 'Diego Buzzalino',
            'a5ps1QoI8r64k7bK8JiBFu' => 'Diego Fernando Segura',
            'a66LHYhmar64kkcK-zJOy8' => 'Daniel Luna',
            'bYoBk2IxKr5PNcdmr6QqzO' => 'Diego Piu',
            'arje-QCWar6l3ddmr6QqzO' => 'Diego Sanabria',
            'aJX8Ymwzqr6PKfcP_HzTya' => 'dvadell@summasolutions.net',
            'c5lxXCUXyr6Ok5cK-zJOy8' => 'Dario Yucra',
            'a46XsuN0Kr6yVcbQarZsNG' => 'Ernesto Campos',
            'bhuk8soA0r64k4cK-zJOy8' => 'Esteban Finkelberg',
            'bj99iSoA0r64k4cK-zJOy8' => 'Exequiel Lares',
            'bdhqZwatmr67OzbQarZsNG' => 'Eleazar Mejias',
            'bv8aaSj78r65JcbK8JiBFu' => 'Esteban Provoste Molina',
            'csUS0AwMqr4RkdacwqjQWU' => 'Facundo Capua',
            'cnxLaKLWSr54o-acwqjQXA' => 'Federico Arostegui',
            'c5eLqIUXyr6Ok5cK-zJOy8' => 'Facundo Calvo',
            'cH61LI3LOr6QDmbQarZsNG' => 'Florencia Cerutti',
            'bQ8Z4G4_ar6RZccK-zJOy8' => 'Fernando Farias',
            'c48wtoUXyr6Ok5cK-zJOy8' => 'FERNANDO SALIDO',
            'aPoQx8lw0r6Ok8cP_HzTya' => 'Franco Vinciarelli',
            'dEbzq-iNSr64ojaIC_Qgzw' => 'Gonzalo Bide',
            'a8lATak4er6ioGacwqEsg8' => 'Giselly Conde',
            'b1O4Sa2hKr6BK5aIC_Qgzw' => 'Gastón Ledesma',
            'aDruZWuwOr6BZdbQarZsNG' => 'Giordhano Valdez',
            'cNY_X4Ym0r6iokaH8tHBnc' => 'Gonzalo Vicens',
            'a4svNqqx4r6lxcdmr6QqzO' => 'Gastón Villarino',
            'cXCCBQuu4r6yoFcP_HzTya' => 'Héctor Fernández',
            'dIgax2MPer6PxcbQarZsNG' => 'Hector Luis Herbas Cortez',
            'bQNZoo4_ar6RZccK-zJOy8' => 'H Emanuel Pereyra',
            'bY4DeosBKr6OGVbK8JiBFu' => 'Ignacio Battani',
            'aUrbD4bG0r5jlcacwqjQYw' => 'Ivan Masson',
            'bm_u1Cbkmr6PhcbK8JiBFu' => 'Jhordy Abonia',
            'a6zFhmRmir6OkRaH8tHBnc' => 'Javier Pons',
            'apzG4sWrmr6yhcbK8JiBFu' => 'Juan Barla',
            'bAZfCE8jOr6OkVaIC_Qgzw' => 'Julio De la Hoz',
            'cbrfo66iGr5Q03dmr6bg7m' => 'Julio Fernandez',
            'buAcvCWVir57nJdmr6QqzO' => 'Juan Ignacio Rodriguez',
            'cERg52_K4r4jvzacwqjQXA' => 'Juan Pedro Palli',
            'aSJaTSxl4r6OkwcK-zJOy8' => 'Jose David Restrepo Balbin',
            'bw6oDUj78r65JcbK8JiBFu' => 'jrimondi',
            'adbwzMmXar64kucK-zJOy8' => 'laguirre545893',
            'bMU7kYe1ar64pdaIC_Qgzw' => 'Luis Felipe Moreno',
            'dUlLpIkk0r6ykvbK8JiBFu' => 'Lisandro Rendon',
            'aDVRV02Gqr47aXacwqjQWU' => 'Lucas Jorge',
            'bNRMvSzpOr6yoXbK8JiBFu' => 'Luis Javier Romero',
            'cbU1po-7mr6Q3dcK-zJOy8' => 'lluque799571',
            'bENOLOZJur6lTIbK8JiBFu' => 'Lucas Michiels',
            'aKaGseYOir6ykUcP_HzTya' => 'Paul López',
            'cLR0tEbyCr6OoGcP_HzTya' => 'Leonardo Quenta',
            'akeuH09bir6jnxcP_HzTya' => 'Luis Sosa',
            'dP9X8Q2hur6zNcaIC_Qgzw' => 'Luciano Santos',
            'blr-WQN0Kr6ytdcK-zJOy8' => 'Leandro Valdés',
            'ckNzzshYqr64FdcP_HzTya' => 'Gabriel Vallejos',
            'bwtalg7Mir4OoJacwqjQYw' => 'Matias Alvarez Vilmasky',
            'aoDILILbir6ivcdmr6CpXy' => 'Cristian Marcet',
            'arrHT2RRer54rQdmr6QqzO' => 'Mariana Rodriguez Genaro',
            'dJgAW0Rp8r56tddmr6bg7m' => 'Matias Anoniz',
            'a6HwIqhmar64kkcK-zJOy8' => 'Mauricio Barchiesi',
            'dDymCqiNSr64ojaIC_Qgzw' => 'Maximiliano Enrique Dahn',
            'biVL52oA0r64k4cK-zJOy8' => 'Mauricio Esguerra',
            'cGUpJWYm0r6jalcK-zJOy8' => 'Matias Ibañez',
            'dErPfAiNSr64ojaIC_Qgzw' => 'Miguel Rojas',
            'aQdIIyOrur6OkFaH8tHBnc' => 'Maximiliano Lucca',
            'aHH1akOrur6PLnbK8JiBFu' => 'Marcel Martinez',
            'afwsYqJQCr6B7daH8tHBnc' => 'Martin Matus',
            'c6fzJmdv0r643daIC_Qgzw' => 'Miguel Angel Ramirez Medel',
            'aX8MusGpOr55Dvdmr6CpXy' => 'Mauricio Piccolo',
            'bEvcTasFOr56bEacwqEsg8' => 'Mauro Pintos',
            'cMi8IupHWr64o8aIC_Qgzw' => 'Margarita Rojas Romero',
            'a0C0wewzqr6QiAcK-zJOy8' => 'Marsha Schlesinger',
            'd4qZ-kiVer6BhdbK8JiBFu' => 'Matias Somoza',
            'b_V2Si_JCr6lldaH8tHBnc' => 'Matias Wagner',
            'c_PDCMTfir6yowaIC_Qgzw' => 'Melania Zilic',
            'cKVX3-f4ar64o1bK8JiBFu' => 'Nestor Armando Sanchez',
            'b5IOE493Cr5QX2dmr6QqzO' => 'Nicolas Yanuzzio',
            'cxeWEAcD0r6BxcbK8JiBFu' => 'Pablo Garcia',
            'd5Y_isXv0r6je-cK-zJOy8' => 'Pablo Gomez',
            'adyxFKmXar64kucK-zJOy8' => 'Paulo Dario Rosso',
            'cv2J3a6zer4QLEacwqjQXA' => 'Paola Tabacman',
            'bh1YSEoA0r64k4cK-zJOy8' => 'Patricio Tomé',
            'akilAeffKr6P4haH8tHBnc' => 'Rodrigo Carrera',
            'aO7FqCXkOr6OoFbQarZsNG' => 'Dario Ferreyra',
            'avLSa6Lbir6iooacwqEsg8' => 'David Sánchez Leiva',
            'cLKPIOEvqr5lWsacwqjQWU' => 'RodriCataldo',
            'alZYpqeG8r64klbQarZsNG' => 'Romer Quispe',
            'aR2-Iam8qr64oQaH8tHBnc' => 'Rafael Ramos',
            'adkIcwmXar64kucK-zJOy8' => 'Roberto Restrepo',
            'accDnITjCr6z4EcK-zJOy8' => 'Ricardo Vera',
            'a4ekn8n8Kr6QpccP_HzTya' => 'Santiago Leandro Cao',
            'ddxXyKbx8r6OoocK-zJOy8' => 'Santiago Martin Campos',
            'cFmecmhCmr6OyAcK-zJOy8' => 'Sebastián Charles',
            'cwXJMIt0qr5Odddmr6QqzO' => 'Sebastián González',
            'b-tbwghCir6Oo7bQarZsNG' => 'Carlos Gil',
            'bzrGTg_VCr6yoKcP_HzTya' => 'Sergio Perez',
            'bYKOU25cyr6io0bQarZsNG' => 'Silvina Gil Romero',
            'b7iLKkbVCr64k7bK8JiBFu' => 'Silvina Pérez',
            'bXuu6E_VCr6z7dcP_HzTya' => 'Nicolas Mañez',
            'cvS8IcBqqr6y5pcK-zJOy8' => 'Santiago Palacios',
            'dvr_q2IZer6PukcK-zJOy8' => 'Santiago Sanz',
            'cFt2tCj5er6jXqacwqjQXA' => 'Sebastian Stanich',
            'arGgzyheSr4QLcacwqjQYw' => 'Francisco Lopez',
            'adrzvmmXar64kucK-zJOy8' => 'Smith Vasquez',
            'bjK08koA0r64k4cK-zJOy8' => 'Violeta Raffin',
            'bwGJcmj78r65JcbK8JiBFu' => 'vyanez',
            'cLYAecpHWr64o8aIC_Qgzw' => 'wtito'
        ];
        if (!array_key_exists($ticketTime->user_assembla_id, $this->userHours)) {
            //user data init
            $this->userHours[$ticketTime->user_assembla_id]['hours'] = [];
            $this->userHours[$ticketTime->user_assembla_id]['tasks'] = [];
            $this->userHours[$ticketTime->user_assembla_id]['label'] = $users[$ticketTime->user_assembla_id];
        }
        
        $this->_trackMonthlyHours($ticketTime, $month);
        $this->_trackWeeklyHours($ticketTime, $weekOfYear);


    }

    /*
        ej:
            4 =>    [
                hours => 7.25,
                tasks => 10,
                label => May,
                users => ['user_assembla_id' => ['hours'=> 2.25, 'tasks' => 3]],
                tickets => [
                        'number' => 1511,
                        'description'=>,
                        'hours'=>,
                        //['user_assembla_id' =>,]//este dato puede ser un array si varias personas trackean en el mismo ticket
                    ]
            ]
        */
    //TODO la informacion mensual tiene que considerar el anyo!
    private function _trackMonthlyHours($ticketTime, $month)
    {
        $this->monthlyHours[$month]['hours'] += $ticketTime->hours;
        $this->monthlyHours[$month]['tasks'] += 1;

        if (!array_key_exists($ticketTime->user_assembla_id, $this->monthlyHours[$month]['users'])) {
            //init user data
            $this->monthlyHours[$month]['users'][$ticketTime->user_assembla_id]['hours'] = 0;
            $this->monthlyHours[$month]['users'][$ticketTime->user_assembla_id]['tasks'] = 0;
            $this->monthlyHours[$month]['users'][$ticketTime->user_assembla_id]['tickets'] = array();
        }

        $this->monthlyHours[$month]['users'][$ticketTime->user_assembla_id]['hours'] += $ticketTime->hours;
        $this->monthlyHours[$month]['users'][$ticketTime->user_assembla_id]['tasks'] += 1;

        if (!array_key_exists($ticketTime->ticket_number, $this->monthlyHours[$month]['users'][$ticketTime->user_assembla_id]['tickets'])) {
            //init ticket data
            $this->monthlyHours[$month]['users'][$ticketTime->user_assembla_id]['tickets'][$ticketTime->ticket_number] = [
                'description' => $ticketTime->description,
                'hours' => 0,
            ];
        }
        if (!array_key_exists($ticketTime->ticket_number, $this->monthlyHours[$month]['tickets'])) {
            $ticket = Ticket::getTicketByAssemblaId($ticketTime->ticket_assembla_id);

            $parent = $ticket->parent();
            $parentLabel = '';
            if ($parent) {
                $parentLabel = $parent->number.' '.$parent->name;
            }
            $this->monthlyHours[$month]['tickets'][$ticketTime->ticket_number] = [
                'description' => $ticketTime->description,
                'hours' => 0,
                'parent' => $parentLabel,
            ];
        }

        $this->monthlyHours[$month]['users'][$ticketTime->user_assembla_id]['tickets'][$ticketTime->ticket_number]['hours'] += $ticketTime->hours;
        $this->monthlyHours[$month]['tickets'][$ticketTime->ticket_number]['hours'] += $ticketTime->hours;

    }

    //TODO this function and all the reporting logic needs to be on a different class
    private function _trackWeeklyHours($ticketTime, $weekOfYear)
    {
        $this->weeklyHours[$weekOfYear]['hours'] += $ticketTime->hours;
        $this->weeklyHours[$weekOfYear]['tasks'] += 1;

        if (array_key_exists($ticketTime->user_assembla_id, $this->weeklyHours[$weekOfYear])) {
            $this->weeklyHours[$weekOfYear]['users'][$ticketTime->user_assembla_id]['hours'] += $ticketTime->hours;
            $this->weeklyHours[$weekOfYear]['users'][$ticketTime->user_assembla_id]['tasks'] += 1;
        } else {
            $this->weeklyHours[$weekOfYear]['users'][$ticketTime->user_assembla_id]['hours'] = $ticketTime->hours;
            $this->weeklyHours[$weekOfYear]['users'][$ticketTime->user_assembla_id]['tasks'] = 1;
        }
    }

    private function _trackUserHours()
    {
        foreach ($this->monthlyHours as $monthNumber => $monthHours) {
            foreach ($this->userHours as $userId => $userHour) {//$monthHours['users'] as $userId => $userHours) {
                if (array_key_exists($userId, $monthHours['users'])) {
                    $this->userHours[$userId]['hours'][] = $monthHours['users'][$userId]['hours'];
                    $this->userHours[$userId]['tasks'][] = $monthHours['users'][$userId]['tasks'];
                } else {
                    $this->userHours[$userId]['hours'][] = 0;
                    $this->userHours[$userId]['tasks'][] = 0;
                }

            }
        }

    }





    /*{
        foreach ($this->tickets as $ticket) {

            $ticketTimes = TicketTime::where('ticket_assembla_id', $ticket->ticket_assembla_id)->get();
            foreach ($ticketTimes as $ticketTime) {
                dd($ticketTime->ticket_number . ' ' . $ticketTime->hours . ' ' . $ticketTime->begin_at);
            }
            //TODO armar horas por (mes, o semana)
/*
 que te muestre las horas insumidas en los tickets por mes. Ej: Julio 34 hs; Junio 210 hs
> el sprint podría tirar horas por persona; mostrando por semana y abajo el total
ej:
            w1(13 al 19)    w2(20 al 26)    w3 (26 a hoy/)
Foco        28  			27		10
Jona        40			40		16
Nico         1			5		2
            69 (+3% respecto av)			72

             *
             *
             */
            //dd($ticketTimes->count());
            //dd($ticketTime->number+ ' '+$ticketTime->begin_at);
        //}
    //}
}

