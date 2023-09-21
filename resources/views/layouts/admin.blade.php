<!DOCTYPE>
<html lang="en" class="no-js">
<head>
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
<meta charset="utf-8"/>

<meta http-equiv="X-UA-Compatible" content="IE=edge">
<!--<meta content="width=device-width, initial-scale=1" name="viewport"/>-->
<meta content="Admin Dashboard" name="description"/>

<meta content="{{ csrf_token() }}" name="_token">
<meta name="viewport" content="width=device-width, user-scalable=no">

@yield("meta")

<link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&amp;subset=all" rel="stylesheet" type="text/css"/>
<link href="{{url('public/media/backend/css/font-awesome.min.css')}}" rel="stylesheet" type="text/css"/>
<link href="{{url('public/media/backend/css/simple-line-icons.min.css')}}" rel="stylesheet" type="text/css"/>
<link href="{{url('public/media/backend/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css"/>
<link href="{{url('public/media/backend/css/uniform.default.css')}}" rel="stylesheet" type="text/css"/>
<link href="{{url('public/media/backend/css/bootstrap-switch.min.css')}}" rel="stylesheet" type="text/css"/>
<link href="{{url('public/media/backend/css/tasks.css')}}" rel="stylesheet" type="text/css"/>
<link href="{{url('public/media/backend/css/components-rounded.css')}}" id="style_components" rel="stylesheet" type="text/css"/>
<link href="{{url('public/media/backend/css/layout4/layout.css')}}" rel="stylesheet" type="text/css"/>
<link href="{{url('public/media/backend/css/layout4/themes/light.css')}}" rel="stylesheet" type="text/css" id="style_color"/>
<link href="{{url('public/media/backend/css/layout4/custom.css')}}" rel="stylesheet" type="text/css"/>
<link href="{{url('public/media/backend/css/plugins.css')}}" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="{{url('public/media/backend/css/datatable/select2.css')}}"/>
<link rel="stylesheet" type="text/css" href="{{url('public/media/backend/css/datatable/dataTables.bootstrap.css')}}"/>
<!--<script src="{{url('public/media/backend/js/jquery.min.js')}}" type="text/javascript"></script>-->
<script src="{{url('public/media/backend/js/jquery-v2.1.3.js')}}" type="text/javascript"></script>
<!-- END THEME STYLES -->
<link rel="shortcut icon" href="favicon.ico"/>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<!-- Include whatever JQuery which you are using -->
<!-- Other js and css scripts -->
</head>

<body class="page-header-fixed page-sidebar-closed-hide-logo page-sidebar-closed-hide-logo">
<!-- BEGIN HEADER -->
<div class="page-header navbar navbar-fixed-top">
	<!-- BEGIN HEADER INNER -->
	<div class="page-header-inner">
		<!-- BEGIN LOGO -->
		<div class="page-logo">
			<a href="{{url('/admin/dashboard')}}">
			 <img src="{{asset('storageasset/global-settings/'.GlobalValues::get('site-logo'))}}" alt="LOGO" class="logo-default"/>
			</a>
			<div class="menu-toggler sidebar-toggler">
				<!-- DOC: Remove the above "hide" to enable the sidebar toggler button on header -->
			</div>
		</div>
		<!-- END LOGO -->
		<!-- BEGIN RESPONSIVE MENU TOGGLER -->
		<a href="javascript:;" class="menu-toggler responsive-toggler" data-toggle="collapse" data-target=".navbar-collapse">
		</a>
		<!-- END RESPONSIVE MENU TOGGLER -->
		
		<!-- BEGIN PAGE TOP -->
		<div class="page-top">
			
			<!-- END HEADER SEARCH BOX -->
			<!-- BEGIN TOP NAVIGATION MENU -->
			<div class="top-menu">
                            
				<ul class="nav navbar-nav">
					<li class="separator hide">
					</li>
					
					<!-- END TODO DROPDOWN -->
					<!-- BEGIN USER LOGIN DROPDOWN -->
					<!-- DOC: Apply "dropdown-dark" class after below "dropdown-extended" to change the dropdown styte -->
					<li class="dropdown dropdown-user dropdown-dark">
                                             @if (Auth::check())
                                              <a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
						<span class="username username-hide-on-mobile">
                                                      Welcome  {{ Auth::user()->userInformation->first_name }} 
                                                </span>
                                                  
                                                <img alt="" class="img-circle" src="{{url('public/media/backend/images/user-admin-default.png')}}"/>
                                              </a>  
                                                <ul class="dropdown-menu dropdown-menu-default">
                                                    @if(Auth::User()->userInformation->user_type=='1')	
							<li>
								<a href="{{url('admin/profile')}}">
								<i class="icon-user"></i> My Profile </a>
							</li>
                                                    @endif 
							<li>
								<a href="{{url('admin/logout')}}">
								<i class="icon-key"></i> Log Out </a>
							</li>
						</ul>
                                             </a>
                                            @endif
						
						
						
					</li>
					<!-- END USER LOGIN DROPDOWN -->
				</ul>
			</div>
			<!-- END TOP NAVIGATION MENU -->
		</div>
		<!-- END PAGE TOP -->
	</div>
	<!-- END HEADER INNER -->
