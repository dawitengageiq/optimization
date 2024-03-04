<!DOCTYPE html>
<html>
<head>
    <title>Unauthorized!</title>

    <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">

    <!-- Bootstrap Core CSS -->
    <link href="{{ asset('bower_components/bootstrap/dist/css/bootstrap.min.css') }}" rel="stylesheet">

    <style>
        html, body {
            height: 100%;
        }

        body {
            margin: 0;
            padding: 0;
            width: 100%;
            color: #B0BEC5;
            display: table;
            font-weight: 100;
            font-family: 'Lato';
        }

        .container {
            text-align: center;
            display: table-cell;
            vertical-align: middle;
        }

        .content {
            text-align: center;
            display: inline-block;
        }

        .title {
            font-size: 72px;
            margin-bottom: 40px;
        }

        .error-container > .message {
            font-size: 30px;
            color: #000000;
        }

        .error-container > .row > .title{
            color: #F90101;
        }

        .error-container{
            margin: 0 auto;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="content error-container">
        <div class="row">
            <div class="row title">You are not authorized to access this page!</div>
        </div>
        <hr>
        <div class="row">
            <a href="{{ redirect()->back()->getTargetUrl() }}" class="btn btn-primary">Click here to go back to the previous page.</a>
        </div>
    </div>
</div>
</body>
</html>
