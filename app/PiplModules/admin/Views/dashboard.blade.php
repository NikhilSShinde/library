@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Admin Dashboard</title>

@endsection
@section('content')
<script src="http://maps.google.com/maps/api/js?v=3&key=AIzaSyD58tVuK_zOf_mw-VWm-rLRWPK5RgGucco&libraries=visualization" type="text/javascript"></script>
<script src="http://jawj.github.io/OverlappingMarkerSpiderfier/bin/oms.min.js" type="text/javascript"></script>


<script type="text/javascript" src="{{url('public/media/backend/js/amchart.js')}}"></script>
<script type="text/javascript" src="{{url('public/media/backend/js/funnelchart.js')}}"></script>
<script src="https://www.amcharts.com/lib/3/serial.js"></script>
<script src="https://www.amcharts.com/lib/3/plugins/export/export.min.js"></script>
<link rel="stylesheet" href="https://www.amcharts.com/lib/3/plugins/export/export.css" type="text/css" media="all" />
<script src="https://www.amcharts.com/lib/3/themes/light.js"></script>
<script>
    var userObj = {!!$countryData!!}
    var orderRevinue = {!!$countryRevinueData!!}
    var chart = AmCharts.makeChart("chartdiv", {
    "type": "funnel",
            "theme": "light",
            "dataProvider":userObj,
            "balloon": {
            "fixedPosition": true
            },
            "valueField": "value",
            "titleField": "title",
            "marginRight": 240,
            "marginLeft": 50,
            "startX": - 500,
            "rotate": true,
            "labelPosition": "right",
            "balloonText": "[[title]]: Trip placed in [[title]]- [[value]] ",
            "export": {
            "enabled": true
            }
    });</script>
