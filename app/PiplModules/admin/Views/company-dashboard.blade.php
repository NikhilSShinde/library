@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Company Dashboard</title>

@endsection
@section('content')
<script src="http://maps.google.com/maps/api/js?v=3&key=AIzaSyD58tVuK_zOf_mw-VWm-rLRWPK5RgGucco" type="text/javascript"></script>
<script src="http://jawj.github.io/OverlappingMarkerSpiderfier/bin/oms.min.js" type="text/javascript"></script>


<script type="text/javascript" src="{{url('public/media/backend/js/amchart.js')}}"></script>
<script type="text/javascript" src="{{url('public/media/backend/js/funnelchart.js')}}"></script>
<script src="https://www.amcharts.com/lib/3/serial.js"></script>
<script src="https://www.amcharts.com/lib/3/plugins/export/export.min.js"></script>
<link rel="stylesheet" href="https://www.amcharts.com/lib/3/plugins/export/export.css" type="text/css" media="all" />
<script src="https://www.amcharts.com/lib/3/themes/light.js"></script>


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
                            <h3 class="font-purple-soft">{{isset($agent_users_count)?$agent_users_count:'0'}}</h3>
                            <small>Agent Users</small>
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
                                <a href="{{url('/admin/agent-users')}}"> See More </a>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                <div class="dashboard-stat2">
                    <div class="display">
                        <div class="number">
                            <h3 class="font-purple-soft">{{isset($star_users_count)?$star_users_count:'0'}}</h3>
                            <small>Star Users</small>
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
                                <a href="{{url('/admin/star-users')}}"> See More </a>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                <div class="dashboard-stat2">
                    <div class="display">
                        <div class="number">
                            <h3 class="font-purple-soft">{{isset($mate_users_count)?$mate_users_count:'0'}}</h3>
                            <small>Customers</small>
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
                                <a href="{{url('/admin/manage-users')}}"> See More </a>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                <div class="dashboard-stat2">
                    <div class="display">
                        <div class="number">
                            <h3 class="font-purple-soft">{{isset($company_users_count)?$company_users_count:'0'}}</h3>
                            <small>Company Users</small>
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
                                <a href="{{url('/admin/company-users')}}"> See More </a>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>

<!--        <div class="row margin-top-10">
            <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                <div class="dashboard-stat3">
                    <div class="display">
                        <div class="number">
                            <h3 class="font-purple-soft">{{isset($pending_order_count)?$pending_order_count:'0'}}</h3>
                            <small>Pending Orders</small>
                        </div>
                        <div class="icon">
                            <i class="icon-user"></i>
                        </div>
                    </div>
                    <div class="progress-info">
                        <div class="progress">
                            <span  class="progress-bar progress-bar-success purple-soft">

                            </span>
                        </div>
                        <div class="status">
                            <div class="status-title">

                                <a href="{{url('/admin/order-list')}}"> See More </a>
                            </div>

                        </div>
                    </div>

                </div>

            </div>

            <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                <div class="dashboard-stat4">
                    <div class="display">
                        <div class="number">
                            <h3 class="font-purple-soft">{{isset($active_order_count)?$active_order_count:'0'}}</h3>
                            <small>Active Orders</small>
                        </div>
                        <div class="icon">
                            <i class="icon-user"></i>
                        </div>
                    </div>
                    <div class="progress-info">
                        <div class="progress">
                            <span  class="progress-bar progress-bar-success purple-soft">

                            </span>
                        </div>
                        <div class="status">
                            <div class="status-title">

                                <a href="{{url('/admin/order-list')}}"> See More </a>
                            </div>

                        </div>
                    </div>

                </div>

            </div>
            
              <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                <div class="dashboard-stat4">
                    <div class="display">
                        <div class="number">
                            <h3 class="font-purple-soft">{{isset($completed_order_count)?$completed_order_count:'0'}}</h3>
                            <small>Completed Orders</small>
                        </div>
                        <div class="icon">
                            <i class="icon-user"></i>
                        </div>
                    </div>
                    <div class="progress-info">
                        <div class="progress">
                            <span  class="progress-bar progress-bar-success purple-soft">

                            </span>
                        </div>
                        <div class="status">
                            <div class="status-title">

                                <a href="{{url('/admin/order-list')}}"> See More </a>
                            </div>

                        </div>
                    </div>

                </div>

            </div>

            <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                <div class="dashboard-stat3">
                    <div class="display">
                        <div class="number">
                            <h3 class="font-purple-soft">{{isset($all_supportTicket_count)?$all_supportTicket_count:'0'}}</h3>
                            <small>Open Support Tickets</small>
                        </div>
                        <div class="icon">
                            <i class="icon-user"></i>
                        </div>
                    </div>
                    <div class="progress-info">
                        <div class="progress">
                            <span  class="progress-bar progress-bar-success purple-soft">

                            </span>
                        </div>
                        <div class="status">
                            <div class="status-title">

                                <a href="{{url('/admin/support-tickets')}}"> See More </a>
                            </div>

                        </div>
                    </div>

                </div>

            </div>
        </div>    -->
   
    </div>

</div>
</div>

@endsection