@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Manage Vehicle</title>

@endsection

@section('content')
<div class="page-content-wrapper">
    <div class="page-content">
        <!-- BEGIN PAGE BREADCRUMB -->
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{url('admin/dashboard')}}">Dashboard</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="{{url('admin/star-users')}}">Manage Users</a>
                   <i class="fa fa-circle"></i>
               
            </li>
            <li> <a href="javascript:void(0)">Manage Vehicle</a></li>
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
                            <i class="fa fa-list"></i>Manage Vehicle
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
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="btn-group">
                                        <a href="{{url('/admin/vehicle/add/'.$user_id)}}" id="sample_editable_1_new" class="btn green">
                                            Add New <i class="fa fa-plus"></i>
                                        </a>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <table class="table table-striped table-bordered table-hover" id="list-tickets">
                            <thead>
                                <tr>
                                    <th>
                            <div class="cust-chqs">  <p><input type="checkbox" id="select_all_delete" ><label for="select_all_delete"></label>  </p></div>
                            </th>
                            <th>User Name</th>
                            <th>Make/Model</th>
                            <th>Plate Number</th>
                            <th>Vehicle Description</th>
                            <th>Vehicle Image </th>
                            <th>Status</th>
                            <th>Action</th>
                            </tr>
                            </thead>
                        </table>
                        <input type="button" onclick='javascript:deleteAll("{{url('/admin/vehicle/delete-selected')}}")' name="delete" id="delete" value="Delete Selected" class="btn btn-danger">
                    </div>
                </div>
                <!-- END PAGE CONTENT INNER -->
            </div>
        </div>
        <!-- END CONTENT -->
    </div>
    <script>
                $(function() {
                $('#list-tickets').DataTable({
                processing: true,
                serverSide: true,
                bStateSave: true,
                        ajax: {"url": '{{url("/admin/vehicle-list-data/$user_id")}}', "complete": afterRequestComplete},
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
                        {data: 'user_name', name: 'user_name'},
                        {data: 'vehicle_name', name: 'vehicle_name'},
                        {data: 'plate_number', name: 'plate_number'},
                        {data: 'vehicle_desc', name: 'vehicle_desc'},
                        {data: 'vehicle_image', render:function(data, type, row){
                        if (type === "display"){
                        return '<img src="{!!asset("storageasset/vehicle-images/' + row.vehicle_image + '")!!}" onerror=src="{!!asset("storageasset/1481886669.png")!!}" class="thumb-image" width="100" height="100">'
                        }
                        return data;
                        }, "orderable": false,
                                name: 'vehicle_image'},
                        {data: 'status', name: 'status'},
                        {data:   "Delete",
                                render: function (data, type, row) {

                                if (type === 'display') {
                                return '<a href="{!!url("/admin/vehicle/update/' + row.id + '")!!}" class="btn btn-sm btn-danger">Edit</a>';
                                }
                                return data;
                                },
                                "orderable": false,
                                name: 'Delete'

                        }

                        ]});
                });
    </script>
    @endsection