<script>
var chart = AmCharts.makeChart("revenue_div", {
    "type": "serial",
    "theme": "light",
    "legend": {
        "autoMargins": true,
        "borderAlpha": 0.2,
        "equalWidths": false,
        "horizontalGap": 10,
        "markerSize": 6,
        "useGraphSettings": true,
        "valueAlign": "right",
        "valueWidth": 0
    },
    "dataProvider":orderRevinue,
    "valueAxes": [{
        "stackType": "100%",
        "axisAlpha": 0,
        "gridAlpha": 0,
        "labelsEnabled": false,
        "position": "left"
    }],
    "graphs": [{
        "balloonText": "[[title]], [[category]]<br><span style='font-size:14px'><b>[[value]]</b> ([[percents]]%)</span>",
        "fillAlphas": 0.9,
        "fontSize": 11,
        "labelText": "[[value]]",
        "lineAlpha": 0.5,
        "title": "Kuwait",
        "type": "column",
        "valueField": "Kuwait"
    }, {
        "balloonText": "[[title]], [[category]]<br><span style='font-size:14px'><b>[[value]]</b> ([[percents]]%)</span>",
        "fillAlphas": 0.9,
        "fontSize": 11,
        "labelText": "[[value]]",
        "lineAlpha": 0.5,
        "title": "United Arab Emirates",
        "type": "column",
        "valueField": "United Arab Emirates"
    }, {
        "balloonText": "[[title]], [[category]]<br><span style='font-size:14pxpadding-top:100px'><b>[[value]]</b> ([[percents]]%)</span>",
        "fillAlphas": 0.9,
        "fontSize": 11,
        "labelText": "[[value]]",
        "lineAlpha": 0.5,
        "title": "Qatar",
        "type": "column",
        "valueField": "Qatar"
    }, {
        "balloonText": "[[title]], [[category]]<br><span style='font-size:14px'><b>[[value]]</b> ([[percents]]%)</span>",
        "fillAlphas": 0.9,
        "fontSize": 11,
        "labelText": "[[value]]",
        "lineAlpha": 0.5,
        "title": "Bahrain",
        "type": "column",
        "valueField": "Bahrain"
    }, {
        "balloonText": "[[title]], [[category]]<br><span style='font-size:14px'><b>[[value]]</b> ([[percents]]%)</span>",
        "fillAlphas": 0.9,
        "fontSize": 11,
        "labelText": "[[value]]",
        "lineAlpha": 0.5,
        "title": "Oman",
        "type": "column",
        "valueField": "Oman"
    }, {
        "balloonText": "[[title]], [[category]]<br><span style='font-size:14px'><b>[[value]]</b> ([[percents]]%)</span>",
        "fillAlphas": 0.9,
        "fontSize": 11,
        "labelText": "[[value]]",
        "lineAlpha": 0.5,
        "title": "India",
        "type": "column",
        "valueField": "India"
    }, {
        "balloonText": "[[title]], [[category]]<br><span style='font-size:14px'><b>[[value]]</b> ([[percents]]%)</span>",
        "fillAlphas": 0.9,
        "fontSize": 11,
        "labelText": "[[value]]",
        "lineAlpha": 0.5,
        "title": "Indonesia",
        "type": "column",
        "valueField": "Indonesia"
    },
    {
        "balloonText": "[[title]], [[category]]<br><span style='font-size:14px'><b>[[value]]</b> ([[percents]]%)</span>",
        "fillAlphas": 0.9,
        "fontSize": 11,
        "labelText": "[[value]]",
        "lineAlpha": 0.5,
        "title": "Egypt",
        "type": "column",
        "valueField": "Egypt"
    },
    {
        "balloonText": "[[title]], [[category]]<br><span style='font-size:14px'><b>[[value]]</b> ([[percents]]%)</span>",
        "fillAlphas": 0.9,
        "fontSize": 11,
        "labelText": "[[value]]",
        "lineAlpha": 0.5,
        "title": "Albania",
        "type": "column",
        "valueField": "Albania"
    },
    {
        "balloonText": "[[title]], [[category]]<br><span style='font-size:14px'><b>[[value]]</b> ([[percents]]%)</span>",
        "fillAlphas": 0.9,
        "fontSize": 11,
        "labelText": "[[value]]",
        "lineAlpha": 0.5,
        "title": "Jordan",
        "type": "column",
        "valueField": "Jordan"
    },
      {
        "balloonText": "[[title]], [[category]]<br><span style='font-size:14px'><b>[[value]]</b> ([[percents]]%)</span>",
        "fillAlphas": 0.9,
        "fontSize": 11,
        "labelText": "[[value]]",
        "lineAlpha": 0.5,
        "title": "Turkey",
        "type": "column",
        "valueField": "Turkey"
    },
      {
        "balloonText": "[[title]], [[category]]<br><span style='font-size:14px'><b>[[value]]</b> ([[percents]]%)</span>",
        "fillAlphas": 0.9,
        "fontSize": 11,
        "labelText": "[[value]]",
        "lineAlpha": 0.5,
        "title": "Romania",
        "type": "column",
        "valueField": "Romania"
    }
    ],
    "marginTop": 60,
    "marginRight": 0,
    "marginLeft": 0,
    "marginBottom": 40,
    "autoMargins": true,
    "categoryField": "month",
    "categoryAxis": {
        "gridPosition": "start",
        "axisAlpha": 0,
        "gridAlpha": 0
    },
    "export": {
    	"enabled": false
     }

});
</script>

