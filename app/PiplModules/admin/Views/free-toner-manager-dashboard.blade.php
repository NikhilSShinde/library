@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Free Toner Dashboard</title>

@endsection

@section('content')
<div class="page-content-wrapper">
		<div class="page-content">
			
			<!-- BEGIN PAGE BREADCRUMB -->
			<ul class="page-breadcrumb breadcrumb hide">
				<li>
					<a href="javascript:void(0);">Home</a><i class="fa fa-circle"></i>
				</li>
				<li class="active">
					 Dashboard
				</li>
			</ul>
			<!-- END PAGE BREADCRUMB -->
			<!-- BEGIN PAGE CONTENT INNER -->
			<div class="row margin-top-10">
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat2">
						<div class="display">
							<div class="number">
								<h3 class="font-purple-soft">{{$free_toner_star}}</h3>
								<small>STAR USERS</small>
							</div>
							<div class="icon">
								<i class="icon-user"></i>
							</div>
						</div>
                                                <div class="progress-info">
							<div class="progress">
								<span style="width: 100%;" class="progress-bar progress-bar-success purple-soft">
								
								</span>
							</div>
							<div class="status">
								<div class="status-title">
                                                                    <a href="{{url('/admin/star-users')}}"> Click Here to see more </a>
								</div>
								
							</div>
						</div>
						
					</div>
                                    
				</div>
                            <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat2">
						<div class="display">
							<div class="number">
								<h3 class="font-purple-soft">{{$vehicle_count}}</h3>
								<small>Vehicles</small>
							</div>
							<div class="icon">
								<i class="icon-user"></i>
							</div>
						</div>
                                            <div class="progress-info">
							<div class="progress">
								<span style="width: 100%;" class="progress-bar progress-bar-success purple-soft">
								
								</span>
							</div>
							<div class="status">
								<div class="status-title">
                                                                    <a href="{{url('/admin/vehicle-list')}}"> Click Here to see more </a>
								</div>
								
							</div>
						</div>
						
					</div>
				</div>
                           
			</div>
			
		</div>
	</div>
@endsection
