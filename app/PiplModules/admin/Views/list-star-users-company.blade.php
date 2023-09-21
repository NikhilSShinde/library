@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Manage Drivers</title>

@endsection

    
@section('content')
<script>
      function changeStatus(user_id, user_status)
            {
                /* changing the user status*/
                var obj_params = new Object();
                obj_params.user_id = user_id;
                obj_params.user_status = user_status;
                jQuery.post("{{url('admin/change_status')}}", obj_params, function (msg) {
                    if (msg.error == "1")
                    {
                        alert(msg.error_message);
                    }
                    else
                    {
                        /* toogling the bloked and active div of user*/
                        if (user_status == 1)
                        { 
                            $("#active_div" + user_id).css('display', 'inline-block');
                            $("#blocked_div" + user_id).css('display', 'none');
                            $("#inactive_div" + user_id).css('display', 'none');
                        }
                        else if(user_status == 0)
                        { 
                             $("#active_div" + user_id).css('display', 'inline-block');
                            $("#blocked_div" + user_id).css('display', 'none');
                            $("#inactive_div" + user_id).css('display', 'none');
                            
                        }else{
                            $("#active_div" + user_id).css('display', 'none');
                            $("#blocked_div" + user_id).css('display', 'inline-block');
                            $("#inactive_div" + user_id).css('display', 'none');
                        }
                    }

                }, "json");

            }
        
    </script>
<div class="page-content-wrapper">
		<div class="page-content">
                    <!-- BEGIN PAGE BREADCRUMB -->
			<ul class="page-breadcrumb breadcrumb">
				<li>
					<a href="{{url('admin/dashboard')}}">Dashboard</a>
					<i class="fa fa-circle"></i>
				</li>
				<li>
					<a href="{{url('admin/company-users')}}"> Manage Company users</a>
					<i class="fa fa-circle"></i>
				</li>
				<li>
					<a href="javascript:void(0)">Manage Drivers</a>
					
				</li>
                        </ul>
    
           @if (session('update-user-status'))
          <div class="alert alert-success">
                {{ session('update-user-status') }}
          </div>
         @endif
         
        @if (session('delete-user-status'))
            <div class="alert alert-success">
                  {{ session('delete-user-status') }}
            </div>
      @endif    
    
         <div class="row">
                    <div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="glyphicon glyphicon-globe"></i>Manage Drivers
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
							
                                                     <input type='hidden' name='agent' id='agent' value='{{isset($agent_id)?$agent_id:''}}'>
							<table class="table table-striped table-bordered table-hover" id="tbladminusers">
							<thead>
							<tr>
								<th>
									<div class="cust-chqs">  <p><input type="checkbox" id="select_all_delete" ><label for="select_all_delete"></label>  </p></div>
                                                                </th>
                                                                 <th>Id</th>
                                                                 <th>First Name</th>
                                                                 <th>Last Name</th>
                                                                 <th>Email</th>
                                                                 <th>Location</th>
                                                                 <th>Status</th>
                                                                 <th>Registered On</th>
                                                                  <th>Update</th>
                                                                  <th>Delete</th>
                                                        </tr>
							</thead>
                                                        </table>
                                                       <input type="button" onclick='javascript:deleteAll("{{url('/admin/delete-star-selected-user')}}")' name="delete" id="delete" value="Delete Selected" class="btn btn-danger">              						</div>
					</div>
	
		</div>
	</div>
<script>
$(function() {
    var agent_id=$("#agent").val();
   
    $('#tbladminusers').DataTable({
        processing: true,
        serverSide: true,
        bStateSave: true,
        ajax: {"url":'{{url("/admin/view-agent-stars-data")}}/'+agent_id,"complete":afterRequestComplete},
        columnDefs: [{
        "defaultContent": "-",
        "targets": "_all"
      }],
        columns: [
           
            {data:   "id",
              render: function ( data, type, row ) 
              {
                
                      if ( type === 'display' ) {
                        
                         return '<div class="cust-chqs">  <p> <input class="checkboxes" type="checkbox"  id="delete'+row.user_id+'" name="delete'+row.user_id+'" value="'+row.user_id+'"><label for="delete'+row.user_id+'"></label> </p></div>';
                    }
                    return data;
                },
                  "orderable": false,
                  
               },
            { data: 'id', name: 'id'},
            { data: 'first_name', name: 'first_name',searchable: true},
            { data: 'last_name', name: 'last_name',searchable: true},
            { data: 'email', name: 'user.email',searchable: true},
            { data: 'location', name: 'location',searchable: true},
            { data: 'status', name: 'status'},
           { data: 'created_at', name: 'created_at' },
       
            {data:   "Update",
              render: function ( data, type, row ) {
               
                    if ( type === 'display' ) {
                        
                        return '<a class="btn btn-sm btn-primary" href="{{url("admin/update-star-user/")}}/'+row.user_id+'">Update</a>';
                    }
                    return data;
                },
                  "orderable": false,
                  name: 'Action'
                  
            },
           
             
            {data:   "Delete",
              render: function ( data, type, row ) {
               
                    if ( type === 'display' ) {
                        
                        return '<form id="delete_user_'+row.user_id+'" method="post" action="{{url("/admin/delete-star-user")}}/'+row.user_id+'">{{ method_field("DELETE") }} {!! csrf_field() !!}<button onclick="confirmDelete('+row.user_id+')" class="btn btn-sm btn-danger" type="button">Delete</button></form>';
                    }
                    return data;
                },
                  "orderable": false,
                  name: 'Action'
                  
            },
             
             
               
        ]
    });
});


          
        
        
function confirmDelete(id)
{
    if(confirm("Do you really want to delete this user?"))
    {
        
        $("#delete_user_"+id).submit();
    }
    return false;
    }
</script>
@endsection