<div class="page-content-wrapper">

    <div class="page-content">

        <!-- BEGIN PAGE BREADCRUMB -->
        <ul class="page-breadcrumb breadcrumb hide">
            <li>
                <a href="#">Home</a><i class="fa fa-circle"></i>
            </li>
            <li class="active">
                Dashboard
            </li>
        </ul>
        <!-- END PAGE BREADCRUMB -->
        <!-- BEGIN PAGE CONTENT INNER -->
        <div class="row margin-top-10">

            <div class="col-lg-3 col-md-2 col-sm-6 col-xs-12">
           
                <div class="dashboard-stat4">
                    <div class="display">
                        <div class="number">
                            <h3 class="font-purple-soft">{{isset($admin_user_count)?$admin_user_count:''}}</h3>
                            <small>Admin USERS</small>
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

                                <a href="{{url('/admin/admin-users')}}"> See More </a>
                            </div>

                        </div>
                    </div>

                </div>

            </div>


            <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                <div class="dashboard-stat4">
                    <div class="display">
                        <div class="number">
                            <h3 class="font-purple-soft">{{isset($agent_users)?$agent_users:''}}</h3>
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
                <div class="dashboard-stat4">
                    <div class="display">
                        <div class="number">
                            <h3 class="font-purple-soft">{{isset($star_users)?$star_users:''}}</h3>
                            <small>Driver Users</small>
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
                <div class="dashboard-stat4">
                    <div class="display">
                        <div class="number">
                            <h3 class="font-purple-soft">{{isset($mate_users)?$mate_users:''}}</h3>
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
<!--            <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                <div class="dashboard-stat3">
                    <div class="display">
                        <div class="number">
                            <h3 class="font-purple-soft">{{isset($company_user)?$company_user:'0'}}</h3>
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
            </div>-->
        </div>

        <div class="row margin-top-10">
            <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                <div class="dashboard-stat3">
                    <div class="display">
                        <div class="number">
                            <h3 class="font-purple-soft">{{isset($order_count['pending_count'])?$order_count['pending_count']:'0'}}</h3>
                            <small>Pending Trips</small>
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

                                <a href="{{url('/admin/order-list/pending')}}"> See More </a>
                            </div>

                        </div>
                    </div>

                </div>

            </div>

            <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                <div class="dashboard-stat4">
                    <div class="display">
                        <div class="number">
                            <h3 class="font-purple-soft">{{isset($order_counts)?$order_counts['active_count']:'0'}}</h3>
                            <small>Active Trips</small>
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

                                <a href="{{url('/admin/order-list/active')}}"> See More </a>
                            </div>

                        </div>
                    </div>

                </div>

            </div>
            
              <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                <div class="dashboard-stat4">
                    <div class="display">
                        <div class="number">
                            <h3 class="font-purple-soft">{{isset($order_counts)?$order_counts['completed_count']:'0'}}</h3>
                            <small>Completed Trips</small>
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

                                <a href="{{url('/admin/order-list/completed')}}"> See More </a>
                            </div>

                        </div>
                    </div>

                </div>

            </div>

            <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                <div class="dashboard-stat3">
                    <div class="display">
                        <div class="number">
                            <h3 class="font-purple-soft">{{isset($order_counts)?$order_counts["support_count"]:'0'}}</h3>
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
        </div>    
    @if(Auth::user()->isSuperadmin() || Auth::user()->userInformation->user_type=='1')       
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active"><a href="#ordersheatmap" data-toggle="tab" role="tab">Orders Map View</a></li>
            <!--<li role="presentation" class=""><a href="#weekorders" data-toggle="tab" role="tab">Heat Map</a></li>-->
<!--            <li role="presentation" class=""><a href="#country_orders" data-toggle="tab" role="tab">Country wise orders</a></li>
            <li role="presentation" class=""><a href="#stars_this_week" data-toggle="tab" role="tab">Last 7 days registered stars</a></li>
            <li role="presentation" class=""><a href="#country_stars" data-toggle="tab" role="tab">Trip revenue</a></li>-->
        </ul>
        <div class="tab-content">
            <div role="tabpanel" id="ordersheatmap" class="tab-pane active">
                <div id="map_wrapper">
                    <div id="map_canvas" class="mapping"></div>
                </div>
            </div>
<!--            <div role="tabpanel" id="weekorders" class="tab-pane active">
               <div role="tabpanel" id="ordersheatmap" class="tab-pane active">
                <div id="map_wrapper">
                        <div id="map_canvas_heat_map" class="mapping"></div>
                </div>
                </div>

            </div>-->

            <div role="tabpanel" id="country_orders" class="tab-pane">
                <div id="chartdiv" style="width:100%;height:500;background-color: #eaeaea;" ></div>
            </div>
            <div role="tabpanel" id="stars_this_week" class="tab-pane">
                <div class="col-md-12">
                    <div class="portlet box grey-cascade">
                        <div class="portlet-title">
                            <div class="caption">
                                <i class="fa fa-list"></i>Manage Drivers
                            </div>
                            <div class="tools">
                                <a class="collapse" href="javascript:;" data-original-title="" title="">
                                </a>
                                <a class="config" data-toggle="modal" href="#portlet-config" data-original-title="" title="">
                                </a>
                                <a class="reload" href="javascript:;" data-original-title="" title="">
                                </a>
                                <a class="remove" href="javascript:;" data-original-title="" title="">
                                </a>
                            </div>
                        </div>
                        <div class="portlet-body">
                            <table class="table table-striped table-bordered table-hover" id="tbladminusers">
                                <thead>
                                    <tr>
                                        <th>
                                <div class="cust-chqs">  <p><input type="checkbox" id="select_all_delete" ><label for="select_all_delete"></label>  </p></div>
                                </th>
                                <th>Id</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Mobile</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Is Blocked?</th>
                                <th>Registered On</th>
                                <th>Update</th>
                                <th>Vehicles</th>
                                <th>Delete</th>
                                </tr>
                                </thead>
                            </table>
                            <input type="button" onclick='javascript:deleteAll("{{url('/admin/delete-star-selected-user')}}")' name="delete" id="delete" value="Delete Selected" class="btn btn-danger">              						
                        </div>
                    </div>
                </div>
            </div>
            <div role="tabpanel" id="country_stars" class="tab-pane">
                <div id='revenue_div'></div>
            </div>
        </div>
  @endif

    </div>
 @if(Auth::user()->isSuperadmin())      
    <div class="modal fade" id="assign_star" role="dialog">
        <div class="modal-dialog">
            <form name="frm_assign_star" id="frm_assign_star" action="{{url('/admin/assign-star-to-order')}}" method="post">
                <div class="modal-content">
                    {{csrf_field()}}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Assign A Driver </h4>
                    </div>
                    <div class="modal-body">
                        <ul class="list-group" id="user_list">No Driver Available</ul>
                        </div>
                    <div for="assign_to" generated="true" class="text-danger text-center"></div>
                    <div class="modal-footer">
                        <input type="hidden" name="order_id" id="order_id" value="">
                        <button type="submit" class="btn btn-success">Assign Now</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
 @endif
