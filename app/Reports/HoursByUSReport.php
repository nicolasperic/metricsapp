<?php
//TODO esta clase está repetida con USHoursReportTest! A lo caco para sacar más rápido un reporte, hacer las cosas bien
namespace App\Reports;

use App\Dto\TicketAssociationDto;
use App\Dto\TicketDto;
use App\Integration\AssemblaGateway;
use App\Integration\AssemblaRequest;
use App\User;
use Illuminate\Support\Facades\Log;

/**
 * Class HoursByUSReport
 *
 * @package App\Reports
 */
class HoursByUSReport
{

    /** @var  array para agrupar las horas de cada US
     *  ticket_id > description (ticket_number + description )
     *      total hours y total tasks
     */
    private $userStories;
    /**
     * @var array para agrupar las horas de tickets que no son US ni subtasks;
     *  ticket_id => description, total hours, total tasks
     */
    private $noUserStories;
    /** @var  array para agrupar las horas trackeadas sin ticket >
     * user_id > hours y tasks */
    private $withoutTicket;
    /** @var  array para mapear subtasks con user stories >
     * subtask_id => user_story_id; de ser posible evitar algunas llamadas a la API */
    private $ticketAssociations;
    //^ de no tener asociacion subtask_id -> false
    /**
     * @var info solicitada a la API
     */
    private $ticketsApiData;

    private $typePercentages;//mantener % de horas y cantidad por tipo de US

    /**
     * @var int tracking the amount of API calls made
     */
    private $apicalls = 0;

    /**
     * @var array information required for the report
     * KEYS: wikiname, space_id, from_date and to_date
     */
    private $requestData;
    /**
     * @var User
     */
    private $user;

    public function __construct($requestData, User $user)
    {
        $this->requestData= $requestData;
        $this->user = $user;
    }




