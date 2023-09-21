@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Manage Rating And Review</title>

@endsection

@section('content')
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
                <a href="javascript:void(0)">Manage Rating And Review</a>
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
                            <i class="fa fa-list"></i>Manage Rating And Review
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
                        <table class="table table-striped table-bordered table-hover" id="list-tickets">
                            <thead>
                                <tr>
                                    <th><div class="cust-chqs">  <p><input type="checkbox" id="select_all_delete" ><label for="select_all_delete"></label>  </p></div></th>
                                    <th>Trip Id</th>
                                    <th>From Name</th>
                                    <th>To Name</th>
                                    <th>Rating</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                        <input type="button" onclick='javascript:deleteAll("{{url('/admin/rating-review/delete-selected')}}")' name="delete" id="delete" value="Delete Selected" class="btn btn-danger">
                    </div>
                </div>



                <!-- END PAGE CONTENT INNER -->
            </div>
        </div>
        <!-- END CONTENT -->
    </div>
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
<script>
    $(function() {
        $('#list-tickets').DataTable({
            processing: true,
            serverSide: true,
            bStateSave: true,
            ajax: {"url": '{{url("admin/rating-review-data/$user_id")}}', "complete": afterRequestComplete},
            columnDefs: [{
                    "defaultContent": "-",
                    "targets": "_all"
                }],
            columns: [
                {data: "id",
                    render: function(data, type, row) {

                        if (type === 'display') {

                            return '<div class="cust-chqs">  <p> <input class="checkboxes" type="checkbox"  id="delete' + row.id + '" name="delete' + row.id + '" value="' + row.id + '"><label for="delete' + row.id + '"></label> </p></div>';
                        }
                        return data;
                    },
                    "orderable": false,
                },
                {data: 'order_unique_id', name: 'order_unique_id'},
                {data: 'from_name', name: 'from_name'},
                {data: 'to_name', name: 'to_name'},
                {data: 'rating', render: function(data, type, row) {
                        if (type === 'display') {
                            return '<div id="hearts-existing" class="starrr" data-rating="' + data + '"></div>';
                        }
                        return data;
                    },
                    "orderable": false, name: 'rating'},
                {data: 'status', name: 'status'},
                {data: "Action",
                    render: function(data, type, row) {

                        if (type === 'display') {
                            @if($user_id!='' && $user_id!='0')
                            {
                                return '<a href="{!!url("/admin/rating-review/edit/' + row.id + '/$user_id")!!}" class="btn btn-sm btn-danger">Edit</a>' + ' ' + '<a href="{!!url("/admin/rating-review/view/' + row.id + '/$user_id")!!}" class="btn btn-sm btn-success">View</a>';
                            }@else{
                                return '<a href="{!!url("/admin/rating-review/edit/' + row.id + '")!!}" class="btn btn-sm btn-danger">Edit</a>' + ' ' + '<a href="{!!url("/admin/rating-review/view/' + row.id + '")!!}" class="btn btn-sm btn-success">View</a>';
                            }
                           @endif; 
                        }
                        return data;
                    },
                    "orderable": false,
                    name: 'Delete'

                }
            ]});

        $('#list-tickets').on('draw.dt', function() {
            $(".starrr").starrr()
        })
    });

    $('#list-tickets').on('draw.dt', function() {
        $(".starrr").starrr()
    })
</script>
@endsection