</div>
</div>
@if(Auth::user()->isSuperadmin() || Auth::user()->userInformation->user_type=='1')       
<script>

            jQuery(document).ready(function() {
                    jQuery("#frm_assign_star").validate({
                    errorClass: 'text-danger',
                            errorElement:'div',
                            rules: {
                            assign_to:{
                            required: true
                            }
                            },
                            messages: {
                            assign_to: {
                            required: "Please select atlest one user."
                            }
                            }
                    });
                    });
                            function assignAStar(order_id)
                            {

                            $.ajax({
                            url: '{{url("/admin/order-assign-star")}}',
                                    data: {
                                    order_id:order_id
                                    },
                                    type:'post',
                                    dataType: 'json',
                                    success: function(response) {

                                    if (response.error_code == '0')
                                    {
                                    $('#order_id').val(order_id);
                                            $('#assign_star').modal('show');
                                            var str = '';
                                            $.each(response.data, function(index, value) {

                                            if (value.user_id != null){
                                            str += '<li class="list-group-item"><input  type="radio" name="assign_to" id="assign_to" value="' + value.user_id + '"> <lable>' + value.first_name + ' ' + value.last_name + ' (' + value.distance + ' KM)</lable> </li>';
                                            }
                                            });
                                            $('#user_list').html(str);
                                    } else{
                                    alert("No star is availabe currently for this location and service.")
                                    }}
                            });
                            }


                    $(function() {

                    $("#list-tickets").dataTable().fnDestroy();
                            $('#list-tickets').DataTable({
                    processing: true,
                            serverSide: true,
                            ajax: {
                            "url": '{{url("/admin/order-data")}}',
                                    "complete": afterRequestComplete,
                                    "data": function(d)
                                    {

                                    d.week_filter = 'week';
                                    }
                            },
                            columnDefs: [{
                            "defaultContent": "-",
                                    "targets": "_all"
                            }],
                            columns: [
                            {data:   "id",
                                    render: function (data, type, row) {

                                    if (type === 'display') {

                                    return '<div class="cust-chqs">  <p> <input class="checkboxes" type="checkbox"  id="delete' + row.id + '" name="delete' + row.id + '" value="' + row.id + '"><label for="delete' + row.id + '"></label> </p></div>';
                                    }
                                    return data;
                                    },
                                    "orderable": false,
                            },
                            {data: 'id', name: 'id'},
                            {data: 'order_unique_id', name: 'order_unique_id'},
                            {data: 'star_user', name: 'star_user'},
                            {data: 'mate_user', name: 'mate_user'},
                            {data: 'service_name', name: 'service_name'},
//                        {data: 'order_place_date_time', name: 'order_place_date_time'},
                            {data: 'fare_amount', name: 'fare_amount'},
                            {data: 'order_type', name: 'order_type'},
                            {data: 'status', name: 'status'},
                            {data: 'created_at', name: 'created_at'},
                            {data:   "View",
                                    render: function (data, type, row) {

                                    if (type === 'display') {
                                    return '<a href="{!!url("/admin/order-view/' + row.id + '")!!}" class="btn btn-sm btn-success">View</a>';
                                    }
                                    return data;
                                    },
                                    "orderable": false,
                                    name: 'View'

                            }
                            ]
                    });
                    });
                            //star user 

                                    function changeStatus(user_id, user_status)
                                    {
                                    /* changing the user status*/
                                    var obj_params = new Object();
                                            obj_params.user_id = user_id;
                                            obj_params.user_status = user_status;
                                            if (user_status == 1)
                                    {

                                    $("#active_div" + user_id).css('display', 'inline-block');
                                            $("#active_div_block" + user_id).css('display', 'inline-block');
                                            $("#blocked_div" + user_id).css('display', 'none');
                                            $("#blocked_div_block" + user_id).css('display', 'none');
                                            $("#inactive_div" + user_id).css('display', 'none');
                                    }
                                    jQuery.post("{{url('admin/change_status')}}", obj_params, function (msg) {
                                    if (msg.error == "1")
                                    {
                                    alert(msg.error_message);
                                            $("#active_div" + user_id).css('display', 'none');
                                            $("#active_div_block" + user_id).css('display', 'none');
                                            $("#inactive_div" + user_id).css('display', 'block');
                                    }
                                    else
                                    {

                                    /* toogling the bloked and active div of user*/
                                    if (user_status == 1)
                                    {
                                    alert(msg.message);
                                            $("#active_div" + user_id).css('display', 'inline-block');
                                            $("#active_div_block" + user_id).css('display', 'inline-block');
                                            $("#blocked_div" + user_id).css('display', 'none');
                                            $("#blocked_div_block" + user_id).css('display', 'none');
                                            $("#inactive_div" + user_id).css('display', 'none');
                                    }
                                    else if (user_status == 0)
                                    {
                                    $("#active_div" + user_id).css('display', 'inline-block');
                                            $("#active_div_block" + user_id).css('display', 'inline-block');
                                            $("#blocked_div" + user_id).css('display', 'none');
                                            $("#blocked_div_block" + user_id).css('display', 'none');
                                            $("#inactive_div" + user_id).css('display', 'none');
                                    } else{
                                    $("#active_div" + user_id).css('display', 'none');
                                            $("#active_div_block" + user_id).css('display', 'none');
                                            $("#blocked_div" + user_id).css('display', 'inline-block');
                                            $("#blocked_div_block" + user_id).css('display', 'inline-block');
                                            $("#inactive_div" + user_id).css('display', 'none');
                                    }
                                    }

                                    }, "json");
                                    }


                            $(function() {
                            $('#tbladminusers').DataTable({
                            processing: true,
                                    serverSide: true,
                                    ajax: {
                                    "url":'{{url("/admin/star-users-data")}}',
                                            "complete":afterRequestComplete,
                                            "data": function(d)
                                            {
                                            d.week_filter = 'week';
                                            }
                                    },
                                    columnDefs: [{
                                    "defaultContent": "-",
                                            "targets": "_all"
                                    }],
                                    columns: [

                                    {data:   "id",
                                            render: function (data, type, row)
                                            {
                                            if (type === 'display') {

                                            return '<div class="cust-chqs">  <p> <input class="checkboxes" type="checkbox"  id="delete' + row.user_id + '" name="delete' + row.user_id + '" value="' + row.user_id + '"><label for="delete' + row.user_id + '"></label> </p></div>';
                                            }
                                            return data;
                                            },
                                            "orderable": false,
                                    },
                                    { data: 'id', name: 'id'},
                                    { data: 'first_name', name: 'first_name', searchable: true},
                                    { data: 'last_name', name: 'last_name', searchable: true},
                                    { data: 'username', name: 'user.username', searchable: true},
                                    { data: 'location', name: 'location', searchable: true},
                                    { data: 'status', name: 'status'},
                                    { data: 'blocked', name: 'blocked'},
                                    { data: 'created_at', name: 'created_at' },
                                    {data:   "Update",
                                            render: function (data, type, row) {

                                            if (type === 'display') {

                                            return '<a class="btn btn-sm btn-primary" href="{{url("admin/update-star-user/")}}/' + row.user_id + '">Update</a>';
                                            }
                                            return data;
                                            },
                                            "orderable": false,
                                            name: 'Action'

                                    },
                                    {data:   "Update",
                                            render: function (data, type, row) {

                                            if (type === 'display') {

                                            return '<a class="btn btn-sm btn-default" href="{{url("admin/vehicle-list/")}}/' + row.user_id + '">Vehicle</a>';
                                            }
                                            return data;
                                            },
                                            "orderable": false,
                                            name: 'Action'

                                    },
                                    {data:   "Delete",
                                            render: function (data, type, row) {

                                            if (type === 'display') {

                                            return '<form id="delete_user_' + row.user_id + '" method="post" action="{{url("/admin/delete-star-user")}}/' + row.user_id + '">{{ method_field("DELETE") }} {!! csrf_field() !!}<button onclick="confirmDelete(' + row.user_id + ')" class="btn btn-sm btn-danger" type="button">Delete</button></form>';
                                            }
                                            return data;
                                            },
                                            "orderable": false,
                                            name: 'Action'

                                    },
                                    ]
                            });
                            });
                                    function confirmDelete(id)
                                    {
                                    if (confirm("Do you really want to delete this user?"))
                                    {

                                    $("#delete_user_" + id).submit();
                                    }
                                    return false;
                                    }







