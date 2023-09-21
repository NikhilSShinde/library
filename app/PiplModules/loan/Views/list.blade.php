@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Manage Loan</title>

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
            <li> <a href="javascript:void(0)">Manage Loan</a></li>
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
                            <i class="fa fa-list"></i>Manage Loan
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
                                        <a href="{{url('/admin/loan/add')}}" id="sample_editable_1_new" class="btn green">
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
                                    <th>Driver Name</th>
                                    <th>Loan Account</th>
                                    <th>Loan Amount</th>
                                    <th>Intrest</th>
                                    <th>Terms</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                        <input type="button" onclick='javascript:deleteAll("{{url('/admin/loan/delete-selected')}}")' name="delete" id="delete" value="Delete Selected" class="btn btn-danger">
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
                ajax: {"url": '{{url("/admin/loan-list-data")}}', "complete": afterRequestComplete},
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
                {data: 'driver_name', name: 'driver_name'},
                {data: 'loan_account', name: 'loan_account'},
                {data: 'loan_amount', name: 'loan_amount'},
                {data: 'intrest', name: 'intrest'},
                {data: 'terms', name: 'terms'},
                {data:   "Action",
                        render: function (data, type, row)
                        {

                        if (type === 'display') {

                        return '<a  class="btn btn-sm btn-primary" href="{{url("/admin/loan-emi-list")}}/' + row.id + '">EMI Detail</a>';
                        }
                        return data;
                        },
                        "orderable": false,
                        name: 'Action'

                }
                ]});
        });
    </script>
    @endsection
