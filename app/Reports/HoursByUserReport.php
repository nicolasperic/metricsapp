<?php
//TODO esta clase está repetida con USHoursReportTest!
namespace App\Reports;

use App\Integration\AssemblaRequest;
use App\Project;
use App\User;
use Illuminate\Support\Facades\Log;

/**
 * Class HoursByUserReport
 *
 * @package App\Reports
 */
class HoursByUserReport
{
    /**
     * @var array information required for the report
     * KEYS: wikiname, space_id, from_date and to_date
     */
    private $requestData;
    private $projects;
    /**
     * @var User
     */
    private $user;

    public function __construct($requestData, User $user)
    {
        $this->requestData= $requestData;
        //$project = Project::getProjectByAssemblaId(request('project'));//fuck! obtener todos los wikinames?
        foreach ($this->requestData['projects'] as $projectAssemblaId) {
            $project = Project::getProjectByAssemblaId($projectAssemblaId);
            $this->projects[$project->wikiname] = $projectAssemblaId;
        }


        if ($this->requestData['users']) {
            $this->requestData['users'] = array_combine($this->requestData['users'],$this->requestData['users']);
        }

        $this->user = $user;
    }



    //TODO la lógica de esta función está repetida en USHoursReportTest
    function execute()
    {
        $apicalls = 0;
        $startTime = time();

        Log::info('Starting HoursByUserReport');
        $teamMembers = [
            // 'd8r95QiVer6zj-aH8tHBnc' => 'Franco Aller',
            'cvixt811Gr4PBcacwqjQYw' => 'Nicolás Peric',
            'dNWJBO9war45rbacwqjQXA' => 'Elina Perez',
            'cc2NS0ZTSr4RS_acwqjQYw' => 'Jonatan Mayorano',
            // 'ajLyFEiVir6A3ccK-zJOy8' => 'Federico Ackerley',
            'buOwlo1uer45NdacwqjQWU' => 'Martín Granate',
            'aAbtrS7fKr6y_dcP_HzTya' => 'Barbara Irizaga',
            'dDTcSCiNSr64ojaIC_Qgzw' => 'Julián García',
        ];
        $teamMembers = $this->requestData['users'];
        //TODO add logic to remove hardcoded users
        //aVzzeMlw0r6RhdaIC_Qgzw, dUHuyGkPGr44k-acwqEsg8, aSD9Sgwzqr6OoBaH8tHBnc
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
        //wikiname => assembla_project_id
        $projects = [
            'AD-Barbieri' => 'ce1LaCpjCr6O96aH8tHBnc',
            'canaldeautopartes' => 'dpT43eCVCr54kBacwqjQYw',
            'cemaco' => 'dKs4GwzB8r4Pz7acwqjQYw',
            'pinturerias-rex' => 'atJlRad84r55JcacwqjQXA',
            'sommiercenter' => 'dxD3_KI5ur6ky6dmr6QqzO',
            'summa-internal-projects' => 'bPFF_gQfWr4PjCacwqjQWU',
            'Grupo-Grassi' => 'dTomygY3Gr6P7dbK8JiBFu'
        ];

        $projects = $this->projects;

        $hours = array();
        $totalHours = 0;
        $totalTasks = 0;
        $projectHours = array();

        $from = $this->requestData['from_date'];//'2020/12/14 00:00';
        $to = $this->requestData['to_date'];//'2020/12/20 23:59';
        foreach ($projects as $wikiname => $spaceId) {

            $page = 1;
            do {
                $queryParams = [
                    'spaces' => $spaceId,
                    'from' => $from,
                    'to' => $to,
                    'page' => $page,
                ];
                Log::info('About to get tasks for '.$wikiname.' page '.$page);

                $applicationKey = $this->user->assembla_key;
                $applicationSecret = $this->user->assembla_secret;
                $response = AssemblaRequest::get("tasks", $applicationKey, $applicationSecret, $queryParams);
                $apicalls++;
                $result = json_decode($response->getBody()->getContents(), 1);
                Log::info('Response for '.$wikiname.' is '.$response->getStatusCode());
                if (!is_array($result)) {
                    break;
                }

                foreach ($result as $timeTracked) {
                    if ($wikiname == 'summa-internal-projects') {
                        if ($teamMembers && !array_key_exists($timeTracked['user_id'], $teamMembers)) {
                            continue;
                        }
                    }
                    if (!array_key_exists($timeTracked['user_id'], $hours)) {
                        $hours[$timeTracked['user_id']]['hours']  = 0;
                        $hours[$timeTracked['user_id']]['tasks'] = 0;
                    }

                    if (!array_key_exists($wikiname, $projectHours)) {
                        $projectHours[$wikiname] = array();
                    }

                    if (!array_key_exists($timeTracked['user_id'], $projectHours[$wikiname])) {
                        $projectHours[$wikiname][$timeTracked['user_id']] = ['hours' => 0, 'tasks' => 0];
                    }


                    $projectHours[$wikiname][$timeTracked['user_id']]['hours'] += $timeTracked['hours'];
                    $projectHours[$wikiname][$timeTracked['user_id']]['tasks'] += 1;

                    $hours[$timeTracked['user_id']]['hours'] += $timeTracked['hours'];
                    $hours[$timeTracked['user_id']]['tasks'] += 1;
                    $totalHours += $timeTracked['hours'];
                    $totalTasks += 1;
                }


                $page++;

            } while(count($result) === 100);
        }

        Log::info('API Calls ended! Report output');
        $results = [];
        $results[] = '';
        $results[] = '======================================================'.PHP_EOL;
        $results[] = "Desde $from hasta $to".PHP_EOL;
        $results[] = '======================================================'.PHP_EOL;
        $results[] = 'Total Hours '.$totalHours.PHP_EOL;
        $results[] = 'Total Tasks '.$totalTasks.PHP_EOL;

        Log::info('Results first chunk');

        foreach ($projectHours as $wikiname => $projectData) {
            $results[] = '======================================================'.PHP_EOL;
            $results[] = "\t".$wikiname.PHP_EOL;
            $results[] = '======================================================'.PHP_EOL;
            $totalHours = 0;
            $totalTasks = 0;
            foreach ($projectData as $userId => $userHours) {
                $totalHours += $userHours['hours'];
                $totalTasks += $userHours['tasks'];

                $userName = (array_key_exists($userId, $users))?$users[$userId]: $userId;
                $results[] = str_pad($userName, 20)."\t".str_pad($userHours['tasks']. " tasks", 9) ." \t".$userHours['hours']. ' hours'.PHP_EOL;
            }
            $results[] = ''.PHP_EOL;
            $results[] = 'Project total hours '.$totalHours.' in '.$totalTasks.' tasks'.PHP_EOL;
        }
        Log::info('Project hours iteration');

        $results[] = PHP_EOL;
        $results[] = '======================================================'.PHP_EOL;
        $results[] = ' Hours grouped by Users '.PHP_EOL;
        $results[] = '======================================================'.PHP_EOL;

        foreach ($hours as $userId => $hoursData) {
            $userName = (array_key_exists($userId, $users))?$users[$userId]: $userId;
            $results[] = str_pad($userName, 20)."\t".str_pad($hoursData['tasks']. " tasks", 9). " \t".$hoursData['hours']. ' hours'.PHP_EOL;
        }
        Log::info('User hours iteration');
        //Log::info(print_r($results, 1));

        $endTime = time();
        $minutes = round(($endTime - $startTime)/60, 2);
        $results[] = ''.PHP_EOL;//adding a breakline
        $results[] = ''.PHP_EOL;//adding a breakline
        $results[] = "Execution time ". $minutes ." minutes".PHP_EOL;
        $results[] = 'Total API calls '.$apicalls.PHP_EOL;

        return $results;
    }




}