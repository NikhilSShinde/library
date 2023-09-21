@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Manage Loan EMI</title>

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
                <a href="{{url('admin/loan-list')}}">Manage Loan</a>
                <i class="fa fa-circle"></i>
            </li>
            <li> <a href="javascript:void(0)">Manage Loan EMI</a></li>
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
                            <i class="fa fa-list"></i>Manage Loan EMI
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

                        <table class="table table-striped table-bordered table-hover" id="emi-list">
                            <thead>
                                <tr>
                                    <th>EMI</th>                                    
                                    <th>EMI Date</th>
                                    <th>Paid</th> 
                                    <th>Paid Date</th>  
                                    <th>Payment Id</th>  
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>

                    </div>
                </div>
                <!-- END PAGE CONTENT INNER -->
            </div>
        </div>
        <!-- END CONTENT -->
    </div>
    <script>
        $(function () {
            $('#emi-list').DataTable({
                processing: true,
                serverSide: true,
                bStateSave: true,
                ajax: {"url": '{{url("/admin/loan-emi-list-data")}}/{{$loan_id}}', "complete": afterRequestComplete},
                columnDefs: [{
                        "defaultContent": "-",
                        "targets": "_all"
                    }],
                columns: [                    
                    {data: 'emi', name: 'emi'},
                    {data: 'emi_date', name: 'emi_date'},
                    {data: 'paid', name: 'paid'},
                    {data: 'paid_date', name: 'paid_date'},
                    {data: 'payment_id', name: 'payment_id'},
                    {data:   "Action",
                        render: function (data, type, row)
                        {
                            if (type === 'display' && row.paid === 'No') {
                                return '<a  class="btn btn-sm btn-primary" href="{{url("/admin/loan-pay-emi")}}/' + row.loan_id + '/' + row.id + '">Pay</a>';
                            }
                                return "-";
                            },
                        "orderable": false,
                        name: 'Action'
                }

                ]});
        });
    </script>
    @endsection
