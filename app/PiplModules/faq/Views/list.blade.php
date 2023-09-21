@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Manage FAQ's</title>

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
					<a href="javascript:void(0)">Manage FAQ's</a>
					
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
								<i class="fa fa-list"></i>Manage FAQ's
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
											<a href="{{url('/admin/faqs/create')}}" id="sample_editable_1_new" class="btn green">
											Add New <i class="fa fa-plus"></i>
											</a>
										</div>
									</div>
									
								</div>
							</div>
							<table class="table table-striped table-bordered table-hover" id="list-categories">
							<thead>
							<tr>
								<th>
									<div class="cust-chqs">  <p><input type="checkbox" id="select_all_delete" ><label for="select_all_delete"></label>  </p></div>
                                                                </th>
                                                                <th>Id</th>
                                                                <th>Question</th>
                                                                <th>Answer</th>
                                                                <th>Category</th>
                                                                <th>Date</th>
                                                                <th>Update</th>
                                                                <th>Language</th>
                                                                <th>Delete</th>
                                                        </tr>
							</thead>
                                                        </table>
                                                          <input type="button" onclick='javascript:deleteAll("{{url('/admin/faq/delete-selected')}}")' name="delete" id="delete" value="Delete Selected" class="btn btn-danger">
						</div>
					</div>
	
				
			
			<!-- END PAGE CONTENT INNER -->
		</div>
	</div>
	<!-- END CONTENT -->
</div>
<script>
$(function() {
    $('#list-categories').DataTable({
        processing: true,
        serverSide: true,
        bStateSave: true,
        ajax: {"url":'{{url("/admin/faq-data")}}',"complete":afterRequestComplete},
        columnDefs: [{
        "defaultContent": "-",
        "targets": "_all"
      }],
        columns: [
            {data:   "id",
              render: function ( data, type, row ) {
                
                    if ( type === 'display' ) {
                        
                       return '<div class="cust-chqs">  <p> <input class="checkboxes" type="checkbox"  id="delete'+row.id+'" name="delete'+row.id+'" value="'+row.id+'"><label for="delete'+row.id+'"></label> </p></div>';
                    }
                    return data;
                },
                  "orderable": false,
                  
               },
           { data: 'id', name: 'id'},
           { data: 'question', name: 'question'},
           { data: 'answer', name: 'answer'},
           { data: 'category', name: 'category'},
            { data: 'created_at', name: 'created_at' },
            {data:   "Update",
              render: function ( data, type, row ) 
              {
               
                    if ( type === 'display' ) {
                        
                        return '<a  class="btn btn-sm btn-primary" href="{{url("/admin/faq")}}/'+row.id+'">Update</a>';
                    }
                    return data;
                },
                  "orderable": false,
                  name: 'Update'
                  
            },
             {data:   "Language","orderable": false,name: 'Update'},
             {data:   "Delete",
              render: function ( data, type, row ) {
               
                    if ( type === 'display' ) {
                        
                        return '<form id="category_delete_'+row.id+'"  method="post" action="{{url("/admin/faq/delete/")}}/'+row.id+'">{{ method_field("DELETE") }} {!! csrf_field() !!}<button onclick="confirmDelete('+row.id+');" class="btn btn-sm btn-danger" type="button">Delete</button></form>';
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
    if(confirm("Do you really want to delete this category?"))
    {
        $("#category_delete_"+id).submit();
    }
    return false;
    }
</script>
@endsection
