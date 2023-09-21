@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Manage Trips</title>

@endsection

@section('content')

<script src="http://maps.google.com/maps/api/js?v=3&key=AIzaSyD58tVuK_zOf_mw-VWm-rLRWPK5RgGucco" type="text/javascript"></script>
<script type="text/javascript"  src="{{url('public/media/backend/js/jquery-ui.min.js')}}"></script>
<link rel="stylesheet" type="text/css" href="{{url('public/media/backend/css/datepicker/jquery-ui.css')}}">
<div class="page-content-wrapper">
    <div class="page-content">
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{url('admin/dashboard')}}">Dashboard</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="javascript:void(0)">Manage Trips</a>
            </li>
        </ul>

        @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
        @endif
        @if (session('status_error'))
        <div class="alert alert-danger">
            {{ session('status_error') }}
        </div>
        @endif

        <div class="row">
            <div class="col-md-12">
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                <div class="portlet box grey-cascade">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-list"></i>Manage Trips
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
                        <form class="navbar-form navbar-left" id="frm_filter" name="frm_filter" method="POST" action="{{url('/download')}}">
                            <div class="row">  
                                <div class="col-md-12">
                                    <label><strong>Filter By</strong> : </label>  
                                    <div class="form-group">
                                        <input type="text" class="form-control" id="start_date" name="start_date" placeholder="From Date">
                                    </div>

                                    <div class="form-group">
                                        <input type="text" class="form-control" id="end_date" name="end_date" placeholder="To Date">
                                    </div>
                                    <button type="button" class="btn btn-primary" id="btn_search" name="btn_search">Search</button>                                

                                    <select class="form-control" name="filter_type" id="filter_type">
                                        <option value="">Choose Filter</option>
                                        <option value="today">Todays</option>
                                        <option value="week">Last 7 Days</option>
                                        <option value="month">Current Month</option>
                                        <option value="year">Current Year</option>
                                    </select> 
                                    @if(Request::segment(3)=="")
                                    <select class="form-control" id="order_country"  name="order_country">
                                        <option value="">--Select Country--</option>
                                        @foreach($all_countries as $key=>$name)
                                        <option value="{{$name->id}}">{{$name->translate()->name}}</option>
                                        @endforeach
                                    </select>
                                    @endif
                                </div>
                                <div class="clearfix"></div>

                            </div>
                        </form>
                        <table class="table table-striped table-bordered table-hover" id="list-orders">
                            <thead>
                                <tr>
                                    <th><div class="cust-chqs">  <p><input type="checkbox" id="select_all_delete" ><label for="select_all_delete"></label>  </p></div></th>
                                    <th>Trip No</th>
                                    <th>Driver User</th>
                                    <th>Customer</th>
                                    <th>Service Name</th>
                                    <!--<th>Trip Place Date</th>-->
                                    <th>Fare Amount</th>
                                    <th>Total Amount</th>
                                    <th>Trip Type</th>
                                    <th>Cancellation Charge</th>
                                    <th>Cancellation Date</th>
                                    <th>Trip Status</th>
                                    <th>Driver user Status</th>
                                    <th>Posted Date</th>
                                    <th>Assign A Driver</th>
                                    <th>View</th>
                                    <th>Notifications</th>
                                    <th>Rejection Details</th>
                                    <!--<th>Quotes</th>-->
                            </tr>
                            </thead>
                        </table>
                        <input type="button" onclick='javascript:deleteAll("{{url('/admin/order/delete-selected')}}")' name="delete" id="delete" value="Delete Selected" class="btn btn-danger">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="assign_star" role="dialog">
        <div class="modal-dialog">
            <form name="frm_assign_star" id="frm_assign_star" action="{{url('/admin/assign-star-to-order')}}" method="post">
                <div class="modal-content">
                    {{csrf_field()}}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Assign A Driver user </h4>
                    </div>
                    <div class="modal-body">
                        <ul class="list-group" id="user_list">No Driver user Available</ul>

                    </div>
                    <div for="assign_to" generated="true" class="text-danger text-center"></div>
                    <div class="modal-footer">
                        <input type="hidden" name="order_id" id="order_id" value="">
                        <button type="submit" class="btn btn-success">Assign Now</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </form>
              <div class="modal-content">
                   
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Assign A Driver user Map view </h4>
                    </div>
                    <div class="modal-body">
                        <ul class="list-group" id="user_list">No Driver user Available</ul>
                    </div>
                    <div for="assign_to" generated="true" class="text-danger text-center"></div>
                    <div class="modal-footer">
                        <input type="hidden" name="order_id" id="order_id" value="">
                        <button type="submit" class="btn btn-success">Assign Now</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                
            </form>
        </div>
    </div><!-- /.modal-dialog -->
