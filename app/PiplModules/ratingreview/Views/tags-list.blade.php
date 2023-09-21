@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Manage Rating Tags</title>

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
                <a href="javascript:void(0)">Manage Rating Tags</a>
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
                            <i class="fa fa-list"></i>Manage Rating Tags
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
                            
                        </div>
                        <table class="table table-striped table-bordered table-hover" id="list-tickets">
                            <thead>
                                <tr>
                                    <th><div class="cust-chqs">  <p><input type="checkbox" id="select_all_delete" ><label for="select_all_delete"></label>  </p></div></th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Rating Star No</th>
                            <th>Language</th>
                            <th>Status</th>
                            <th>Action</th>
                            </tr>
                            </thead>
                        </table>
                        <!--<input type="button" onclick='javascript:deleteAll("{{url('/admin/rating-review-tag/delete-selected')}}")' name="delete" id="delete" value="Delete Selected" class="btn btn-danger">-->
                    </div>
                </div>
            </div>
        </div>
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
    $(document).ready(function() {
        $('#list-tickets').DataTable({
            processing: true,
            serverSide: true,
            ajax: {"url": '{{url("admin/rating-tags-data")}}', "complete": afterRequestComplete},
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
                {data: 'ques_title', name: 'ques_title'},
                {data: 'ques_desc', name: 'ques_desc'},
                {data: 'rating_star_no', render: function(data, type, row) {
                        if (type === 'display') {
                            return '<div id="hearts-existing" class="starrr" data-rating="' + data + '"></div>';
                        }
                        return data;
                    },
                    "orderable": false,
                    name: 'rating_star_no'},
                { data: 'Language', name: 'Language'},
                {data: 'status', name: 'status'},
                {data: "Delete",
                    render: function(data, type, row) {
                        if (type === 'display') {
                            return '<a href="{!!url("/admin/rating-review/edit-tags/' + row.id + '")!!}" class="btn btn-sm btn-danger">Edit</a>';
                        }
                        return data;
                    },
                    "orderable": false,
                    name: 'Delete'

                }
            ]});

        setTimeout(function() {
            $(".starrr").starrr()
        }, 200)
    });

    $('#list-tickets').on('draw.dt', function() {
        $(".starrr").starrr()
    })
</script>
@endsection
