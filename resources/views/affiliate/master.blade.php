<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">
        <meta name="_token" content="{{ csrf_token() }}" />
        <link rel="icon" href="{{ URL::asset('images/favicon.ico') }}">
        <title>Welcome!</title>
        <link href="{{ asset('bower_components/bootstrap/dist/css/bootstrap.min.css') }}" rel="stylesheet">
        <link href="{{ asset('bower_components/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet" type="text/css">
        <link href="{{ asset('css/affiliate/style.min.css') }}" rel="stylesheet">
        @yield('header')
    </head>

    <body>
        <nav class="navbar navbar-default navbar-fixed-top">

            <div class="container">
                <div class="navbar-header">

                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-menu">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="glyphicon glyphicon-user"></span>
                    </button>

                    <button type="button" class="navbar-toggle pull-left special_hide" data-toggle="collapse" data-target="#navbar-submenu">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>

                    <a class="engageiq-logo" href="{{ url() }}">
                        <img class="logo" src="{{ URL::asset('images/logos/engageiq-logo.png') }}" alt="EngageIQ Icon" title="EngageIQ">
                    </a>

                </div>

                <?php
                    $today = Bus::dispatch(new AffiliateEarningsByDateFilter(auth()->user()->affiliate_id,1));
                    $month = Bus::dispatch(new AffiliateEarningsByDateFilter(auth()->user()->affiliate_id,4));
                    $year = Bus::dispatch(new AffiliateEarningsByDateFilter(auth()->user()->affiliate_id,5));
                ?>

                <div id="navbar" class="navbar-collapse collapse">
                    <ul class="nav navbar-nav right-navbar-menu">
                        <li class="special_hide"><a style="background-color: #0c4674;" href="#">TODAY ${{ $today }}</a></li>
                        <li class="special_hide"><a style="background-color: #2c72a6;" href="#">MTD ${{ $month }}</a></li>
                        <li class="special_hide"><a style="background-color: #445d71;" href="#">YTD ${{ $year }}</a></li>
                        <li><a style="background-color: #063153;" href="{{ url('auth/logout') }}"><i class="fa fa-sign-out fa-lg"></i> LOGOUT</a></li>
                    </ul>
                </div><!--/.nav-collapse -->

                <!-- MOBILE -->
                <div class="navbar-collapse collapse navbar-menu-mobile" id="navbar-menu">
                    <ul class="nav navbar-nav navbar-nav-menu-mobile">
                        <li class="special_hide"><a style="background-color: #0c4674;" href="#">TODAY ${{ $today }}</a></li>
                        <li class="special_hide"><a style="background-color: #2c72a6;" href="#">MTD ${{ $month }}</a></li>
                        <li class="special_hide"><a style="background-color: #445d71;" href="#">YTD ${{ $year }}</a></li>
                        <li><a style="background-color: #063153;" href="{{ url('auth/logout') }}"><i class="fa fa-sign-out fa-lg"></i> LOGOUT</a></li>
                    </ul>
                </div>
            </div>

            <div class="navbar-collapse collapse" id="navbar-submenu" style="background-color: #063153;">
                <div class="container">
                    <ul class="nav navbar-nav navbar-nav-dashboard">
                        <li class="@yield('statistics-active') special_hide"><a href="{{ url('affiliate/statistics') }}">STATISTICS</a></li>
                        <li class="@yield('campaigns-active') special_hide"><a href="{{ url('affiliate/campaigns') }}">CAMPAIGNS</a></li>
                         <li class="@yield('websites-active') special_hide"><a href="{{ url('affiliate/websites') }}">MY WEBSITES</a></li>
                        <li class="@yield('accounts-active') special_hide"><a href="{{ url('affiliate/account') }}">ACCOUNT INFO</a></li>
                        <li class="special_hide affiliate_info">
                            <a href="{{ url('affiliate/edit_account') }}">
                               {{ session('affiliate_name', 
                                    auth()->user()->affiliate_id . ' - '. auth()->user()->affiliate->company)}}
                            </a>
                            
                        </li>
                    </ul>
                </div>
            </div>
            <!-- END MOBILE -->
        </nav>

        <!-- Begin page content -->
        <div class="container">
            @yield('content')
        </div>

        <!--
        <footer class="footer">
            <div class="container">
                <p class="text-muted">Place sticky footer content here.</p>
            </div>
        </footer>
        -->

        <span id="baseUrl" hidden>{{ url() }}</span>

        <!-- jQuery -->
        <script src="{{ asset('bower_components/jquery/dist/jquery.min.js') }}"></script>

        <!-- Bootstrap Core JavaScript -->
        <script src="{{ asset('bower_components/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    
        @yield('footer')
    </body>
</html>
