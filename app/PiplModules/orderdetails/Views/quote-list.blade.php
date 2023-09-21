@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>View  Orders Quotes</title>

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
                <a href="javascript:void(0)">View  Orders Quotes</a>
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
                            <i class="fa fa-list"></i>View  Orders Quotes
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
                       
                        <table class="table table-striped table-bordered table-hover" id="list-order-quotes">
                            <thead>
                                <tr>
                                    <th><div class="cust-chqs">  <p><input type="checkbox" id="select_all_delete" ><label for="select_all_delete"></label>  </p></div></th>
                                    <th>Order No</th>
                                    <th>Driver user Name</th>
                                    <th>Quotation Amount</th>
                                    <th>Description</th>
                                    <th>Pickup Location</th>
                                    <th>Status</th>
                                    <th>Posted Date</th>
                            </tr>
                            </thead>
                        </table>
                        <input type="button" onclick='javascript:deleteAll("{{url('/admin/order/delete-selected-quotes')}}")' name="delete" id="delete" value="Delete Selected" class="btn btn-danger">
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
        </div>
    </div><!-- /.modal-dialog -->
</div>
<script>
       $(function() {
             $('#list-order-quotes').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {"url": '{{url("/admin/order-quotes-data/$order_id")}}',"complete": afterRequestComplete},
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
                    {data: 'qutation_amount', name: 'qutation_amount'},
                    {data: 'description', name: 'description'},
                    {data: 'pickup_location', name: 'pickup_location'},
                    {data: 'status', name: 'status'},
                    {data: 'created_at', name: 'created_at'},
                    ]
                   
            });
            });
</script>
@endsection
