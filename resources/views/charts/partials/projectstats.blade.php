@if($doughnutChart->hasInformation())

        <div class="col-xl-{{$doughnutChart->getWidth()}} col-lg-{{$doughnutChart->getWidth()}}">
            <div class="card shadow mb-4">
                <!-- Card Header - Dropdown -->
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">{{ $doughnutChart->getChartTitle() }}</h6>
                    <i class="last-sync" style="font-size: 11px;">Stats from {{Helper::getTimeDiff($doughnutChart->getLastUpdated())}}</i>
                </div>
                <!-- Card Body -->
                <div class="card-body">
                    <div class="col-xl-4 col-lg-4" style="float: left;">
                        <div class="chart-pie">
                            <canvas id="{{$doughnutChart->getElementId()}}"></canvas>
                        </div>
                        @if(count($doughnutChart->getDatasets()) > 1)
                            <div class="text-center small">
                                <span>External: amount of US for each type</span><br>
                                <span>Internal: amount of hours for each type</span>
                            </div>
                        @endif
                    </div>

                    <div class="col-xl-5 col-lg-5" style="float: right; padding-right: 0px;">
                        <div class="card-body">
                            <div class="chart-area">
                                <canvas id="{{$barChart->getElementId()}}"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-lg-3" style="float: right; padding-right: 0px;">
                        <div class="user-stories-types mt-2 small">


                            <?php $dataset = $doughnutChart->getDatasets()[0]; $labels = $doughnutChart->getLabels();?>
                            <?php arsort($dataset['data']); $totalHours = 0; $totalDatasetTwo = 0;?>
                            @foreach($dataset['data'] as $i => $percentage)
                                    <div class="user-story-type">
                                        <i class="fas fa-circle" style="color: {{$dataset['backgroundColor'][$i]}}"></i>
                                        <span class="type-label">{{$labels[$i]}}</span>
                                        <div class="user-story-type-stats">
                                            <span class="dataset-stats">{{($dataset['realValues'][$i])? $dataset['realValues'][$i]: 0}} {{$dataset['dataLabel']}} ({{$percentage}}%)</span>
                                            @if(count($doughnutChart->getDatasets()) > 1)
                                                <?php
                                                    $secondDataset = $doughnutChart->getDatasets()[1];
                                                    $totalDatasetTwo += $secondDataset['realValues'][$i]
                                                ?>
                                                <span class="dataset-stats">{{($secondDataset['realValues'][$i])? $secondDataset['realValues'][$i]: 0}} {{$secondDataset['dataLabel']}} ({{$secondDataset['data'][$i]}}%)</span>
                                            @endif
                                        </div>
                                    </div>
                                <?php $totalHours += $dataset['realValues'][$i]; ?>
                            @endforeach

                                @if(count($doughnutChart->getDatasets()) == 1)
                                    <div class="user-story-type">
                                        <i class="fas fa-circle" style="color: black"></i>
                                        <span class="type-label">Total Hours</span>
                                        <div class="user-story-type-stats">
                                            <span class="dataset-stats">{{$totalHours}}</span>
                                        </div>
                                    </div>
                                @elseif(count($doughnutChart->getDatasets()) == 2)
                                    <div class="user-story-type">
                                        <i class="fas fa-circle" style="color: black"></i>
                                        <span class="type-label">Totals</span>
                                        <div class="user-story-type-stats">
                                            <span class="dataset-stats">{{$totalHours}} {{$dataset['dataLabel']}}</span>
                                            <span class="dataset-stats">{{$totalDatasetTwo}} {{$secondDataset['dataLabel']}}</span>
                                        </div>
                                    </div>
                                @endif

                                <style>
                                    .user-story-type {

                                    }
                                    .type-label {
                                        text-transform: uppercase;
                                        font-weight: bold;
                                        font-size: 1.2em;
                                    }
                                    .dataset-stats {
                                        display: block;
                                        margin-left: 17px;
                                    }
                                </style>

                        </div>
                    </div>


                </div>
            </div>
        </div>

    <script src="{{ asset('vendor/chart.js/Chart.min.js') }}"></script>

    <script type="text/javascript">

        Chart.defaults.global.defaultFontFamily = 'Nunito', '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
        Chart.defaults.global.defaultFontColor = '#858796';

        function number_format(number, decimals, dec_point, thousands_sep) {
            // *     example: number_format(1234.56, 2, ',', ' ');
            // *     return: '1 234,56'
            number = (number + '').replace(',', '').replace(' ', '');
            var n = !isFinite(+number) ? 0 : +number,
                    prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
                    sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
                    dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
                    s = '',
                    toFixedFix = function(n, prec) {
                        var k = Math.pow(10, prec);
                        return '' + Math.round(n * k) / k;
                    };
            // Fix for IE parseFloat(0.55).toFixed(0) = 0;
            s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
            if (s[0].length > 3) {
                s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
            }
            if ((s[1] || '').length < prec) {
                s[1] = s[1] || '';
                s[1] += new Array(prec - s[1].length + 1).join('0');
            }
            return s.join(dec);
        }


        

        var ctx = document.getElementById("{!! $doughnutChart->getElementId() !!}");


        var usTypeCount = new Chart(ctx, {
            type: '{!! $doughnutChart->getChartType() !!}',
            data: {
                labels: {!! json_encode($doughnutChart->getLabels()) !!},
                datasets: {!! json_encode($doughnutChart->getDatasets()) !!}
            },
            options: {
                maintainAspectRatio: false,
                tooltips: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    borderColor: '#dddfeb',
                    footerFontColor: '#6e707e',
                    titleFontColor: '#6e707e',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: true,
                    caretPadding: 10,
                    callbacks: {
                        afterTitle: function() {
                            window.total = 0;
                            window.datasetlabel = '';
                        },
                        label: function (tooltipItem, chart) {
                            var dataset = chart.datasets[tooltipItem.datasetIndex];
                            var meta = dataset._meta[Object.keys(dataset._meta)[0]];


                            var i;
                            for (i = 0; i < meta.data.length; i++) {
                                if (!meta.data[i].hidden) {
                                    var realValue = chart.datasets[tooltipItem.datasetIndex]["realValues"][i];
                                    window.total += realValue;
                                }
                            }

                            window.datasetlabel = chart.datasets[tooltipItem.datasetIndex]["dataLabel"];

                            var total = meta.total;
                            var currentValue = dataset.data[tooltipItem.index];
                            var percentage = parseFloat((currentValue/total*100).toFixed(1));
                            var realValue = chart.datasets[tooltipItem.datasetIndex]["realValues"][tooltipItem.index] + " "+ chart.datasets[tooltipItem.datasetIndex]["dataLabel"];
                            return realValue + ' (' + percentage + '%)';

                            return percentage + "%";
                        },
                        title: function (tooltipItem, chart) {

                            return chart.labels[tooltipItem[0].index];

                        },
                        footer: function() {
                            return "Total: " + number_format(window.total, 2) + " " + window.datasetlabel
                        }
                    }
                },
                legend: {
                    display: true
                },
                cutoutPercentage: 30,
            },
        });



        var element = document.getElementById("{!! $barChart->getElementId() !!}");


        var barChart = new Chart(element,{
            "type": '{!! $barChart->getChartType() !!}',
            "data":{
                labels: {!! json_encode($barChart->getLabels()) !!},
        datasets: {!! json_encode($barChart->getDatasets()) !!}
        },
        "options":{
            "scales":{
                "xAxes":[{
                    "ticks":{
                        "beginAtZero":true
                    }
                }]
            },
            legend: {
                display: false
            },
            maintainAspectRatio: false
        }
        });
    </script>
@endif

