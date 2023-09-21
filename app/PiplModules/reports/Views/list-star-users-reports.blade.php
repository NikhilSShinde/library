@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Manage Driver users</title>

@endsection


@section('content')
<script type="text/javascript"  src="{{url('public/media/backend/js/jquery-ui.min.js')}}"></script>
<link rel="stylesheet" type="text/css" href="{{url('public/media/backend/css/datepicker/jquery-ui.css')}}">
<script type="text/javascript"  src="{{url('public/media/backend/js/star-rate.js')}}"></script>
<div class="page-content-wrapper">
    <div class="page-content">
        <!-- BEGIN PAGE BREADCRUMB -->
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{url('admin/dashboard')}}">Dashboard</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="javascript:void(0)">Driver Users Report</a>

            </li>
        </ul>

        @if (session('update-user-status'))
        <div class="alert alert-success">
            {{ session('update-user-status') }}
        </div>
        @endif

        @if (session('delete-user-status'))
        <div class="alert alert-success">
            {{ session('delete-user-status') }}
        </div>
        @endif    

        <div class="row">

            <div class="col-md-12">
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                <div class="portlet box grey-cascade">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="glyphicon glyphicon-globe"></i>Driver Users Report
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

                        <div class="table-toolbar">
                            <form class="navbar-form navbar-left" id="frm_filter" name="frm_filter" method="POST" action="{{url('/download/star-users')}}">
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
                                            <option value="{{$name->id}}">{{$name->translate()->name}}</option>
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
                </div>
                <table class="table table-striped table-bordered table-hover" id="tbladminusers">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Mobile</th>
                            <th>Rating</th>
                            <th>Location</th>
                            <th>Status</th> 
                            <th>Device</th> 
                            
                            <th>Registered On</th>                            
                        </tr>
                    </thead>
                </table>
                
        </div>

    </div>
</div>
<script>

            $("#filter_type").on("change", function(){
    initDatatable();
    });
            $("#order_country").on("change", function(){

    initDatatable();
    });
            $("#btn_search").on("click", function(){
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
            $("#tbladminusers").dataTable().fnDestroy();
                    $('#tbladminusers').DataTable({
            processing: true,
                    serverSide: true,
                    ajax: {"url":'{{url("/admin/star-users-report-data")}}',
                            "complete":afterRequestComplete,
                            "data": function(d)
                            {
                                    d.user_filter_by = $("#filter_type").val();
                                    d.user_country = $("#order_country").val();
                                    d.user_reg_from_date = $("#start_date").val();
                                    d.user_reg_to_date = $("#end_date").val();
                            }
                    },
                    columnDefs: [{
                    "defaultContent": "-",
                            "targets": "_all"
                    }],
                    columns: [


                    { data: 'user_id', name: 'user_id'},
                    { data: 'first_name', name: 'first_name', searchable: true},
                    { data: 'last_name', name: 'last_name', searchable: true},
                    { data: 'username', name: 'user.username', searchable: true},
                     {data: 'rating', render: function(data, type, row) {
                                if (type === 'display') {
                                   return '<div id="hearts-existing" class="starrr" data-rating="' + data + '"></div>';
                                 }
                                 return data;
                                 },
                                 "orderable": false, name: 'rating'
                     },
                    { data: 'location', name: 'location', searchable: true},
                    { data: 'status', name: 'status'},                    
                    { data: 'device', name: 'device'},                    
                    { data: 'created_at', name: 'created_at' }                    
                    ]
            });
             $('#tbladminusers').on('draw.dt', function() {
                    $(".starrr").starrr()
                });
            }
    function confirmDelete(id)             {
    if (confirm("Do you really want to delete this user?"))
    {

    $("#delete_user_" + id).submit();
    }
    return false;
    }
</script>
<style>
    #hearts { color: #ee8b2d;}
    #hearts-existing { color: #ee8b2d;}
    .glyphicon{
        display: inline-block;
        font-size: 20px;
        line-height: 14px;
        margin-left: 5px;
    }
    .help-block {
        margin-bottom: 10px;
        margin-top: 10px;
    }
</style>  
@endsection
