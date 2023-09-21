@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>View Order Assign Notifications</title>

@endsection

@section('content')

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
                <a href="{{url('admin/order-list')}}">Manage Trips</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="javascript:void(0)">Notifications Sent to Users</a>
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
                            <i class="fa fa-list"></i>Manage Notifications
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
                        
                        <table class="table table-striped table-bordered table-hover" id="list-notification">
                            <thead>
                                <tr>
                                    <th><div class="cust-chqs">  <p><input type="checkbox" id="select_all_delete" ><label for="select_all_delete"></label>  </p></div></th>
                                    <th>User Id </th>
                                    <th>User Name </th>
                                    <th>Date Time</th>
                            </tr>
                            </thead>
                        </table>
                        <input type="button" onclick='javascript:deleteAll("{{url('/admin/notification/delete-selected')}}")' name="delete" id="delete" value="Delete Selected" class="btn btn-danger">
                    </div>
                </div>
            </div>
        </div>
    </div>

   
<script>
            
            $(function(){
               initDatatable(); 
            });
            
            function initDatatable()
            {
            $("#list-notification").dataTable().fnDestroy();
                    $('#list-notification').DataTable({
            processing: true,
                    serverSide: true,
                    ajax: {
                    "url": '{{url("/admin/load-notifications")}}/'+<?php echo $order_id; ?>,
                            "complete": afterRequestComplete
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
                    {data: 'user_id', name: 'user_id'},
                    {data: 'sent_to', name: 'sent_to'},
                    {data: 'created_at', name: 'created_at'},
                   
                    
                    ]
            });
            }


</script>
@endsection
