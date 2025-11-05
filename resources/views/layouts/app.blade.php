<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Asset Upload Tool') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <!-- Start - Assets for Upload Tool -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Optional: Make it fit Bootstrap's form style */
        .select2-container .select2-selection--single {
            height: 38px;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
        }

        #execute-loading-container {
            transition: opacity 0.3s ease-in-out;
        }

        /* Highlight locked mapping rows */
        tr:has(select[disabled]) {
            background-color: #f8f9fa;
        }

        /* Make "Auto-mapped" Rows Visually Distinct */
        tr.table-light {
            background-color: #f8f9fa !important;
        }

        tr.table-light td {
            opacity: 0.9;
        }

        .table-light small.text-muted {
            display: block;
            font-size: 0.8rem;
        }

        .nav-link {
            transition: all 0.2s ease-in-out;
        }
        
        .nav-link.active {
            font-weight: 600;
            color: #0d6efd !important; /* Bootstrap primary color */
            border-bottom: 2px solid #0d6efd;
            background-color: rgba(13, 110, 253, 0.05);
            border-radius: 4px;
        }

        #bimUploadForm {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
        }

        #check-all-bim {
            cursor: pointer;
        }

        #scroll-buttons {
            position: fixed;
            right: 25px;
            bottom: 40px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            z-index: 1050;
        }

        #scroll-buttons button {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: none; /* Initially hidden */
            transition: opacity 0.3s ease;
        }

        #scroll-buttons button.show {
            display: inline-block;
            opacity: 1;
        }

        #scroll-buttons button:hover {
            opacity: 0.85;
        }

    </style>
    <!-- End - Assets for Upload Tool -->
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                @auth
                <a class="nav-link {{ request()->is('dashboard*') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <!-- {{ config('app.name', 'Laravel') }} -->
                    Home
                </a>
                @endauth
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                        @auth
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('bimupload*') ? 'active' : '' }}" href="{{ route('bimupload.index') }}">
                                    Upload i.BIM
                                </a>
                            </li>
                            <li class="nav-item">
                                <!-- request()->is('uploadtool*') - Matches any URL starting with /uploadtool e.g. /uploadtool or /uploadtool/execute -->
                                <a class="nav-link {{ request()->is('uploadtool*') ? 'active' : '' }}" href="{{ route('uploadtool') }}">
                                    Upload Tool
                                </a>
                            </li>
                        @endauth
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Unauthenticated Links -->
                        @guest
                            <!-- @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif -->
                            @if (Route::has('register'))
                                <!-- <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li> -->
                            @endif
                        @else
                            <!-- Authentication Links -->
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>

    <!-- Floating Scroll Buttons -->
    <div id="scroll-buttons">
        <button id="goTopBtn" class="btn btn-primary shadow-sm rounded-circle" data-bs-toggle="tooltip" data-bs-placement="left" title="Go to Top">
            <i class="bi bi-arrow-up"></i>
        </button>
        <button id="goBottomBtn" class="btn btn-secondary shadow-sm rounded-circle" data-bs-toggle="tooltip" data-bs-placement="left" title="Go to Bottom">
            <i class="bi bi-arrow-down"></i>
        </button>
    </div>

    <!-- This ensures the <script> inside @push('scripts') in your Blade is actually loaded. -->
    @stack('scripts')

    <script>
        $(document).ready(function() {
            const goTopBtn = $('#goTopBtn');
            const goBottomBtn = $('#goBottomBtn');

            // Show/hide buttons on scroll
            $(window).on('scroll', function() {
                if ($(this).scrollTop() > 300) {
                    goTopBtn.addClass('show');
                    goBottomBtn.addClass('show');
                } else {
                    goTopBtn.removeClass('show');
                    goBottomBtn.removeClass('show');
                }
            });

            // Scroll to top smoothly
            goTopBtn.on('click', function() {
                $('html, body').animate({ scrollTop: 0 }, 'slow');
            });

            // Scroll to bottom smoothly
            goBottomBtn.on('click', function() {
                $('html, body').animate({ scrollTop: $(document).height() }, 'slow');
            });
        });
    </script>
</body>
</html>
