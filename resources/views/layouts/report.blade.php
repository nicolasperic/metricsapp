<html>
    <head>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/css/bootstrap-select.css" />
        <link href="{{ asset('css/reports.css') }}" rel="stylesheet">
        <title>MetricsApp Reports</title>
    </head>
    <body>
        <div class="report-container">
            @yield('content')
        </div>
    </body>
</html>

