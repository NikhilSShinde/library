@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Manage Coupons's</title>

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
                <a href="javascript:void(0)">Manage Coupons</a>
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
                            <i class="fa fa-list"></i>Manage Coupons
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
                                        <a href="{{url('/admin/coupon/create')}}" id="sample_editable_1_new" class="btn green">
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
                            <th>Title</th>
                            <th>Discount</th>
                            <th>Country</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Action</th>
                            </tr>
                            </thead>
                        </table>
                        <input type="button" onclick='javascript:deleteAll("{{url('/admin/coupon/delete-selected')}}")' name="delete" id="delete" value="Delete Selected" class="btn btn-danger">
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
                        ajax: {"url":'{{url("/admin/coupon-data")}}', "complete":afterRequestComplete},
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
                        { data: 'title', name: 'title'},
                        { data: 'discount', name: 'discount'},
                        { data: 'country_code', name: 'country_code'},
                        { data: 'start_date', name: 'start_date'},
                        { data: 'end_date', name: 'end_date'},
                        { data: 'type', name: 'type' },
                        { data: 'status', name: 'status' },
                        {data:   "Action",
                                render: function (data, type, row) {

                                if (type === 'display') {
                                return '<a href="{!!url("/admin/coupon/view/'+row.id+'")!!}" class="btn btn-sm btn-info">View</a>' + '<a href="{!!url("/admin/coupon/update/'+row.id+'")!!}" class="btn btn-sm btn-danger">Edit</a>';
                                }
                                return data;
                                },
                                "orderable": false,
                                name: 'Delete'

                        }


                        ]
                });
                });
                function confirmDelete(id)
                {
                if (confirm("Do you really want to delete this category?"))
                {
                $("#category_delete_" + id).submit();
                }
                return false;
                }
    </script>
    @endsection