</div>
<script>
            $("#filter_type").on("change", function() {
               initDatatable();
            });
            $("#order_country").on("change", function() {

                initDatatable();
            });
            $("#btn_search").on("click", function() {
                initDatatable();
            });
                    $(function() {

                    /* 
                     * @ Manage mise not found issue for datepicker:
                     *  
                     *  Below code is used to handle msie not found issue,
                     *  as jquery 1.9 or above version not support the $.browser function 
                     *  which is needed for displaying date picker using ui.js and ui.css.
                     *  
                     *   [START] :: From Here 
                     */
                    jQuery.browser = {};
                            (function()
                            {
                            jQuery.browser.msie = false;
                                    jQuery.browser.version = 0;
                                    if (navigator.userAgent.match(/MSIE ([0-9]+)\./))
                            {
                            jQuery.browser.msie = true;
                                    jQuery.browser.version = RegExp.$1;
                            }
                            })();
                            //For Driver usert Date Calender:
                            $("#start_date").datepicker({
                            dateFormat: "yy-mm-dd",
                            //minDate: 0,
                            onSelect: function(date) {
                            var date2 = $('#start_date').datepicker('getDate');
                                    date2.setDate(date2.getDate() + 1);
                                    $('#end_date').datepicker('setDate', date2);
                                    $('#end_date').datepicker('option', 'minDate', date2);
                            }
                    });
                            //For End Date Calender:
                            $('#end_date').datepicker({
                             dateFormat: "yy-mm-dd",
                             onClose: function() {
                             var dt1 = $('#start_date').datepicker('getDate');
                                    console.log(dt1);
                                    var dt2 = $('#end_date').datepicker('getDate');
                                    if (dt2 <= dt1) {
                             var minDate = $('#end_date').datepicker('option', 'minDate');
                                    $('#end_date').datepicker('setDate', minDate);
                            }
                            }
                    });
                            initDatatable();
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
                                    alert("No driver is availabe currently for this location and service.")
                                    }}
                            });
                    }



            function initDatatable()
            {
                
                    $("#list-orders").dataTable().fnDestroy();
                    $('#list-orders').DataTable({
                    processing: true,
                    serverSide: true,
                    bStateSave: true,
                    ajax: {
                    "url": '{{url("/admin/order-data")}}/{{$status}}',
                            "complete": afterRequestComplete,
                            "data": function(d)
                            {
                            d.order_filter_by = $("#filter_type").val();
                                    d.order_country = $("#order_country").val();
                                    d.start_date = $("#start_date").val();
                                    d.end_date = $("#end_date").val();
                                    d.country_name = '{{Request::segment(3)}}';
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
                    {data: 'order_unique_id', name: 'order_unique_id'},
                    {data: 'star_user', name: 'star_user'},
                    {data: 'mate_user', name: 'mate_user'},
                    {data: 'service_name', name: 'service_name'},
//                        {data: 'order_place_date_time', name: 'order_place_date_time'},
                    {data: 'fare_amount', name: 'fare_amount'},
                    {data: 'total_amount', name: 'total_amount'},
                    {data: 'order_type', name: 'order_type'},
                    {data: 'cancellation_charge', name: 'cancellation_charge'},
                    {data: 'cancelled_date', name: 'cancelled_date'},
                    {data: 'status', name: 'status'},
                    {data: 'star_status', name: 'star_status'},                    
                    {data: 'created_at', name: 'created_at'},
                    {data:   "View",
                            render: function (data, type, row) {

                            if (type === 'display') {
                                if (row.status == 'Active')
                                { 
                                    return 'Already Assigned'
                                }
                                else if (row.status == 'Expired')
                                {
                                     return '-'
                                }  
                                 else if (row.status == 'Cancelled')
                                {
                                     return '-'
                                }  
                                 else if (row.status == 'Completed')
                                {
                                    return 'Already Assigned'
                                }  
                                else if (row.status == 'Pending')
                                {
                                    return '<a  href="{!!url("/admin/assign-star/' + row.id + '")!!}" onclick"="assignAStar(' + row.id + ')" class="btn btn-sm btn-warning">Assign A Driver</a>';
                                }
                            }
                            return data;
                            },
                            "orderable": false,
                            name: 'View'

                    },
                    {data:   "View",
                            render: function (data, type, row) {

                            if (type === 'display') {
                            return '<a href="{!!url("/admin/order-view/' + row.id + '")!!}" class="btn btn-sm btn-success">View</a>';
                            }
                            return data;
                            },
                            "orderable": false,
                            name: 'View'

                    },
                    {data:   "View",
                            render: function (data, type, row) {

                            if (type === 'display') {
                            return '<a href="{!!url("/admin/view-order-notifications/' + row.id + '")!!}" class="btn btn-sm btn-success">View Notification</a>';
                            }
                            return data;
                            },
                            "orderable": false,
                            name: 'View'

                    },
                    {data:   "View",
                            render: function (data, type, row) {

                            if (type === 'display') {
                            return '<a href="{!!url("/admin/view-order-rejected/' + row.id + '")!!}" class="btn btn-sm btn-success">View Rejection</a>';
                            }
                            return data;
                            },
                            "orderable": false,
                            name: 'View'

                    },
//                    {data:   "Quote", name: 'Quote'}
                    ]
            });
            }




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
</script>
@endsection
