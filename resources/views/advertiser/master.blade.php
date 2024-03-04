<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="{{ URL::asset('images/favicon.ico') }}">
    <title>Welcome!</title>
    <link href="{{ asset('bower_components/bootstrap/dist/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('bower_components/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('css/advertiser/style.min.css') }}" rel="stylesheet">
    @yield('header')
  </head>

  <body>
    <nav class="navbar navbar-default navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <ul class="nav navbar-nav navbar-top">
            <a class="engageiq-logo" href="#">
              <img class="logo" src="{{ URL::asset('images/logos/engageiq-logo.png') }}" alt="EngageIQ Icon" title="EngageIQ">
            </a>
          </ul>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <ul class="nav navbar-nav right-navbar-menu">
            <li><a style="background-color: #0c4674;" href="#">TODAY $2.00</a></li>
            <li><a style="background-color: #2c72a6;" href="#">MTD $2.00</a></li>
            <li><a style="background-color: #445d71;" href="#">YTD $2.00</a></li>
            <li><a style="background-color: #4e4f51;" href="#"><i class="fa fa-link fa-lg"></i> LINK OUT</a></li>
            <li><a style="background-color: #063153;" href="{{ url('auth/logout') }}"><i class="fa fa-sign-out fa-lg"></i> LOGOUT</a></li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>

    <!-- Begin page content -->
    <div class="container">
      @yield('content')
    </div>

    <footer class="footer">
      <div class="container">
        <p class="text-muted">Place sticky footer content here.</p>
      </div>
    </footer>

    <!-- jQuery -->
    <script src="{{ asset('bower_components/jquery/dist/jquery.min.js') }}"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="{{ asset('bower_components/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    
    @yield('footer')
  </body>
</html>
