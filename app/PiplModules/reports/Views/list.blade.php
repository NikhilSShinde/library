@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Manage Trips Reports</title>

@endsection

@section('content')


<script type="text/javascript"  src="{{url('public/media/backend/js/jquery-ui.min.js')}}"></script>
<link rel="stylesheet" type="text/css" href="{{url('public/media/backend/css/datepicker/jquery-ui.css')}}">
<div class="page-content-wrapper">
    <div class="page-content">
        <!-- BEGIN PAGE BREADCRUMB -->
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{url('admin/dashboard')}}">Dashboard</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="javascript:void(0)">Manage Reports</a>
            </li>
        </ul>
        @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
        @endif
        <div class="row">
            <div class="col-md-12">
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                <div class="portlet box grey-cascade">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-list"></i>Trips Report
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
                                    
                                 @if(Auth::user()->userInformation->user_type=='1')  
                                    <select class="form-control" id="order_country"  name="order_country">
                                        <option value="">--Select Country--</option>
                                        @foreach($all_countries as $key=>$name)
                                            @if($name->id!='17')
                                             <option value="{{$name->id}}">{{$name->translate()->name}}</option>
                                            @endif 
                                        @endforeach
                                    </select>
                                 @endif   
                                </div>
                                <div class="clearfix"></div>
                                <br/>
                                <div class="col-md-12">
                                    <ul class="list-inline">
                                        <li> <label><strong>Export Type</strong> : </label> </li>
                                    </li><input type="radio" value="excel" id="excel" name="download" checked=""></li><li><label>Excel</label></li>                          
                                <li><input type="radio"  value="csv" id="csv" name="download" ></li><li><label>CSV</label></li>
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <li><button type="submit" class="btn btn-primary" id="btn_value" name="btn_value" value="download">Export Report</button>  </li></ul>                              
                        </div>
                    </div>
                </form>
                <table class="table table-striped table-bordered table-hover" id="list-tickets">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Trip No.</th>
                            <th>Driver User</th>
                            <th>Customer User</th>
                            <th>Service Name</th>
                            <!--<th>Trip Place Date</th>-->
                            <th>Fare Amount</th>
                            <th>Trip Type</th>
                            <th>Trip Status</th>
                            <th>Posted Date</th>
                                                        <th>Action</th>
                        </tr>
                    </thead>
                </table>                
            </div>
        </div>
    </div>
</div>
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
    //For Start Date Calender:
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
function initDatatable()
{


    $("#list-tickets").dataTable().fnDestroy();
    $('#list-tickets').DataTable({
        processing: true,
        serverSide: true,
        bStateSave: true,
        ajax: {
            "url": '{{url("/admin/report-order-data/")}}',
            "complete": afterRequestComplete
            ,
            "data": function(d)
            {
                d.order_filter_by = $("#filter_type").val();
                d.order_country = $("#order_country").val();
                d.start_date = $("#start_date").val();
                d.end_date = $("#end_date").val();
                d.country_name = '{{Request::segment(4)}}';
            }
        },
        columnDefs: [{
                "defaultContent": "-",
                "targets": "_all"
            }],
        columns: [
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
            {data: "View",
                render: function(data, type, row) {

                    if (type === 'display') {
                        return '<a href="{!!url("/admin/order-view-report/' + row.id + '")!!}" class="btn btn-sm btn-success">View</a>';
                    }
                    return data;
                },
                "orderable": false,
                name: 'View'

            }
        ]
    });
}
</script>
@endsection
