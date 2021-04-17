@if($chart->hasInformation())
        <div class="col-xl-{{$chart->getWidth()}} col-lg-{{$chart->getWidth()}}">
            <div class="card shadow mb-4">
                <!-- Card Header - Dropdown -->
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">{{ $chart->getChartTitle() }}</h6>
                    <i class="last-sync" style="font-size: 11px;">Stats from {{Helper::getTimeDiff($chart->getLastUpdated())}}</i>
                </div>
                <!-- Card Body -->
                <div class="card-body">
                        <div class="chart-area">
                            <canvas id="{{$chart->getElementId()}}"></canvas>
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


        

        var element = document.getElementById("{!! $chart->getElementId() !!}");


        var barChart = new Chart(element,{
            "type": '{!! $chart->getChartType() !!}',
            "data":{
                labels: {!! json_encode($chart->getLabels()) !!},
                datasets: {!! json_encode($chart->getDatasets()) !!}
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

