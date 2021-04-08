@if($chart->hasInformation())

        <div class="col-xl-12 col-lg-12">
            <div class="card shadow mb-4">
                <!-- Card Header - Dropdown -->
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">{{ $chart->getChartTitle() }}</h6>
                </div>
                <!-- Card Body -->
                <div class="card-body">
                    <div class="col-xl-6 col-lg-6" style="float: left;">
                        <div class="chart-area">
                            <canvas id="{{$chart->getElementId()}}"></canvas>
                        </div>
                    </div>


                    <div class="col-xl-6 col-lg-6" style="float: right; padding-right: 0px;">
                        <div class="user-stories-types mt-2 small">

                            <?php $dataset = $chart->getDatasets()[0]; $labels = $chart->getLabels();?>
                            <?php arsort($dataset['data']);?>
                            @foreach($dataset['data'] as $i => $percentage)
                                    <div class="user-story-type">
                                        <i class="fas fa-circle" style="color: {{$dataset['backgroundColor'][$i]}}"></i>
                                        <span class="type-label">{{$labels[$i]}}</span>
                                        <div class="user-story-type-stats">
                                            <span class="hours">{{$dataset['realValues'][$i]}} hours ({{$percentage}}%)</span>
                                        </div>
                                    </div>
                            @endforeach
                                <style>
                                    .user-story-type {

                                    }
                                    .type-label {
                                        text-transform: uppercase;
                                        font-weight: bold;
                                        font-size: 1.2em;
                                    }
                                    .hours {
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


        

        var ctx = document.getElementById("{!! $chart->getElementId() !!}");


        var usTypeCount = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($chart->getLabels()) !!},
                datasets: {!! json_encode($chart->getDatasets()) !!}
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

                            var total = meta.total;
                            var currentValue = dataset.data[tooltipItem.index];
                            var percentage = parseFloat((currentValue/total*100).toFixed(1));
                            var realValue = chart.datasets[tooltipItem.datasetIndex]["realValues"][tooltipItem.index] + " horas";
                            return realValue + ' (' + percentage + '%)';

                            return percentage + "%";
                        },
                        title: function (tooltipItem, chart) {

                            return chart.labels[tooltipItem[0].index];

                        },
                        footer: function() {

                            return "Total: " + number_format(window.total, 2) + " hours";
                        }
                    }
                },
                legend: {
                    display: true
                },
                cutoutPercentage: 30,
            },
        });
    </script>
@endif

