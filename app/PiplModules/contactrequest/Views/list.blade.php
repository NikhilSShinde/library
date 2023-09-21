@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Manage Contact Requests</title>

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
					<a href="javascript:void(0)">Manage Contact Requests</a>
					
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
								<i class="fa fa-list"></i>Manage Contact Requests
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
											<a href="{{url('/admin/contact-request-categories')}}" id="sample_editable_1_new" class="btn green">
											Contact Request Categories <i class="fa fa-list"></i>
											</a>
										</div>
									</div>
									
								</div>
							</div>
							<table class="table table-striped table-bordered table-hover" id="list_contacts">
							<thead>
							<tr>
								
                                                                <th>Id</th>
                                                                <th>Name / Email / Phone</th>
                                                                <th>Subject</th>
                                                                <th>Category</th>
                                                                <th>Date</th>
                                                                <th>Replied?</th>
                                                                <th>View</th>
                                                                <!--<th>Delete</th>-->
                                                        </tr>
							</thead>
                                                        </table>
                                                     <!--<input type="button" onclick='javascript:deleteAll("{{url('/admin/contact-request/delete-selected')}}")' name="delete" id="delete" value="Delete Selected" class="btn btn-danger">-->
						</div>
					</div>
	
				
			
			<!-- END PAGE CONTENT INNER -->
		</div>
	</div>
	<!-- END CONTENT -->
</div>
<script>
$(function() {
    $('#list_contacts').DataTable({
        processing: true,
        serverSide: true,
        bStateSave: true,
        ajax: {"url":'{{url("/admin/contact-requests-data")}}',"complete":afterRequestComplete},
        columnDefs: [{
        "defaultContent": "-",
        "targets": "_all"
      }],
        columns: [
           { data: 'id', name: 'id'},
           { data: 'name', name: 'Name / Email / Phone<'},
            { data: 'contact_subject', name: 'subject' },
            { data: 'category', name: 'category' },
            {data: 'created_at', name: 'created_at' },
             {data: 'is_reply', name: 'is_reply' },
            {data:   "View and Reply",
              render: function ( data, type, row ) {
               
                    if ( type === 'display' ) {
                        
                        return '<a  class="btn btn-sm btn-primary" href="{{url("/admin/contact-request/")}}/'+row.reference_no+'">View and Reply</a>';
                    }
                    return data;
                },
                  "orderable": false,
                  name: 'View'
                  
            },
              
//             {data:   "Delete",
//              render: function ( data, type, row ) {
//               
//                    if ( type === 'display' ) {
//                        
//                        return '<form id="contact_delete_'+row.id+'"  method="post" action="{{url("/admin/contact-request/delete/")}}/'+row.id+'">{{ method_field("DELETE") }} {!! csrf_field() !!}<button onclick="confirmDelete('+row.id+');" class="btn btn-sm btn-danger" type="button">Delete</button></form>';
//                    }
//                    return data;
//                },
//                  "orderable": false,
//                  name: 'Delete'
//                  
//            }
             
               
        ]
    });
});
function confirmDelete(id)
{
    if(confirm("Do you really want to delete this contact request?"))
    {
        $("#contact_delete_"+id).submit();
    }
    return false;
    }
</script>
@endsection
