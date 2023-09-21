@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Manage Agent users</title>

@endsection

    
@section('content')
<script>
      function changeStatus(user_id, user_status)
            {
                /* changing the user status*/
                var obj_params = new Object();
                obj_params.user_id = user_id;
                obj_params.user_status = user_status;
                if (user_status == 1)
                { 
                   
                    $("#active_div" + user_id).css('display', 'inline-block');
                    $("#active_div_block" + user_id).css('display', 'inline-block');
                    $("#blocked_div" + user_id).css('display', 'none');
                    $("#blocked_div_block" + user_id).css('display', 'none');
                    $("#inactive_div" + user_id).css('display', 'none');
                }
                jQuery.post("{{url('admin/change_status')}}", obj_params, function (msg) {
                    if (msg.error == "1")
                    {
                        alert(msg.error_message);
                        $("#active_div" + user_id).css('display', 'none');
                        $("#active_div_block" + user_id).css('display', 'none');
                        $("#inactive_div" + user_id).css('display', 'block');
                    }
                    else
                    {
                        
                        /* toogling the bloked and active div of user*/
                        if (user_status == 1)
                        { 
                            alert(msg.message);
                            $("#active_div" + user_id).css('display', 'inline-block');
                            $("#active_div_block" + user_id).css('display', 'inline-block');
                            $("#blocked_div" + user_id).css('display', 'none');
                            $("#blocked_div_block" + user_id).css('display', 'none');
                            $("#inactive_div" + user_id).css('display', 'none');
                        }
                        else if(user_status == 0)
                        { 
                             $("#active_div" + user_id).css('display', 'inline-block');
                             $("#active_div_block" + user_id).css('display', 'inline-block');
                            $("#blocked_div" + user_id).css('display', 'none');
                            $("#blocked_div_block" + user_id).css('display', 'none');
                            $("#inactive_div" + user_id).css('display', 'none');
                            
                        }else{
                            $("#active_div" + user_id).css('display', 'none');
                            $("#active_div_block" + user_id).css('display', 'none');
                            $("#blocked_div" + user_id).css('display', 'inline-block');
                            $("#blocked_div_block" + user_id).css('display', 'inline-block');
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
					<a href="javascript:void(0)">Manage Agent Users</a>
					
				</li>
                        </ul>
    
           @if (session('update-user-status'))
          <div class="alert alert-success">
                {{ session('update-user-status') }}
          </div>
         @endif
           @if (session('profile-updated'))
          <div class="alert alert-success">
                {{ session('profile-updated') }}
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
								<i class="glyphicon glyphicon-globe"></i>Manage Agent Users
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
									<div class="col-md-2">
										<div class="btn-group">
											<a href="{{url('admin/create-agent-user')}}" id="sample_editable_1_new" class="btn green">
											Add New <i class="fa fa-plus"></i>
											</a>
										</div>
									</div>
									<div class="col-md-2">
                                                                            
                                                                           @if(Auth::user()->userInformation->user_type=='1')  
                                                                            <div class="form-group">
                                                                                <select onchange="getAllStates(this.value);" class="form-control" id="country"  name="country">
                                                                               <option value="">--Country--</option>
                                                                               @foreach($all_countries as $key=>$name)
                                                                                   @if($name->id!='17')
                                                                                    <option value="{{$name->id}}">{{$name->translate()->name}}</option>
                                                                                   @endif 
                                                                               @endforeach
                                                                           </select>
                                                                           </div>
                                                                            @endif  
                                                                        </div>
                                                                    <div class="col-md-2">
                                                                            @if(Auth::user()->userInformation->user_type=='1')  
                                                                            <div class="form-group">
                                                                            <select onchange="getAllCities(this.value);" class="form-control" id="state"  name="state">
                                                                               <option value="">--Region--</option>
                                                                              
                                                                           </select>
                                                                           </div>
                                                                            @endif  
                                                                        </div>
                                                                    <div class="col-md-3">
                                                                            @if(Auth::user()->userInformation->user_type=='1')  
                                                                            <div class="form-group">
                                                                            <select class="form-control" id="city"  name="city">
                                                                               <option value="">--City--</option>
                                                                              
                                                                           </select>
                                                                           </div>
                                                                            @endif  
                                                                        </div>
                                                                     <div class="col-md-3">
                                                                           
                                                                          <button type="button" class="btn btn-primary" id="btn_search" name="btn_search">Search</button>                                
                                                                        </div>
								</div>
							</div>
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
                                                                 <th>Is Blocked?</th>
                                                                 <th>Registered On</th>
                                                                  <th>Driver</th>
                                                                  <th>Update</th>
                                                                  <!--<th>Wallet</th>-->
                                                                  <th>Delete</th>
                                                        </tr>
							</thead>
                                                        </table>
                                                       <input type="button" onclick='javascript:deleteAll("{{url('/admin/delete-agent-selected-user')}}")' name="delete" id="delete" value="Delete Selected" class="btn btn-danger">              						</div>
					</div>
	
		</div>
	</div>
<script>
//$("#country").on("change", function() {
//
//    initDatatable();
//});
//$("#state").on("change", function() {
//
//    initDatatable();
//});
//$("#city").on("change", function() {
//
//    initDatatable();
//});
$("#btn_search").on("click", function() {

    initDatatable();
});
function initDatatable()
{
      $("#tbladminusers").dataTable().fnDestroy();
    $('#tbladminusers').DataTable({
        processing: true,
        serverSide: true,
        bStateSave: true,
        order: [ [0, 'desc'] ],
        ajax: {"url":'{{url("/admin/agent-users-data")}}',
            "complete":afterRequestComplete,
             "data": function(d)
            {
                d.search_country = $("#country").val();
                d.search_state = $("#state").val();
                d.search_city = $("#city").val();

            }
        },
        columnDefs: [{
        "defaultContent": "-",
        "targets": "_all",
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
            { data: 'user_id', name: 'user_id'},
            { data: 'first_name', name: 'first_name',searchable: true},
            { data: 'last_name', name: 'last_name',searchable: true},
            { data: 'email', name: 'user.email',searchable: true},
            { data: 'location', name: 'location',searchable: true},
              { data: 'status', name: 'status'},
             { data: 'blocked', name: 'blocked'},
           { data: 'created_at', name: 'created_at' },
           
            {data:   "Driver",
              render: function ( data, type, row ) {
               
                    if ( type === 'display' ) {
                        
                        return '<a class="btn btn-sm btn-primary" href="{{url("admin/view-agent-stars/")}}/'+row.user_id+'">Driver</a>';
                    }
                    return data;
                },
                  "orderable": false,
                  name: 'Action'
                  
            },
            {data:   "Update",
              render: function ( data, type, row ) {
               
                    if ( type === 'display' ) {
                        
                        return '<a class="btn btn-sm btn-primary" href="{{url("admin/update-agent-user/")}}/'+row.user_id+'">Update</a>';
                    }
                    return data;
                },
                  "orderable": false,
                  name: 'Action'
                  
            },
//            {data:   "Wallet",
//              render: function ( data, type, row ) {
//               
//                    if ( type === 'display' ) {
//                        
//                        return '<a class="btn btn-sm btn-warning" href="{{url("admin/wallet-history/")}}/'+row.user_id+'">Wallet</a>';
//                    }
//                    return data;
//                },
//                  "orderable": false,
//                  name: 'Action'
//                  
//            }, 
             
            {data:   "Delete",
              render: function ( data, type, row ) {
               
                    if ( type === 'display' ) {
                        
                        return '<form id="delete_user_'+row.user_id+'" method="post" action="{{url("/admin/delete-agent-user")}}/'+row.user_id+'">{{ method_field("DELETE") }} {!! csrf_field() !!}<button onclick="confirmDelete('+row.user_id+')" class="btn btn-sm btn-danger" type="button">Delete</button></form>';
                    }
                    return data;
                },
                  "orderable": false,
                  name: 'Action'
                  
            },
             
             
               
        ]
    });
}
$(function() {
    $('#tbladminusers').DataTable({
        processing: true,
        serverSide: true,
        bStateSave: true,
        order: [ [0, 'desc'] ],
        ajax: {"url":'{{url("/admin/agent-users-data")}}',"complete":afterRequestComplete},
        columnDefs: [{
        "defaultContent": "-",
        "targets": "_all",
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
            { data: 'user_id', name: 'user_id'},
            { data: 'first_name', name: 'first_name',searchable: true},
            { data: 'last_name', name: 'last_name',searchable: true},
            { data: 'email', name: 'user.email',searchable: true},
            { data: 'location', name: 'location',searchable: true},
              { data: 'status', name: 'status'},
             { data: 'blocked', name: 'blocked'},
           { data: 'created_at', name: 'created_at' },
           
            {data:   "Driver",
              render: function ( data, type, row ) {
               
                    if ( type === 'display' ) {
                        
                        return '<a class="btn btn-sm btn-primary" href="{{url("admin/view-agent-stars/")}}/'+row.user_id+'">Driver</a>';
                    }
                    return data;
                },
                  "orderable": false,
                  name: 'Action'
                  
            },
            {data:   "Update",
              render: function ( data, type, row ) {
               
                    if ( type === 'display' ) {
                        
                        return '<a class="btn btn-sm btn-primary" href="{{url("admin/update-agent-user/")}}/'+row.user_id+'">Update</a>';
                    }
                    return data;
                },
                  "orderable": false,
                  name: 'Action'
                  
            },
//            {data:   "Wallet",
//              render: function ( data, type, row ) {
//               
//                    if ( type === 'display' ) {
//                        
//                        return '<a class="btn btn-sm btn-warning" href="{{url("admin/wallet-history/")}}/'+row.user_id+'">Wallet</a>';
//                    }
//                    return data;
//                },
//                  "orderable": false,
//                  name: 'Action'
//                  
//            }, 
             
            {data:   "Delete",
              render: function ( data, type, row ) {
               
                    if ( type === 'display' ) {
                        
                        return '<form id="delete_user_'+row.user_id+'" method="post" action="{{url("/admin/delete-agent-user")}}/'+row.user_id+'">{{ method_field("DELETE") }} {!! csrf_field() !!}<button onclick="confirmDelete('+row.user_id+')" class="btn btn-sm btn-danger" type="button">Delete</button></form>';
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
         
function getAllStates(country_id)
{
    if(country_id!='' && country_id!=0)
    {
        $("#state").html('');
        $("#city").html('');
        $.ajax({
           url:"{{url('/admin/states/getAllStatesRegistration')}}/"+country_id,
           method:'get',
           success:function(data)
           {

                $("#state").html(data);

           }

        });
    }
}
function getAllCities(state_id)
{
    if(state_id!='' && state_id!=0)
    {
         $("#city").html('');
       var country_id=$("#country").val();
        $.ajax({
           url:"{{url('/admin/cities/getAllCitiesStar')}}/"+country_id+"/"+state_id,
           method:'get',
           success:function(data)
           {

                $("#city").html(data);

           }

        });
    }
}
</script>
@endsection