</script>
<script type="text/javascript">
//    var ordersLocationsData = {!!$orderLocationData!!}
                            $(document).ready(function()
                                    {

                                   

                                    });

</script>
<script type="text/javascript">
//    var ordersLocationsData = {!!$orderLocationData!!}
                            $(document).ready(function()
                                    {
                                        
                                    var locations = [
                                    {!!$orderLocationData!!}
                                    ];
                                            var map = new google.maps.Map(document.getElementById('map_canvas'), {
                                            zoom: 10,
                                                    center: new google.maps.LatLng(19.0760, 72.8777),
                                                    mapTypeId: google.maps.MapTypeId.ROADMAP
                                            });
                                            var oms = new OverlappingMarkerSpiderfier(map, {markersWantMove:true, markersWontHide:true, keepSpiderfied:true, circleSpiralSwitchover:20});
                                            var infowindow = new google.maps.InfoWindow();
                                            var marker, i;
                                            for (i = 0; i < locations.length; i++) {
                                                marker = new google.maps.Marker({
                                                position: new google.maps.LatLng(locations[i][1], locations[i][2]),
                                                        map: map,
                                                        title:'Trip- ' + locations[i][3],
                                                        desc:'Trip- ' + locations[i][3]
                                                });
                                            oms.addMarker(marker);
                                            google.maps.event.addListener(marker, 'click', (function(marker, i) {
                                            return function() {
                                            infowindow.setContent(locations[i][0]);
                                                    infowindow.open(map, marker);
                                            }
                                            })(marker, i));
                                    }
//                                            var map1 = new google.maps.Map(document.getElementById('map_canvas_heat_map'), {
//                                            zoom: 4,
//                                                    center: new google.maps.LatLng(29.3117, 47.4818),
//                                                    mapTypeId: google.maps.MapTypeId.ROADMAP
//                                            });
//                                            
//                                    //for heatmap
//                                    var heatMapData = [
//                                        {!!$orderLocationDataHeatMap!!}
//                                    ];
//                                        
//                                        var heatmap = new google.maps.visualization.HeatmapLayer({
//                                          data: heatMapData
//                                        });
//                                        var gradient = [
//                                                    'rgba(255, 255, 0, 0)',
//                                                    'rgba(255, 255, 0, 1)',
//                                                    'rgba(255, 225, 0, 1)',
//                                                    'rgba(255, 200, 0, 1)',
//                                                    'rgba(255, 175, 0, 1)',
//                                                    'rgba(255, 160, 0, 1)',
//                                                    'rgba(255, 145, 0, 1)',
//                                                    'rgba(255, 125, 0, 1)',
//                                                    'rgba(255, 110, 0, 1)',
//                                                    'rgba(255, 100, 0, 1)',
//                                                    'rgba(255, 75, 0, 1)',
//                                                    'rgba(255, 50, 0, 1)',
//                                                    'rgba(255, 25, 0, 1)',
//                                                    'rgba(255, 0, 0, 1)'
//                                                ];
//                                        heatmap.setOptions({gradient:gradient,radius: heatmap.get('50'),dissipating:true,opacity:1});
//                                        heatmap.setMap(map1);
//                                            setTimeout(function()
//                                            {
//                                               $("#weekorders").removeClass('active'); 
//                                            },2000);
                                        });
                                    
</script>

<style>
    #map_wrapper {
        height: 400px;
    }

    #map_canvas {
        width: 100%;
        height: 100%;
    }
</style>
<style>
    #map_wrapper {
        height: 400px;
    }

    #map_canvas_heat_map {
        width: 100%;
        height: 100%;
    }
</style>
@endif
@endsection