<!DOCTYPE html>
<html lang="en">
<head> 
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    @yield('meta')

    <!-- Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css" integrity="sha384-XdYbMnZ/QjLh6iI4ogqCTaIjrFk87ip+ekIjefZch0Y+PvJ8CDYtEs1ipDmPorQ+" crossorigin="anonymous">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato:100,300,400,700">
    <link rel="stylesheet" href="{{url('public/media/front/css/style.css')}}">
    
    <script type="text/javascript"  src="{{url('public/media/front/js/jquery-v2.1.3.js')}}"></script>
   
      <!-- Styles -->
   <link href="{{url('public/media/backend/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css"/>
 
    <style>
        body {
            font-family: 'Lato';
        }

        .fa-btn {
            margin-right: 6px;
        }
    </style>
    <script>
    var javascript_site_path='{{url('')}}/';
   </script>            
</head>
<body id="app-layout">
    <nav class="navbar navbar-default navbar-static-top">
        <div class="container">
            <div class="navbar-header">

                <!-- Collapsed Hamburger -->
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                    <span class="sr-only">Toggle Navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>

                <!-- Branding Image -->
			@if(Auth::User()->userInformation->user_type=='1')	
                <a class="navbar-brand" href="{{ url('/admin/dashboard') }}">
                  {{GlobalValues::get('site-title')}}
                </a>
				@endif
			@if(Auth::User()->userInformation->user_type!='1')	
				  <a class="navbar-brand" href="{{ url('/') }}">
                 {{GlobalValues::get('site-title')}}
                </a>
			@endif
            </div>
        </div>
    </nav>

    @yield('content')

    <!-- JavaScripts -->
<script src="{{url('public/media/backend/js/jquery-v2.1.3.js')}}" type="text/javascript"></script>
    <script src="{{url('public/media/backend/js/bootstrap.min.js')}}" type="text/javascript"></script>
    {{-- <script src="{{ elixir('js/app.js') }}"></script> --}}


</body>
</html>