</div>
<!-- END HEADER -->
<div class="clearfix">
</div>

<div class="page-container">
	
    @include(config("piplmodules.back-left-view-layout-location"))
    @yield("content")

</div>


<div class="page-footer">
	<div class="page-footer-inner">
		 <?php echo date('Y')?> &copy; {{GlobalValues::get('site-title')}}
	</div>
	<div class="scroll-to-top">
		<i class="icon-arrow-up"></i>
	</div>
</div>


<script src="{{url('public/media/backend/js/bootstrap.min.js')}}" type="text/javascript"></script>
<script src="{{url('public/media/backend/js/bootstrap-hover-dropdown.min.js')}}" type="text/javascript"></script>
<script src="{{url('public/media/backend/js/jquery.slimscroll.min.js')}}" type="text/javascript"></script>
<script src="{{url('public/media/backend/js/jquery.blockui.min.js')}}" type="text/javascript"></script>
<script src="{{url('public/media/backend/js/jquery.cokie.min.js')}}" type="text/javascript"></script>
<script src="{{url('public/media/backend/js/jquery.uniform.min.js')}}" type="text/javascript"></script>
<script src="{{url('public/media/backend/js/bootstrap-switch.min.js')}}" type="text/javascript"></script>
<!-- END CORE PLUGINS -->

<script src="{{url('public/media/backend/js/metronic.js')}}" type="text/javascript"></script>
<script src="{{url('public/media/backend/js/layout4/scripts/layout.js')}}" type="text/javascript"></script>
<!--<script src="../../assets/admin/layout4/scripts/demo.js" type="text/javascript"></script>-->
<script src="{{url('public/media/backend/js/tasks.js')}}" type="text/javascript"></script>
<!-- END PAGE LEVEL SCRIPTS -->

<script type="text/javascript" src="{{url('public/media/backend/js/datatable/select2.min.js')}}"></script>
<script type="text/javascript" src="{{url('public/media/backend/js/datatable/jquery.dataTables.min.js')}}"></script>
<script type="text/javascript" src="{{url('public/media/backend/js/datatable/dataTables.bootstrap.js')}}"></script>
<script type="text/javascript" src="{{url('public/media/backend/js/datatable/table-managed.js')}}"></script>
<script type="text/javascript" src="{{url('public/media/backend/js/jquery.validate.js')}}"></script>
<script type="text/javascript" src="{{url('public/media/backend/js/validation.js')}}"></script>
<script type="text/javascript" src="{{url('public/media/backend/js/select-all-delete.js')}}"></script>

<script>
jQuery(document).ready(function() {    
   Metronic.init(); // init metronic core componets
   Layout.init(); // init layout
   Tasks.initDashboardWidget(); // init tash dashboard widget  
   //TableManaged.init();
});
</script>
<!-- END JAVASCRIPTS -->
</body>
</html>