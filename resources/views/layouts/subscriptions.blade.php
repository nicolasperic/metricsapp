<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Assembla Metrics App" />
    <meta name="author" content="Nicolás Peric" />
    <link rel="shortcut icon" href={{ asset("favicon.ico") }} />

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        window.Laravel = <?php echo json_encode([
            'csrfToken' => csrf_token(),
        ]); ?>
    </script>
    <!-- Custom scripts for all pages-->
    <title>{{ config('app.name', 'Laravel') }}</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <script src="{{ asset('js/app.js') }}" defer></script>



    <!-- Custom fonts for this template-->
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    @yield('head')
</head>

<body id="page-top">

<!-- Page Wrapper -->
<div id="wrapper">

    <!-- Sidebar -->

    <!-- End of Sidebar -->

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">

        <!-- Main Content -->
        <div id="content">


            <!-- Begin Page Content -->
            <div class="container-fluid @yield('container-class','')">


                @if(Session::has('message'))
                    <p class="alert {{ Session::get('alert-class', 'alert-info') }}">{!! Session::get('message') !!}</p>
                @endif




                    <!-- Content loaded from partials -->
                    @yield('content')

            </div>
            <!-- /.container-fluid -->

        </div>
        <!-- End of Main Content -->

        <!-- Footer -->
        <footer class="sticky-footer bg-white">
            <div class="container my-auto">
                <div class="copyright text-center my-auto">
                    <span>Copyright &copy; Nicolás Peric 2019</span>
                </div>
            </div>
        </footer>
        <!-- End of Footer -->

    </div>
    <!-- End of Content Wrapper -->

</div>
<!-- End of Page Wrapper -->

<!-- Scroll to Top Button-->
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<!-- Logout Modal-->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                <!--a class="btn btn-primary" href="login.html">Logout</a-->
                <a class="btn btn-primary" href="{{ route('logout') }}"
                   onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i> {{ __('Logout') }}
                </a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csr
                </form>

            </div>
        </div>
    </div>
</div>

<!-- Page level plugins -->
<script src="{{ asset('vendor/chart.js/Chart.min.js') }}"></script>
<!-- Page level custom scripts -->
<script src="{{ asset('js/demo/chart-area-demo.js') }}"></script>
<script src="{{ asset('js/demo/chart-pie-demo.js') }}"></script>
<script src="{{ asset('js/demo/chart-bar-demo.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#mark-notifications-as-read').click(function(){
            event.preventDefault();
            $.post('/notifications/markNotificationsAsRead', {"_token": "{{ csrf_token() }}"}, function(response){
                if (response && response.result == 'success') {
                    $('#user-notifications').html('<a class=\"dropdown-item d-flex align-items-center\"><div><div class=\"small text-gray-500\">No notifications</div></div></a>');
                    $('#notifications-counter').html('');
                    $('#notifications-counter').removeClass('badge-danger');
                    $('#mark-notifications-as-read').hide();
                }
            });

        });
    });

</script>
</body>

</html>
