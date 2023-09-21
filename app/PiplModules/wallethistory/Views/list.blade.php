@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Manage Wallet History</title>

@endsection

@section('content')
<link rel="stylesheet" type="text/css" href="{{url('public/media/backend/css/datepicker/jquery-ui.css')}}">
<script type="text/javascript"  src="{{url('public/media/backend/js/jquery-ui.min.js')}}"></script>

<div class="page-content-wrapper">
    <div class="page-content">
        <!-- BEGIN PAGE BREADCRUMB -->
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{url('admin/dashboard')}}">Dashboard</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="javascript:void(0)">Manage Wallet History</a>
            </li>
        </ul>
        @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
        @endif
        <div class="row">
            <div class="col-md-12">
                <div class="portlet box grey-cascade">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-list"></i>Manage Wallet History
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
                            <form class="navbar-form navbar-left" id="frm_filter" name="frm_filter" method="POST">
                         <div class="col-md-12">
                              <div class="row">  
                                   <div class="form-group">
                                        <input type="text" class="form-control" id="start_date" name="start_date" placeholder="From Date">
                                    </div>
                                    <div class="form-group">
                                        <input type="text" class="form-control" id="end_date" name="end_date" placeholder="To Date">
                                    </div>
                                    <button type="button" class="btn btn-primary" id="btn_search_date" name="btn_search">Search</button>                                
                             
                                   <div class="form-group">
                                      <input type="text" class="form-control" id="search_value" name="search_value" placeholder="Search by description">
                                    </div>
                                  <select class="form-control" name="filter_type" id="filter_type">
                                        <option value="">Type</option>
                                        <option value="1">Debit</option>
                                        <option value="0">Credit</option>
                                       
                                    </select> 
                                    <select class="form-control" name="filter_type_reply" id="filter_type_reply">
                                        <option value="">User Type</option>
                                        <option value="3">Customer</option>
                                        <option value="2">Driver</option>
                                        <option value="4">Agent</option>
                                       
                                    </select> 
                                    
                                    <button type="button" class="btn btn-primary" id="btn_search" name="btn_search">Search</button>                                
                                   
                                   </div>
                              </div>
                       </form> 
                        <table class="table table-striped table-bordered table-hover" id="wallet-histpry">
                            <thead>
                                <tr>
                                    <th>#ID</th>
                                    <th>User Name</th>
                                    <th>User Type</th>
                                    <th>Transaction Amount</th>
                                    <th>Balance</th>
                                    <th>Transaction Description</th>
                                    <th>Transaction Type</th>
                                    <!--<th>Payment Type</th>-->
                                    <th>Transaction Date</th>
                                </tr>
                            </thead>
                        </table>
                        <!--<input type="button" onclick='javascript:deleteAll("{{url('/admin/support-tickets/delete-selected')}}")' name="delete" id="delete" value="Delete Selected" class="btn btn-danger">-->
                    </div>
                </div>



                <!-- END PAGE CONTENT INNER -->
            </div>
        </div>
        <!-- END CONTENT -->
    </div>
    <script>
        
$(function() {
    $("#btn_search").on("click", function() {
    initDatatable();
});
});
$(function() {
    $("#btn_search_date").on("click", function() {
    initDatatable();
});
});
//$(function() {
//    $("#filter_type").on("change", function() {
//    initDatatable();
//});
//});
//$(function() {
//    $("#filter_type_reply").on("change", function() {
//    initDatatable();
//});
//});
function initDatatable()
{
    $("#wallet-histpry").dataTable().fnDestroy();
     $('#wallet-histpry').DataTable({
                processing: true,
                serverSide: true,
                bStateSave: true,
                order: [ [0, 'desc'] ],
                ajax: {"url": '{{url("/admin/wallet-history-data/$user_id")}}',
                        "complete": afterRequestComplete,
                         "data": function(d)
                        {
                           // d.search_value = $("#search_value").val();
                            d.start_date = $("#start_date").val();
                            d.end_date = $("#end_date").val();
                            d.filter_type = $("#filter_type").val();
                            d.filter_type_reply = $("#filter_type_reply").val();
                            d.search_value = $("#search_value").val();

                        }
                    },
                columnDefs: [{
                        "defaultContent": "-",
                        "targets": "_all"
                    }],
                columns: [
                    {data: "id", name: 'id'},
                    {data: 'user_name', name: 'user_name'},
                    {data: 'user_type', name: 'user_type'},
                    {data: 'transaction_amount', name: 'transaction_amount'},
                    {data: 'final_amout', name: 'final_amout'},
                    {data: 'trans_desc', name: 'trans_desc'},
                    {data: 'transaction_type', name: 'transaction_type'},
//                    {data: 'payment_type', name: 'payment_type'},
                    {data: 'created_at', name: 'created_at'},
                ],
                
            });
}
        var total1=0;
        $(function() {
            
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
            $('#wallet-histpry').DataTable({
                processing: true,
                serverSide: true,
                bStateSave: true,
                order: [ [0, 'desc'] ],
                ajax: {"url": '{{url("/admin/wallet-history-data/$user_id")}}',"complete": afterRequestComplete},
                columnDefs: [{
                        "defaultContent": "-",
                        "targets": "_all"
                    }],
                columns: [
                    {data: "id", name: 'id'},
                    {data: 'user_name', name: 'user_name'},
                    {data: 'user_type', name: 'user_type'},
                    {data: 'transaction_amount', name: 'transaction_amount'},
                    {data: 'final_amout', name: 'final_amout'},
                    {data: 'trans_desc', name: 'trans_desc'},
                    {data: 'transaction_type', name: 'transaction_type'},
//                    {data: 'payment_type', name: 'payment_type'},
                    {data: 'created_at', name: 'created_at'},
                ],
                
            });
        });
    </script>
    @endsection
