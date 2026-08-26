<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta name="viewport"
          content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <meta name="description"
          content="USMS - University/School Management System">

    <meta name="author"
          content="USMS">

    <meta name="csrf-token"
          content="{{ csrf_token() }}">

    <link rel="icon"
          type="image/png"
          href="{{ asset('img/Talha.jpeg') }}">

    <title>
        USMS @yield('title')
    </title>


    {{-- =========================================================
         GOOGLE FONT
    ========================================================== --}}
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900"
        rel="stylesheet">


    {{-- =========================================================
         FONT AWESOME
    ========================================================== --}}
    <link
        href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}"
        rel="stylesheet"
        type="text/css">


    {{-- =========================================================
         BOOTSTRAP
         Load Bootstrap ONCE only
    ========================================================== --}}
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css"
        rel="stylesheet">


    {{-- =========================================================
         SB ADMIN 2
    ========================================================== --}}
    <link
        href="{{ asset('css/sb-admin-2.min.css') }}"
        rel="stylesheet">


    {{-- =========================================================
         TOASTR
    ========================================================== --}}
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css"
        integrity="sha512-vKMx8UnXk60zUwyUnUPM3HbQo8QfmNx7+ltw8Pm5zLusl1XIfwcxo8DbWCqMGKaWeNxWA8yrx5v3SaVpMvR3CA=="
        crossorigin="anonymous"
        referrerpolicy="no-referrer">


    {{-- =========================================================
         PAGE-SPECIFIC CSS
    ========================================================== --}}
    @stack('styles')

    @yield('styles')

</head>


<body id="page-top">

    {{-- =========================================================
         PAGE WRAPPER
    ========================================================== --}}
    <div id="wrapper">

        @includeIf('layouts.sidebar')

        <div id="content-wrapper"
             class="d-flex flex-column">

            <div id="content">

                @includeIf('layouts.topbar')

                <div class="container-fluid">

                    {{-- Flash Messages --}}
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show"
                             role="alert">

                            <i class="fas fa-check-circle mr-2"></i>

                            {{ session('success') }}

                            <button type="button"
                                    class="close"
                                    data-dismiss="alert"
                                    aria-label="Close">

                                <span aria-hidden="true">
                                    &times;
                                </span>

                            </button>

                        </div>
                    @endif


                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show"
                             role="alert">

                            <i class="fas fa-exclamation-circle mr-2"></i>

                            {{ session('error') }}

                            <button type="button"
                                    class="close"
                                    data-dismiss="alert"
                                    aria-label="Close">

                                <span aria-hidden="true">
                                    &times;
                                </span>

                            </button>

                        </div>
                    @endif


                    @if(session('warning'))
                        <div class="alert alert-warning alert-dismissible fade show"
                             role="alert">

                            <i class="fas fa-exclamation-triangle mr-2"></i>

                            {{ session('warning') }}

                            <button type="button"
                                    class="close"
                                    data-dismiss="alert"
                                    aria-label="Close">

                                <span aria-hidden="true">
                                    &times;
                                </span>

                            </button>

                        </div>
                    @endif


                    @if(session('info'))
                        <div class="alert alert-info alert-dismissible fade show"
                             role="alert">

                            <i class="fas fa-info-circle mr-2"></i>

                            {{ session('info') }}

                            <button type="button"
                                    class="close"
                                    data-dismiss="alert"
                                    aria-label="Close">

                                <span aria-hidden="true">
                                    &times;
                                </span>

                            </button>

                        </div>
                    @endif


                    {{-- =================================================
                         PAGE CONTENT
                    ================================================== --}}
                    @yield('content')

                </div>

            </div>


            {{-- =====================================================
                 FOOTER
            ====================================================== --}}
            @includeIf('layouts.footer')

        </div>

    </div>


    {{-- =========================================================
         SCROLL TO TOP
    ========================================================== --}}
    <a class="scroll-to-top rounded"
       href="#page-top">

        <i class="fas fa-angle-up"></i>

    </a>


    {{-- =========================================================
         LOGOUT MODAL
    ========================================================== --}}
    @includeIf('layouts.logout-modal')


    {{-- =========================================================
         JQUERY
         MUST COME BEFORE BOOTSTRAP JS
    ========================================================== --}}
    <script
        src="https://code.jquery.com/jquery-3.6.0.min.js">
    </script>


    {{-- =========================================================
         POPPER
         REQUIRED BY BOOTSTRAP 4
    ========================================================== --}}
    <script
        src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js">
    </script>


    {{-- =========================================================
         BOOTSTRAP JS
    ========================================================== --}}
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.min.js">
    </script>


    {{-- =========================================================
         SB ADMIN 2 JS
    ========================================================== --}}
    <script
        src="{{ asset('js/sb-admin-2.min.js') }}">
    </script>


    {{-- =========================================================
         TOASTR
    ========================================================== --}}
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js">
    </script>


    {{-- =========================================================
         SESSION MESSAGES
    ========================================================== --}}
    <script>

        document.addEventListener('DOMContentLoaded', function () {

            @if(session('success'))
                toastr.success(
                    @json(session('success'))
                );
            @endif

            @if(session('error'))
                toastr.error(
                    @json(session('error'))
                );
            @endif

            @if(session('warning'))
                toastr.warning(
                    @json(session('warning'))
                );
            @endif

            @if(session('info'))
                toastr.info(
                    @json(session('info'))
                );
            @endif

        });

    </script>


    {{-- =========================================================
         PAGE-SPECIFIC JS
    ========================================================== --}}
    @stack('scripts')

    @yield('scripts')


</body>

</html>