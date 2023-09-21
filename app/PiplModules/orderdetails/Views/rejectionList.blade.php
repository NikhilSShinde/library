@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Manage Rejection</title>

@endsection

@section('content')


<script type="text/javascript"  src="{{url('public/media/backend/js/jquery-ui.min.js')}}"></script>
<link rel="stylesheet" type="text/css" href="{{url('public/media/backend/css/datepicker/jquery-ui.css')}}">
<div class="page-content-wrapper">
    <div class="page-content">
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{url('admin/order-list')}}">Manage Trips</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="javascript:void(0)">Rejection</a>
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
                            <i class="fa fa-list"></i>Manage Rejection
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
                        
                        <table class="table table-striped table-bordered table-hover" id="list-rejected">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Reason</th>
                                    <th>Rejected By</th>
                                    <th>Reject on </th>
                            </tr>
                            </thead>
                        </table>
                        
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
            $("#list-rejected").dataTable().fnDestroy();
                    $('#list-rejected').DataTable({
            processing: true,
                    serverSide: true,
                    ajax: {
                    "url": '{{url("/admin/load-rejected-orders")}}/'+<?php echo $order_id; ?>,
                            "complete": afterRequestComplete
                    },
                    columns: [
                    {data:   "id",name: 'id'},
                    {data: 'message', name: 'message'},
                    {data: 'reject_by', name: 'reject_by'},
                    {data: 'created_at', name: 'created_at'},
                   
                    
                    ]
            });
            }


</script>
@endsection
