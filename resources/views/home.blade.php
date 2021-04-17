@extends('layouts.app')

@section('breadcrumbs',  Breadcrumbs::render('home'))
@section('content')
    <div class="row justify-content-center">
        @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @endif
        <!--div class="col-md-12">
            <div class="card shadow mb-12">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Dashboard</h6>
                </div>
                <div class="card-body">


                    Welcome Back!
                </div>
            </div>
        </div-->

        <div class="col-md-12" style="margin-bottom: 25px;">
            <blockquote class="kelvin">
                What is not defined cannot be measured. What is not measured, cannot be improved. What is not improved, is always degraded.
                <footer>William T. Kelvin</footer>
            </blockquote>
        </div>


        <?php $lineChart = new \App\Charts\Adapters\ProjectLineChart();?>
        @include('charts.partials.line', ['chart' => $lineChart->generateStarredProjectsMonthlyHoursChart()])

        <?php
            $doughnutChart = new \App\Charts\Adapters\ProjectDoughnutChart();
            $barChart = new \App\Charts\Adapters\ProjectBarChart();
        ?>
        @include('charts.partials.projectstats', [
            'doughnutChart' => $doughnutChart->generateStarredProjectsCurrentMonthHoursChart(),
            'barChart' => $barChart->generateHoursPerUserBarChartForCurrentMonth(),
        ])
        @include('charts.partials.projectstats', [
            'doughnutChart' => $doughnutChart->generateStarredProjectsLastMonthHoursChart(),
            'barChart' => $barChart->generateHoursPerUserBarChartForLastMonth(),
        ])



        <div class="col-md-12" style="margin-bottom: 25px;">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="grid grid-cols-1 md:grid-cols-2">
                    <div class="p-6">
                        <div class="flex items-center">
                            <i class="fas fa-book-open fa-sm text-gray-400" style="font-size: 26px;"></i>
                            <div class="ml-4 text-lg leading-7 font-semibold"><a href="{{url('/docs')}}" class="underline text-gray-900 dark:text-white" target="_blank">Documentation</a></div>
                        </div>

                        <div class="ml-12">
                            <div class="mt-2 text-gray-600 dark:text-gray-400 text-sm">
                                Each section and feature of the application described in depth. Visit the <a href="{{url('/docs')}}" class="underline">documentation</a> for a Product detailed guide that will allow you to take the most out of all features.
                            </div>
                        </div>
                    </div>

                    <div class="p-6 border-t border-gray-200 dark:border-gray-700 md:border-t-0 md:border-l">
                        <div class="flex items-center">
                            <i class="fas fa-cog fa-sm text-gray-400" style="font-size: 26px;"></i>

                            <div class="ml-4 text-lg leading-7 font-semibold"><a href="{{ route('projects.index') }}" class="underline text-gray-900 dark:text-white">Setting up your Spaces</a></div>
                        </div>

                        <div class="ml-12">
                            <div class="mt-2 text-gray-600 dark:text-gray-400 text-sm">
                                Define which spaces are the ones you work with the most by starring them for easy access and for including them in the Current Milestone View. Learn which other configurations you can tweak by visiting the <a href="/docs/1.0/registration/first_steps#configuring-projects" class="underline" target="_blank">documentation</a>.
                            </div>
                        </div>
                    </div>

                    <div class="p-6 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center">
                            <i class="fas fa-file fa-sm text-gray-400" style="font-size: 26px;"></i>
                            <div class="ml-4 text-lg leading-7 font-semibold"><a href="{{ route('reports.index') }}" class="underline text-gray-900 dark:text-white">Reports</a></div>
                        </div>

                        <div class="ml-12">
                            <div class="mt-2 text-gray-600 dark:text-gray-400 text-sm">
                                Generate reports of tracked time grouped by Users and Spaces or grouped by User Stories! Check out the Milestones stats report for easily gathering metrics on multiple milestones. To learn more check out the <a href="/docs/1.0/reports#reports" class="underline" target="_blank">reports documentation</a>.
                            </div>
                        </div>
                    </div>

                    <div class="p-6 border-t border-gray-200 dark:border-gray-700 md:border-l">
                        <div class="flex items-center">
                            <i class="fas fa-chart-pie fa-sm text-gray-400" style="font-size: 26px;"></i>
                            <div class="ml-4 text-lg leading-7 font-semibold text-gray-900 dark:text-white"> <a href="{{ route('sprints.current') }}">Current Milestones View</a></div>
                        </div>

                        <div class="ml-12">
                            <div class="mt-2 text-gray-600 dark:text-gray-400 text-sm">
                                Get the insight you need to manage your projects on a more efficient way. Get to see in just one place how your spaces current milestones are performing. Global stats will be calculated allowing you to get useful metrics like team velocity. For more information about this view visit the full <a href="/docs/1.0/sprints/current#current" class="underline" target="_blank">documentation</a>.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>



        <!-- Styles -->
        <style>
            /*! normalize.css v8.0.1 | MIT License | github.com/necolas/normalize.css */html{line-height:1.15;-webkit-text-size-adjust:100%}body{margin:0}a{background-color:transparent}[hidden]{display:none}html{font-family:system-ui,-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica Neue,Arial,Noto Sans,sans-serif,Apple Color Emoji,Segoe UI Emoji,Segoe UI Symbol,Noto Color Emoji;line-height:1.5}*,:after,:before{box-sizing:border-box;border:0 solid #e2e8f0}a{color:inherit;text-decoration:inherit}svg,video{display:block;vertical-align:middle}video{max-width:100%;height:auto}.bg-white{--bg-opacity:1;background-color:#fff;background-color:rgba(255,255,255,var(--bg-opacity))}.bg-gray-100{--bg-opacity:1;background-color:#f7fafc;background-color:rgba(247,250,252,var(--bg-opacity))}.border-gray-200{--border-opacity:1;border-color:#edf2f7;border-color:rgba(237,242,247,var(--border-opacity))}.border-t{border-top-width:1px}.flex{display:flex}.grid{display:grid}.hidden{display:none}.items-center{align-items:center}.justify-center{justify-content:center}.font-semibold{font-weight:600}.h-5{height:1.25rem}.h-8{height:2rem}.h-16{height:4rem}.text-sm{font-size:.875rem}.text-lg{font-size:1.125rem}.leading-7{line-height:1.75rem}.mx-auto{margin-left:auto;margin-right:auto}.ml-1{margin-left:.25rem}.mt-2{margin-top:.5rem}.mr-2{margin-right:.5rem}.ml-2{margin-left:.5rem}.mt-4{margin-top:1rem}.ml-4{margin-left:1rem}.mt-8{margin-top:2rem}.ml-12{margin-left:3rem}.-mt-px{margin-top:-1px}.max-w-6xl{max-width:72rem}.min-h-screen{min-height:100vh}.overflow-hidden{overflow:hidden}.p-6{padding:1.5rem}.py-4{padding-top:1rem;padding-bottom:1rem}.px-6{padding-left:1.5rem;padding-right:1.5rem}.pt-8{padding-top:2rem}.fixed{position:fixed}.relative{position:relative}.top-0{top:0}.right-0{right:0}.shadow{box-shadow:0 1px 3px 0 rgba(0,0,0,.1),0 1px 2px 0 rgba(0,0,0,.06)}.text-center{text-align:center}.text-gray-200{--text-opacity:1;color:#edf2f7;color:rgba(237,242,247,var(--text-opacity))}.text-gray-300{--text-opacity:1;color:#e2e8f0;color:rgba(226,232,240,var(--text-opacity))}.text-gray-400{--text-opacity:1;color:#cbd5e0;color:rgba(203,213,224,var(--text-opacity))}.text-gray-500{--text-opacity:1;color:#a0aec0;color:rgba(160,174,192,var(--text-opacity))}.text-gray-600{--text-opacity:1;color:#718096;color:rgba(113,128,150,var(--text-opacity))}.text-gray-700{--text-opacity:1;color:#4a5568;color:rgba(74,85,104,var(--text-opacity))}.text-gray-900{--text-opacity:1;color:#1a202c;color:rgba(26,32,44,var(--text-opacity))}.underline{text-decoration:underline}.antialiased{-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}.w-5{width:1.25rem}.w-8{width:2rem}.w-auto{width:auto}.grid-cols-1{grid-template-columns:repeat(1,minmax(0,1fr))}@media (min-width:640px){.sm\:rounded-lg{border-radius:.5rem}.sm\:block{display:block}.sm\:items-center{align-items:center}.sm\:justify-start{justify-content:flex-start}.sm\:justify-between{justify-content:space-between}.sm\:h-20{height:5rem}.sm\:ml-0{margin-left:0}.sm\:px-6{padding-left:1.5rem;padding-right:1.5rem}.sm\:pt-0{padding-top:0}.sm\:text-left{text-align:left}.sm\:text-right{text-align:right}}@media (min-width:768px){.md\:border-t-0{border-top-width:0}.md\:border-l{border-left-width:1px}.md\:grid-cols-2{grid-template-columns:repeat(2,minmax(0,1fr))}}@media (min-width:1024px){.lg\:px-8{padding-left:2rem;padding-right:2rem}}@media (prefers-color-scheme:dark){.dark\:bg-gray-800{--bg-opacity:1;background-color:#2d3748;background-color:rgba(45,55,72,var(--bg-opacity))}.dark\:bg-gray-900{--bg-opacity:1;background-color:#1a202c;background-color:rgba(26,32,44,var(--bg-opacity))}.dark\:border-gray-700{--border-opacity:1;border-color:#4a5568;border-color:rgba(74,85,104,var(--border-opacity))}.dark\:text-white{--text-opacity:1;color:#fff;color:rgba(255,255,255,var(--text-opacity))}.dark\:text-gray-400{--text-opacity:1;color:#cbd5e0;color:rgba(203,213,224,var(--text-opacity))}}
        </style>
    </div>
@endsection
