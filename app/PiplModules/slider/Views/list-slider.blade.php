@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Manage Tutorials Images</title>

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
					<a href="javascript:void(0)">Manage Tutorials Images</a>
					
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
								<i class="fa fa-list"></i>Manage Tutorials Images
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
											<a href="{{url('/admin/tutorials/create')}}" id="sample_editable_1_new" class="btn green">
											Add New <i class="fa fa-plus"></i>
											</a>
										</div>
									</div>
									
								</div>
							</div>
							<table class="table table-striped table-bordered table-hover" id="list_categories">
							<thead>
							<tr>
								<th>Id</th>
                                                                <th>Title</th>
                                                                <th>Image</th>
                                                                <th>Type</th>
                                                                <th>Updated Date</th>
                                                                <th>Update</th>                                                                
                                                                <th>Delete</th>
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
$(function() {
    $('#list_categories').DataTable({
        processing: true,
        serverSide: true,
        ajax: {"url":'{{url("/admin/tutorials-list-data")}}',"complete":afterRequestComplete},
        columnDefs: [{
        "defaultContent": "-",
        "targets": "_all"
      }],
        columns: [
          
           { data: 'id', name: 'id'},
           { data: 'title', name: 'title'},
           { data: 'image', name: 'image'},
           { data: 'type', name: 'type'},
            { data: 'updated_at', name: 'updated_at' },
            {data:   "Update",
              render: function ( data, type, row ) {
               
                    if ( type === 'display' ) {
                        
                        return '<a  class="btn btn-sm btn-primary" href="{{url("/admin/tutorials")}}/'+row.id+'">Update</a>';
                    }
                    return data;
                },
                  "orderable": false,
                  name: 'Update'
                  
            },            
            {data:   "Delete",
              render: function ( data, type, row ) {
               
                    if ( type === 'display' ) {
                        
                        return '<form id="category_delete_'+row.id+'"  method="post" action="{{url("/admin/tutorials")}}/'+row.id+'">{{ method_field("DELETE") }} {!! csrf_field() !!}<button onclick="confirmDelete('+row.id+');" class="btn btn-sm btn-danger" type="button">Delete</button></form>';
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
    if(confirm("Do you really want to delete this image?"))
    {
        $("#category_delete_"+id).submit();
    }
    return false;
    }
</script>
@endsection
