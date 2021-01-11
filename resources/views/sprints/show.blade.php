@extends('layouts.app')
<?php
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
?>


@section('breadcrumbs',  Breadcrumbs::render('sprints.show',$sprint->projects->first(), $sprint))


@section('container-title', $sprint->getProjectName(). " | $sprint->name")


@section('content')
    <?php
        $percentCompletedStores = $sprint->getPercentCompletedStories();
        $percentCompletedSubtasks = $sprint->getPercentCompletedSubtasks();
    ?>
    <div class="container">

        <div class="row">
            <!-- Total Hours Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Worked Hours</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $sprint->getTotalWorkedHours() }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-hourglass-end fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Remaining Hours Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Remaining Hours</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $sprint->getTotalWorkingHours() }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-hourglass-start fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- US Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Stories</div>
                                <div class="row no-gutters align-items-center">
                                    <div class="col-auto">
                                        <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">{{ $sprint->getTotalStories() }}</div>
                                    </div>
                                    <div class="col" data-toggle="tooltip" data-placement="top" title="{{ $sprint->getCompletedStories() }} completed stories {{ $percentCompletedStores }}%">
                                        <div class="progress progress-sm mr-2">
                                            <div class="progress-bar {{Helper::getPercentageClass($percentCompletedStores)}}" role="progressbar" style="width: {{ $percentCompletedStores }}%" aria-valuenow="{{ $percentCompletedStores }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subtasks Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Subtasks</div>
                                <div class="row no-gutters align-items-center">
                                    <div class="col-auto">
                                        <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">{{ $sprint->getTotalSubtasks() }}</div>
                                    </div>
                                    <div class="col" data-toggle="tooltip" data-placement="top" title="{{ $sprint->getCompletedSubtasks() }} completed subtasks {{ $percentCompletedSubtasks }}%">
                                        <div class="progress progress-sm mr-2">
                                            <div class="progress-bar {{ Helper::getPercentageClass($percentCompletedSubtasks)  }}" role="progressbar" style="width: {{ $percentCompletedSubtasks }}%" aria-valuenow="{{ $percentCompletedSubtasks }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Reamaining Estimate (Story Points) Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Remaining Estimate</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $sprint->getTotalRemainingEstimate() }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estimate Completed (Story Points) Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2" style="border-left-color: #1cc88a !important;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Completed Estimate</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $sprint->getTotalCompletedEstimate() }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Estimate (Story Points) Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Estimate</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $sprint->getTotalEstimate() }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-6 col-lg-6">
                <div class="card shadow mb-4">
                    <!-- Card Header - Dropdown -->
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Horas por Mes</h6>
                        <!--div class="dropdown no-arrow">
                            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                                <div class="dropdown-header">Dropdown Header:</div>
                                <a class="dropdown-item" href="#">Action</a>
                                <a class="dropdown-item" href="#">Another action</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#">Something else here</a>
                            </div>
                        </div-->
                    </div>
                    <!-- Card Body -->
                    <div class="card-body">
                        <div class="chart-area">
                            <canvas id="sprintMonthlyHoursChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6 col-lg-6">
                <div class="card shadow mb-4">
                    <!-- Card Header - Dropdown -->
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Horas por Semana</h6>
                        <!--div class="dropdown no-arrow">
                            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                                <div class="dropdown-header">Dropdown Header:</div>
                                <a class="dropdown-item" href="#">Action</a>
                                <a class="dropdown-item" href="#">Another action</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#">Something else here</a>
                            </div>
                        </div-->
                    </div>
                    <!-- Card Body -->
                    <div class="card-body">
                        <div class="chart-area">
                            <canvas id="sprintWeeklyHoursChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6 col-lg-6">
                <div class="card shadow mb-4">
                    <!-- Card Header - Dropdown -->
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Horas por usuario</h6>
                        <!--div class="dropdown no-arrow">
                            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                                <div class="dropdown-header">Dropdown Header:</div>
                                <a class="dropdown-item" href="#">Action</a>
                                <a class="dropdown-item" href="#">Another action</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#">Something else here</a>
                            </div>
                        </div-->
                    </div>
                    <!-- Card Body -->
                    <div class="card-body">
                        <div class="chart-area">
                            <canvas id="userHoursChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pie Chart -->
            <div class="col-xl-4 col-lg-5">
                <div class="card shadow mb-4">
                    <!-- Card Header - Dropdown -->
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">US Types</h6>
                        <div class="dropdown no-arrow">
                            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                                <div class="dropdown-header">Dropdown Header:</div>
                                <a class="dropdown-item" href="#">Action</a>
                                <a class="dropdown-item" href="#">Another action</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#">Something else here</a>
                            </div>
                        </div>
                    </div>
                    <!-- US Type Body -->
                    <div class="card-body">
                        <div class="chart-pie pt-4 pb-2">
                            <canvas id="usTypeCount"></canvas>
                        </div>
                        <div class="mt-4 text-center small">
                            @foreach($sprint->getUserStoriesTypePercentages() as $i => $usType)
                                <span class="mr-2">
                                    <i class="fas fa-circle" style="color: {{$usType['color']['main']}}"></i> {{$usType['label']}}
                                </span>
                            @endforeach
                            <br/><span>Externo: cantidad de us de este tipo</span><br>
                            <span>Iterno: cantidad de horas de este tipo</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">{{ $sprint->name }} <a href="{{url("tickets/importTickets/{$sprint->id}")}}" style="float:right;">Import Tickets</a></div>

                    <div class="sprint-stats" style="margin-left: 40px">
                        - Total Invested Hours: {{ $sprint->getTotalWorkedHours() }} hs
                        <?php foreach ($sprint->getTimeReport()['monthly_hours'] as $key => $timeReport):?>
                        <pre>    Mes: {{ $key }} ({{ $timeReport['label'] }}) Total: {{$timeReport['hours']}} <?php //echo print_r($timeReport, 1)?>horas <?php echo '('.number_format($timeReport['hours']/$sprint->getTotalWorkedHours()*100, 2).'%)'?></pre>
                            <?php foreach ($timeReport['users'] as $assemblaUserId => $userHours):?>
                                <pre>        {{$users[$assemblaUserId]}} {{$userHours['hours']}} horas ({{$userHours['tasks']}} tasks)</pre>
                            <?php endforeach;?>
                        <?php endforeach?>

                        <?php
                        if (count($sprint->getTimeReport()['monthly_hours']) ) {
                                //print print_r($sprint->getTimeReport()['monthly_hours'][9]['tickets'],1);
                                //print print_r($sprint->getTimeReport(),1);
                        }


                            //print print_r($sprint->getUserStoriesTypePercentages());
                        ?><br>


                        - Stories without SP: {{ $sprint->getUserStoriesWithoutStoryPoints() }}<br>
                        - Stories with inconsistent states: {{ $sprint->getUserStoriesWithInconsistentState()  }}<br>
                        - Total tickets: {{ $sprint->getTotalTickets() }}<br>
                        - Total Stories: {{ $sprint->getTotalStories() }}<br>
                        - Total Subtasks: {{ $sprint->getTotalSubtasks() }}<br>
                        - Completed Stories: {{$sprint->getCompletedStories()}} {{ $percentCompletedStores  }}%<br>
                        - Completed Subtasks: {{$sprint->getCompletedSubtasks()}} {{ $percentCompletedSubtasks }}%<br>

                        - Completed Tickets: {{ $sprint->getCompletedTickets()->count() }}<br>
                        - Completed Story Points: {{ $sprint->getCompletedStoryPoints() }} ({{ $sprint->getTotalCompletedEstimatePercentage() }}%)<br>
                        - Total story points: {{ $sprint->getTotalStoryPoints() }}<br>

                        - Average Lead Time: {{ $sprint->getAverageLeadTime() }} days <br>
                        - Average Cycle Time: {{ $sprint->getAverageCycleTime() }} days <br>
                    </div>

                    <div class="time-report" style="margin-left: 40px;">


                        <?php
                            //
                            foreach ($sprint->getTimeReport()['weekly_hours'] as $key => $timeReport) {
                            //    print $key .' weekly_hours '.print_r($timeReport,1);
                            }
                        ?>



                    </div>

                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                            ❌: subtasks with invalid status<br/>
                            ⏱: horas trackeadas en la US<br/>
                            🚨: US sin story points estimados
                        <table>
                            <thead>
                                <th>Ticket</th><th>Status</th><th>SP</th><th>Total Hs</th><th>Hs subs</th><th>Hs tracked</th><th># subtasks</th>
                            </thead>
                            @forelse ($sprint->tickets as $ticket)
                                @if($ticket->is_story)

                                    <?php
                                    $status = '';
                                    ?>
                                    @if(count($ticket->getInvalidStatusSubtasks()) > 0)
                                        <?php
                                        $status = '❌';
                                        ?>
                                    @endif

                                    @if($ticket->worked_hours > 0)
                                            <?php
                                            $status .= '⏱';
                                            ?>
                                    @endif

                                        @if($ticket->story_points == 0)
                                            <?php
                                            $status .= '🚨';
                                            ?>
                                        @endif
                                    <tr>
                                        <td><?php echo $status;?>{{ $ticket->number }} {{ Helper::substrIf($ticket->name, 75)}}</td>
                                        <td>{{ $ticket->status }}</td>
                                        <td>{{ $ticket->story_points }}</td>
                                        <td>{{ $ticket->total_invested_hours }}</td>
                                        <td>{{ $ticket->getSubtasksTotalWorkedHours() }}</td>
                                        <td>{{ $ticket->getTotalTrackedTime() }}</td>
                                        <td>{{ $ticket->subtasks()->count() }}</td>
                                    </tr>
                                @endif

                            @empty
                                <p>No tickets assigned to this sprint yet.</p>
                            @endforelse
                        </table>

                        Users:
                            <ul>
                                @forelse ($sprint->users as $user)
                                    <li>
                                        {{ $user->name}}
                                    </li>


                                @empty
                                    <p>No tickets assigned to this sprint yet.</p>
                                @endforelse
                            </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        var percentages = {!! json_encode($sprint->getUserStoriesTypePercentages()) !!};
        var timeReport = {!! json_encode($sprint->getTimeReport()) !!};

        console.log(percentages);
        //console.log(timeReport);
    </script>
@endsection