    function execute()
    {
        $this->apicalls = 0;
        $startTime = time();
        $users = [
            'aVzzeMlw0r6RhdaIC_Qgzw' => 'ezegomez',
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



        //reset data
        $this->userStories = [];
        $this->noUserStories = [];
        $this->withoutTicket = [];
        $this->ticketAssociations = [];
        $this->ticketsApiData = [];
        $totalHours = 0;
        $totalTasks = 0;

        //setting request parameters
        $wikiname = $this->requestData['wikiname'];
        $spaceId = $this->requestData['space_id'];
        $from = $this->requestData['from_date'];
        $to = $this->requestData['to_date'];

        /*$projects = [
            'AD-Barbieri' => 'ce1LaCpjCr6O96aH8tHBnc',
            //'canaldeautopartes' => 'dpT43eCVCr54kBacwqjQYw',
            //'cemaco' => 'dKs4GwzB8r4Pz7acwqjQYw',
            //'pinturerias-rex' => 'atJlRad84r55JcacwqjQXA',
            //'sommiercenter' => 'dxD3_KI5ur6ky6dmr6QqzO',
            //'summa-internal-projects' => 'bPFF_gQfWr4PjCacwqjQWU',
            //'Grupo-Grassi' => 'dTomygY3Gr6P7dbK8JiBFu'
        ];*/


        $page = 1;
        do {
            $queryParams = [
                'spaces' => $spaceId,
                'from' => $from,
                'to' => $to,
                'page' => $page,
            ];

            Log::info('query params '.print_r($queryParams, 1));
            $response = AssemblaRequest::get("tasks", $this->user->assembla_key, $this->user->assembla_secret, $queryParams);
            $this->apicalls++;
            $result = json_decode($response->getBody()->getContents(), 1);
            if (!is_array($result)) {
                break;
            }


            foreach ($result as $timeTracked) {
                $timetracked = false;

                if (trim($timeTracked['ticket_id']) != '') {//Tracked time to a ticket
                    if (!array_key_exists($timeTracked['ticket_id'], $this->ticketsApiData)) {
                        $this->_retrieveAndSetTicketInformation($wikiname, $timeTracked['ticket_number']);
                    }



                    if (array_key_exists($timeTracked['ticket_id'], $this->userStories)) {//it's a user story
                        $this->userStories[$timeTracked['ticket_id']]['hours'] += $timeTracked['hours'];
                        $this->userStories[$timeTracked['ticket_id']]['tasks'] += 1;
                        Log::info('Tracking T1 '.$timeTracked['hours'].' ticket number'.$timeTracked['ticket_number'].' US hs '.$this->userStories[$timeTracked['ticket_id']]['hours'].' US tasks '.$this->userStories[$timeTracked['ticket_id']]['tasks']);
                        $timetracked = true;
                    } else {//subtask

                        //subtask found on ticketAssociations, we can retrieve the user story ID without calling the API
                        if (array_key_exists($timeTracked['ticket_id'], $this->ticketAssociations) && $this->ticketAssociations[$timeTracked['ticket_id']] !== false) {
                            $this->userStories[$this->ticketAssociations[$timeTracked['ticket_id']]]['hours'] += $timeTracked['hours'];
                            $this->userStories[$this->ticketAssociations[$timeTracked['ticket_id']]]['tasks'] += 1;
                            $timetracked = true;

                            Log::info('Tracking T2 '.$timeTracked['hours'].' ticket number'.$timeTracked['ticket_number'].' US hs '.$this->userStories[$this->ticketAssociations[$timeTracked['ticket_id']]]['hours'].' US tasks '.$this->userStories[$this->ticketAssociations[$timeTracked['ticket_id']]]['tasks']);
                        } else {//subtask not found on ticketAssociations, need to retrieve related US from API if exists

                            $userStoryId = $this->_retrieveTicketAssociation($wikiname, $timeTracked['ticket_number']);
                            if ($userStoryId !== false) {
                                if (!array_key_exists($userStoryId, $this->userStories)) {
                                    $response = AssemblaRequest::get("spaces/{$wikiname}/tickets/id/{$userStoryId}", $this->user->assembla_key, $this->user->assembla_secret);
                                    $this->apicalls++;
                                    if ($response->getStatusCode() == 200) {
                                        $bodyContents = json_decode($response->getBody()->getContents(), 1);
                                        if ($bodyContents['is_story']) {
                                            $this->userStories[$userStoryId]['description'] = $bodyContents['number'].' '.$bodyContents['summary'];
                                            $this->userStories[$userStoryId]['total_invested_hours'] = $bodyContents['total_invested_hours'];
                                            $this->userStories[$userStoryId]['status'] = $bodyContents['status'];
                                            $this->userStories[$userStoryId]['hours'] = $timeTracked['hours'];
                                            $this->userStories[$userStoryId]['tasks'] = 1;

                                            Log::info('Tracking T3 '.$timeTracked['hours'].' ticket number'.$timeTracked['ticket_number'].' US hs '.$this->userStories[$userStoryId]['hours'].' US tasks '.$this->userStories[$userStoryId]['tasks']);
                                            $timetracked = true;
                                        } else {
                                            dd('hmm it has a subtask but is not a user story??');
                                        }
                                    }
                                } else {
                                    $this->userStories[$userStoryId]['hours'] += $timeTracked['hours'];
                                    $this->userStories[$userStoryId]['tasks'] += 1;
                                    Log::info('Tracking T4 '.$timeTracked['hours'].' ticket number'.$timeTracked['ticket_number'].' US hs '.$this->userStories[$userStoryId]['hours'].' US tasks '.$this->userStories[$userStoryId]['tasks']);
                                    $timetracked = true;
                                }

                            } else {
                                if (!array_key_exists($timeTracked['ticket_id'], $this->noUserStories)) {
                                    $this->noUserStories[$timeTracked['ticket_id']]['hours'] = 0;
                                    $this->noUserStories[$timeTracked['ticket_id']]['tasks'] = 0;
                                }

                                $this->noUserStories[$timeTracked['ticket_id']]['hours'] += $timeTracked['hours'];
                                $this->noUserStories[$timeTracked['ticket_id']]['tasks'] += 1;
                                Log::info('Tracking T5 '.$timeTracked['hours'].' ticket number'.$timeTracked['ticket_number'].' US hs '.$this->noUserStories[$timeTracked['ticket_id']]['hours'].' US tasks '.$this->noUserStories[$timeTracked['ticket_id']]['tasks']);
                                $timetracked = true;
                            }
                        }
                    }
                } else {
                    //no ticket ID
                    if (!array_key_exists($timeTracked['user_id'], $this->withoutTicket)) {
                        $this->withoutTicket[$timeTracked['user_id']]['hours']  = 0;
                        $this->withoutTicket[$timeTracked['user_id']]['tasks'] = 0;
                    }

                    $this->withoutTicket[$timeTracked['user_id']]['hours']  += $timeTracked['hours'];
                    $this->withoutTicket[$timeTracked['user_id']]['tasks'] += 1;
                    Log::info('Tracking T6 '.$timeTracked['hours'].' US hs '.$this->withoutTicket[$timeTracked['user_id']]['hours'].' US tasks '.$this->withoutTicket[$timeTracked['user_id']]['tasks']);
                    $timetracked = true;
                }

                if (!$timetracked) {
                    Log::info('[US time]  time not tracked for entry hs '.$timeTracked['hours'].' '.$timeTracked['ticket_id'].' '.$timeTracked['ticket_number']);
                }

                $totalHours += $timeTracked['hours'];
                $totalTasks += 1;

                Log::info('END Task Data hours '.$timeTracked['hours'].' ticket number'.$timeTracked['ticket_number'].' user_id '.$timeTracked['user_id'].' id'.$timeTracked['id']);
            }


            if (count($result) === 100) {
                $page++;
            }

        } while(count($result) === 100);





        $results = [];
        $results[]= '======================================================'.PHP_EOL;
        $results[] = "Desde $from hasta $to".PHP_EOL;
        $results[] = '======================================================'.PHP_EOL;
        $results[] = 'Total Hours '.$totalHours.PHP_EOL;
        $results[] = 'Total Tasks '.$totalTasks.PHP_EOL;


        $results[] = '======================================================'.PHP_EOL;
        $results[] = 'User Stories'.PHP_EOL;
        $results[] = '======================================================'.PHP_EOL;

        $this->typePercentages = [];// Type => hours; count; hours/percentage (sobre total stories hours); count/percentage (sobre total stories)
        $userStoriesTotalHours = 0;
        ksort($this->userStories);
        $results[] = 'ticket,total_hours, hours, tasks, status, type'.PHP_EOL;
        foreach ($this->userStories as $id => $storyData) {
            $results[] = $storyData['description'].', '.$storyData['total_invested_hours'].', '.$storyData['hours'].', '.$storyData['tasks'].', '.$storyData['status'].', '.$storyData['type'].PHP_EOL;
            $userStoriesTotalHours += $storyData['hours'];
            self::_keepTrackOfTypeData($storyData);
        }

        $results[] = ''.PHP_EOL;
        $results[] = 'User Stories Total Hours: '.$userStoriesTotalHours.PHP_EOL;
        //$results[] = print_r($this->userStories, 1).PHP_EOL;
        //$results[] = print_r($this->noUserStories, 1).PHP_EOL;

        $noUserStoriesTotalHours = 0;
        if ($this->noUserStories) {
            $results[] = '======================================================'.PHP_EOL;
            $results[] = 'Tasks (not a User Story)'.PHP_EOL;
            $results[] = '======================================================'.PHP_EOL;
            ksort($this->noUserStories);
            $results[] = 'ticket,total_hours, hours, tasks, status'.PHP_EOL;
            foreach ($this->noUserStories as $id => $ticketData) {
                $results[] = $ticketData['description'].', '.$ticketData['total_invested_hours'].', '.$ticketData['hours'].', '.$ticketData['tasks'].', '.$ticketData['status'].PHP_EOL;
                $noUserStoriesTotalHours += $ticketData['hours'];
            }

            $results[] = ''.PHP_EOL;
            $results[] = 'Tasks (not a User Story) Total Hours: '.$noUserStoriesTotalHours.PHP_EOL;
        }


        //$results[] = print_r($this->withoutTicket, 1).PHP_EOL;

        $withoutTicketTotalHours = 0;
        if (count($this->withoutTicket)) {
            $results[] = '======================================================'.PHP_EOL;
            $results[] = ' Tracked time without ticket'.PHP_EOL;
            $results[] = '======================================================'.PHP_EOL;
            $results[] = 'username, hours, tasks'.PHP_EOL;
            foreach ($this->withoutTicket as $userId => $data) {
                $username = (array_key_exists($userId, $users))?$users[$userId]: $userId;
                $results[] = $username.','.$data['hours'].','.$data['tasks'].PHP_EOL;
                $withoutTicketTotalHours += $data['hours'];
            }

            $results[] = ''.PHP_EOL;
            $results[] = 'Tracked time without ticket Total Hours: '.$withoutTicketTotalHours.PHP_EOL;
        }

        $results[] = ''.PHP_EOL;//adding a breakline

        foreach ($this->typePercentages as $type => $typeData) {
            $typeTotalHours = $this->typePercentages[$type]['total_hours'];
            $typeTotalTickets = $this->typePercentages[$type]['total_tickets'];

            $typeTotalHoursPercentage =  ($userStoriesTotalHours != 0)?number_format(($typeTotalHours / $userStoriesTotalHours) * 100, 2) : 0;
            $typeTotalTicketsPercentage = (count($this->userStories) != 0)?  number_format(($typeTotalTickets/ count($this->userStories)) * 100, 2) : 0;

            $results[] = str_pad($type, 20)."\t\t".$typeTotalTickets.' user stories ('.$typeTotalTicketsPercentage.'%)'.PHP_EOL;
            $results[] = str_pad('', 20)."\t\t".$typeTotalHours.' horas ('.$typeTotalHoursPercentage.'%)'.PHP_EOL;
        }


        $results[] = ''.PHP_EOL;//adding a breakline
        $results[] = 'Total Tracked Time '.$totalHours.' vs Total Hours per section (all added) '.($userStoriesTotalHours+$noUserStoriesTotalHours+$withoutTicketTotalHours).PHP_EOL;

        $endTime = time();
        $minutes = round(($endTime - $startTime)/60, 2);
        $results[] = ''.PHP_EOL;//adding a breakline
        $results[] = ''.PHP_EOL;//adding a breakline
        $results[] = "Execution time ". $minutes ." minutes".PHP_EOL;
        $results[] = 'Total API calls '.$this->apicalls.' (pages '.$page.')'.PHP_EOL;

        return $results;
    }


    /**
     * This beautiful function will retrieve the ticket information using the API
     * If the ticket is a subtask it will fetch the associations to try to get the related user story
     *
     * @param $space
     * @param $ticketNumber
     */
    private function _retrieveAndSetTicketInformation($space, $ticketNumber)
    {
        $assemblaGateway = new AssemblaGateway($this->user);
        /** @var TicketDto $ticketDto */
        $ticketDto = $assemblaGateway->getTicketBySpaceAndNumber($space, $ticketNumber);
        $this->apicalls++;

        if ($ticketDto !== false) {

            $ticketId = $ticketDto->getTicketAssemblaId();
            $this->ticketsApiData[$ticketId] = [
                'is_story' => $ticketDto->isStory(),
                'description' => $ticketDto->getDescription(),
                'total_invested_hours' => $ticketDto->getTotalInvestedHours()
            ];

            if ($ticketDto->isStory() && !array_key_exists($ticketId, $this->userStories)) {
                $this->userStories[$ticketId]['description'] = $ticketDto->getDescription();
                $this->userStories[$ticketId]['total_invested_hours'] = $ticketDto->getTotalInvestedHours();
                $this->userStories[$ticketId]['status'] = $ticketDto->getStatus();
                $this->userStories[$ticketId]['hours'] = 0;
                $this->userStories[$ticketId]['tasks'] = 0;
                $this->userStories[$ticketId]['type'] = ($ticketDto->getType())? $ticketDto->getType() : 'Empty';

            } else {
                $userStoryId = $this->_retrieveTicketAssociation($space, $ticketNumber);
                if ($userStoryId !== false) {
                    if (!array_key_exists($userStoryId, $this->userStories)) {
                        $response = AssemblaRequest::get("spaces/{$space}/tickets/id/{$userStoryId}", $this->user->assembla_key, $this->user->assembla_secret);
                        $this->apicalls++;
                        if ($response->getStatusCode() == 200) {
                            $bodyContents = json_decode($response->getBody()->getContents(), 1);
                            if ($bodyContents['is_story']) {
                                $this->userStories[$bodyContents['id']]['description'] = $bodyContents['number'].' '.$bodyContents['summary'];
                                $this->userStories[$bodyContents['id']]['total_invested_hours'] = $bodyContents['total_invested_hours'];
                                $this->userStories[$bodyContents['id']]['status'] = $bodyContents['status'];
                                $this->userStories[$bodyContents['id']]['hours'] = 0;
                                $this->userStories[$bodyContents['id']]['tasks'] = 0;
                                $this->userStories[$bodyContents['id']]['type'] = self::_getTicketType($bodyContents['custom_fields']);
                            } else {
                                dd('hmm it has a subtask but is not a user story??');
                            }
                        }
                    }

                } else {
                    if (!array_key_exists($ticketId, $this->noUserStories)) {
                        //the task is not a user story neither a subtask since it has no association as subtask
                        $this->noUserStories[$ticketId]['description'] = $ticketDto->getDescription();
                        $this->noUserStories[$ticketId]['total_invested_hours'] = $ticketDto->getTotalInvestedHours();
                        $this->noUserStories[$ticketId]['status'] = $ticketDto->getStatus();
                        $this->noUserStories[$ticketId]['hours'] = 0;
                        $this->noUserStories[$ticketId]['tasks'] = 0;
                    }

                }
            }
        }
    }

    private function _getTicketType($customFields)
    {
        $type = 'Empty';
        if (array_key_exists('Type',$customFields) && trim($customFields['Type']) != '') {
            $type = $customFields['Type'];
        }

        return $type;
    }

    private function _keepTrackOfTypeData($storyData)
    {
        if (array_key_exists('type', $storyData)) {

            $type = $storyData['type'];
        } else {
            $type = 'Empty';
            dd($storyData);
        }

        if (!array_key_exists($type, $this->typePercentages)) {
            $this->typePercentages[$type] = [
                'total_hours' => 0,
                'total_tickets' => 0,
            ];
        }

        $this->typePercentages[$type]['total_hours'] += $storyData['hours'];
        $this->typePercentages[$type]['total_tickets'] += 1;
    }


    private function _retrieveTicketAssociation($space, $ticketNumber)
    {
        $assemblaGateway = new AssemblaGateway($this->user);
        $ticketAssociations = $assemblaGateway->getTicketAssociationsBySpaceAndNumber($space, $ticketNumber);
        $this->apicalls++;
        if ($ticketAssociations !== false) {
            /** @var TicketAssociationDto $association */
            foreach ($ticketAssociations as $association) {
                if ($association->getRelationship() === AssemblaGateway::STORY_RELATION) {
                    //$subtaskId = $association['ticket1_id'];
                    return  $association->getTicket2Id();//returning user story ID
                }
            }
        }

        return false;//the received ticketNumber has no subtask relation
    }

}