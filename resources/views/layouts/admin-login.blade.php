<html lang="en" class="no-js">
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
<head>
<meta charset="utf-8"/>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta content="width=device-width, initial-scale=1" name="viewport"/>
<meta content="Admin login" name="description"/>
<meta content="Somnath/Anuj" name="author"/>

@yield("meta")

<link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&amp;subset=all" rel="stylesheet" type="text/css"/>
<link href="{{url('public/media/backend/css/font-awesome.min.css')}}" rel="stylesheet" type="text/css"/>
<link href="{{url('public/media/backend/css/simple-line-icons.min.css')}}" rel="stylesheet" type="text/css"/>
<link href="{{url('public/media/backend/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css"/>
<link href="{{url('public/media/backend/css/uniform.default.css')}}" rel="stylesheet" type="text/css"/>
<link href="{{url('public/media/backend/css/bootstrap-switch.min.css')}}" rel="stylesheet" type="text/css"/>
<link href="{{url('public/media/backend/css/layout4/layout.css')}}" rel="stylesheet" type="text/css"/>
<link href="{{url('public/media/backend/css/layout4/themes/light.css')}}" rel="stylesheet" type="text/css" id="style_color"/>
<link href="{{url('public/media/backend/css/lock.css')}}" rel="stylesheet" type="text/css"/>


<!-- END THEME STYLES -->
<link rel="shortcut icon" href="favicon.ico"/>
</head>
<!-- END HEAD -->
<!-- BEGIN BODY -->
<!-- DOC: Apply "page-header-fixed-mobile" and "page-footer-fixed-mobile" class to body element to force fixed header or footer in mobile devices -->
<!-- DOC: Apply "page-sidebar-closed" class to the body and "page-sidebar-menu-closed" class to the sidebar menu element to hide the sidebar by default -->
<!-- DOC: Apply "page-sidebar-hide" class to the body to make the sidebar completely hidden on toggle -->
<!-- DOC: Apply "page-sidebar-closed-hide-logo" class to the body element to make the logo hidden on sidebar toggle -->
<!-- DOC: Apply "page-sidebar-hide" class to body element to completely hide the sidebar on sidebar toggle -->
<!-- DOC: Apply "page-sidebar-fixed" class to have fixed sidebar -->
<!-- DOC: Apply "page-footer-fixed" class to the body element to have fixed footer -->
<!-- DOC: Apply "page-sidebar-reversed" class to put the sidebar on the right side -->
<!-- DOC: Apply "page-full-width" class to the body element to have full width page without the sidebar menu -->

<body class="page-header-fixed page-sidebar-closed-hide-logo page-sidebar-closed-hide-logo">

</div>
<!-- END HEADER -->
<div class="clearfix">
</div>

<!--<div class="page-container">-->
    
    @yield("content")
<!-- BEGIN CONTENT -->
	
	<!-- END CONTENT -->
<!--</div>-->
<div class="page-footer">
	<div class="page-footer-inner">
		 <?php echo date('Y')?> &copy; {{GlobalValues::get('site-title')}}
	</div>
	<div class="scroll-to-top">
		<i class="icon-arrow-up"></i>
	</div>
</div>

<script src="{{url('public/media/backend/js/jquery-v2.1.3.js')}}" type="text/javascript"></script>

<script src="{{url('public/media/backend/js/bootstrap.min.js')}}" type="text/javascript"></script>
<script src="{{url('public/media/backend/js/jquery.validate.js')}}" type="text/javascript"></script>
<script src="{{url('public/media/backend/js/validation.js')}}" type="text/javascript"></script>
<script>
  $(document).ready(function() {
        fullSize();
        function fullSize() {
            var heights = window.innerHeight;
            jQuery(".fullHt").css('min-height', (heights + 0) + "px");
        }
});
</script>
</body>
</html>