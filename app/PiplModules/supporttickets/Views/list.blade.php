@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Manage Support Ticket's</title>

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
                <a href="javascript:void(0)">Manage Support Ticket's</a>
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
                            <i class="fa fa-list"></i>Manage Support Ticket's
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
                                    <th>
                            <div class="cust-chqs">  <p><input type="checkbox" id="select_all_delete" ><label for="select_all_delete"></label>  </p></div>
                            </th>
                            <th>Added By</th>
                            <!--<th>Subject</th>-->
                            <th>Ticket Id</th>
                            <th>Trip Id</th>
                            <th>Replied?</th>
                            <th>Date</th>
                            <th>Action</th>
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
    <div class="modal fade" id="myTicketModal" role="dialog">
        <div class="modal-dialog">
            <form name="frm_assign_ticket" id="frm_assign_ticket" method="post" action="{{url('/admin/support-ticket/assign-to-agent')}}">
                <div class="modal-content">
                    {{csrf_field()}}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Assign Ticket </h4>
                    </div>
                    <div class="modal-body">
                        <ul class="list-group" id="user_list"></ul>
                        <div for="assign_to" generated="true" class="text-danger"></div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="ticket_id" id="ticket_id" value="">
                        <button type="submit" class="btn btn-success">Submit</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

<script>
    $(function() {
        $('#list-tickets').DataTable({
            processing: true,
            serverSide: true,
            bStateSave: true,
            ajax: {"url": '{{url("/admin/suppor-ticket-data")}}', "complete": afterRequestComplete},
            columnDefs: [{
                    "defaultContent": "-",
                    "targets": "_all"
                }],
            columns: [
//                {data: "id",
//                    render: function(data, type, row) {
//
//                        if (type === 'display') {
//
//                            return '<div class="cust-chqs">  <p> <input class="checkboxes" type="checkbox"  id="delete' + row.id + '" name="delete' + row.id + '" value="' + row.id + '"><label for="delete' + row.id + '"></label> </p></div>';
//                        }
//                        return data;
//                    },
//                    "orderable": false,
//                },
     {data: 'id', name: 'id'},
                {data: 'added_by', name: 'added_by'},
            
//                {data: 'title', name: 'title'},
                {data: 'ticket_unique_id', name: 'ticket_unique_id'},
                {data: 'order_unique_id', name: 'order_unique_id'},
                {data: 'is_reply', name: 'is_reply'},
                {data: 'created_at', name: 'created_at'},
                {data: 'assign_btn', name: 'assign_btn'}

            ]
        });
    });
//    function confirmDelete(id)
//    {
//        if (confirm("Do you really want to delete this category?"))
//        {
//            $("#category_delete_" + id).submit();
//        }
//        return false;
//    }

    function assignTicket(ticket_id) {
        $.ajax({
            url: '{{url("/admin/support-ticket/agent-user")}}',
            data: {
                ticket_id:ticket_id
            },
            dataType: 'json',
            success: function(response) {
                $('#ticket_id').val(ticket_id)
                $('#myTicketModal').modal('show');
                var str = '';
                $.each(response, function(index, value) {
                    if(value.asigned_user_information != null){
                        str += '<li class="list-group-item"><input checked type="radio" name="assign_to" id="assign_to" value="' + value.user_id + '"> <lable>' + value.first_name + ' ' + value.last_name + '</lable> </li>';
                    } else {
                        str += '<li class="list-group-item"><input type="radio" name="assign_to" id="assign_to" value="' + value.user_id+ '"> <lable>' + value.first_name + ' ' + value.last_name + '</lable> </li>';
                    }
                });
                $('#user_list').html(str)
            }
        });
    }
    
    jQuery(document).ready(function() {
        jQuery("#frm_assign_ticket").validate({
            errorClass: 'text-danger',
            errorElement:'div',
            rules: {
                assign_to:{
                    required: true
                }            
            },
            messages: {
                assign_to: {
                    required: "Please select atlest one user."
                }
            }
        });
    });
    
</script>
@endsection
