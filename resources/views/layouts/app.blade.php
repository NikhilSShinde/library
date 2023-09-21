<!DOCTYPE html>
<html>
    
    <head> 
           
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @yield('meta')
        <link rel="shortcut icon" type="image/x-icon" href="{{url('public/media/front/images/favicon.ico')}}" />
        <link href='https://fonts.googleapis.com/css?family=Titillium+Web:400,200,300,700,600' rel='stylesheet' type='text/css'> 
        <script type="text/javascript"  src="{{url('public/media/front/js/jquery-v2.1.3.js')}}"></script>
        <script type="text/javascript"  src="{{url('public/media/front/js/jquery.validate.js')}}"></script> 
        <link href="{{url('public/media/front/css/bootstrap.min.css')}}" rel="stylesheet"/>
        <link rel="stylesheet" href="{{url('public/media/front/css/font-awesome.min.css')}}" />
        <link rel="stylesheet" href="{{url('public/media/front/css/owl.carousel.css')}}" />
        <link rel="stylesheet" href="{{url('public/media/front/css/animated.css')}}" />
        <link rel="stylesheet" href="{{url('public/media/front/css/owl.theme.css')}}" />
        <link rel="stylesheet" href="{{url('public/media/front/css/main.css')}}" />
        <link rel="stylesheet" href="{{url('public/media/front/css/responsive.css')}}" />
        <link rel="stylesheet" href="{{url('public/media/front/css/font-icon.css')}}"/>
        
<!--       @if(App::getLocale()=='ar')
        <link rel="stylesheet" href="{{url('public/media/front/css/arabic.css')}}" />
        <link rel="stylesheet" href="{{url('public/media/front/css/responsivearabic.css')}}" />
      @else  -->
         <link rel="stylesheet" href="{{url('public/media/front/css/main.css')}}" />
         <link rel="stylesheet" href="{{url('public/media/front/css/responsive.css')}}" />
      <!--@endif-->   
        <script>
            var javascript_site_path = '{{url('')}}/';
        </script>
        <noscript>
        
        </noscript>
            
    </head>
    <body>
    <header>
	<nav class="custom-nav">
    	<div class="nav-outer clearfix">
        	<div class="logo"><a href="javascript:void(0)" data-scroll-nav="1"><img src="{{url('public/media/front/images/logo.png')}}" alt="BAGGI LOGO"/></a></div>
            <div class="nav-navigations">
            	<ul>
                	<li><a href="javascript:void(0)" data-scroll-nav="1">Home</a></li>
                    <li><a href="javascript:void(0)" data-scroll-nav="2">About us</a></li>
                    <li><a href="javascript:void(0)" data-scroll-nav="3">Features</a></li>
                    <li><a href="javascript:void(0)" data-scroll-nav="4">Screenshots</a></li>
                    <li><a href="javascript:void(0)" data-scroll-nav="5">Faq's</a></li>
                    <li><a href="javascript:void(0)" data-scroll-nav="6">Contact</a></li>
                </ul>
            </div>
        </div>
    </nav>
</header>
        @yield('content')
        <!-- Modal -->
      <footer>
	<div class="container">
    	<div class="row">
        	<div class="col-sm-8">
            	<p class="copyright-cont">&copy; {{date('Y')}} <a href="javascript:void(0)">{{GlobalValues::get('site-title')}}</a> All rights reserved.</p>
            </div>
            <div class="col-sm-4">
            	<div class="social-icons">
                    <ul>
                        <li><a href="{{GlobalValues::get('facebook-link')}}" class="fb-icon"><i class="fa fa-facebook"></i></a></li>
                        <li><a href="{{GlobalValues::get('twitter-link')}}" class="ld-icon"><i class="fa fa-twitter"></i></a></li>
                        <li><a href="{{GlobalValues::get('google-link')}}" class="pt-icon"><i class="fa fa-google"></i></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer>

      
        <!--<script src="{{url('public/media/front/js/jquery.min.js')}}" type="text/javascript"></script>--> 
        <script src="{{url('public/media/front/js/bootstrap.min.js')}}" type="text/javascript"></script> 
        
        <script src="{{url('public/media/front/js/owl.carousel.min.js')}}" type="text/javascript"></script> 
        <script src="{{url('public/media/front/js/wow.min.js')}}" type="text/javascript"></script> 
        <script src="{{url('public/media/front/js/scrollIt.min.js')}}" type="text/javascript"></script> 
        <script src="{{url('public/media/front/js/skrollr.min.js')}}" type="text/javascript"></script> 
        <script src="{{url('public/media/front/js/device.min.js')}}" type="text/javascript"></script> 
      
         <script src="{{url('public/media/front/js/validation.js')}}" type="text/javascript"></script> 
        
        <script src="{{url('public/media/front/js/custom.js')}}" type="text/javascript"></script> 
         
<!--      <script type="text/javascript">
          function showCmsContent(div_id)
          {
              $("#about-us").hide();
              $("#terms-of-use").hide();
              $("#driver-required").hide();
              $("#"+div_id).show();
          }
		$(document).ready(function() {
			$('#fullHHt').fullpage({
				anchors: ['home', 'about-app', 'amazing-features', 'contact', 'odt'],
				sectionsColor: ['', '', '', ''],
				slidesNavigation: true,
			});
                          $("div.bhoechie-tab-menu>div.list-group>a").click(function(e) {
                        e.preventDefault();
                        $(this).siblings('a.active').removeClass("active");
                        $(this).addClass("active");
                        var index = $(this).index();
                        $("div.bhoechie-tab>div.bhoechie-tab-content").removeClass("active");
                        $("div.bhoechie-tab>div.bhoechie-tab-content").eq(index).addClass("active");
                    });
		});
    </script>-->
 
</body>
</html>
