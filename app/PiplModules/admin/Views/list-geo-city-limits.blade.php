@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Manage City GEO Settings</title>

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
					<a href="javascript:void(0)">Manage Cities GEO Settings </a>
					
				</li>
                        </ul>
    
            @if (session('country-status'))
              <div class="alert alert-success">
                {{ session('country-status') }}
            </div>
         @endif
         
    @if (session('update-country-status'))
          <div class="alert alert-success">
                {{ session('update-country-status') }}
          </div>
    @endif
        <div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="glyphicon glyphicon-globe"></i>Manage Cities GEO Settings
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
											<a href="{{url('admin/create-geo-city-setting/create')}}" id="sample_editable_1_new" class="btn green">
											Add New <i class="fa fa-plus"></i>
											</a>
										</div>
									</div>
									
								</div>
							</div>
							<table class="table table-striped table-bordered table-hover" id="tbl_countries">
							<thead>
							<tr>
<!--								<th>
									<div class="cust-chqs">  <p><input type="checkbox" id="select_all_delete" ><label for="select_all_delete"></label>  </p></div>
								</th>-->
                                                                  <th>Id</th>
                                                                  <th>City</th>
                                                                  <th>Location 1</th>
                                                                  <th>Location 2</th>
                                                                  <th>Last Updated On</th>
                                                                  <th>Update</th>
                                                                  <th>Delete</th>
                                                                  
                                                        </tr>
							</thead>
                                                        </table>
                                                        <!--<input type="button" onclick='javascript:deleteAll("{{url('/admin/countries/delete-selected')}}")' name="delete" id="delete" value="Delete Selected" class="btn btn-danger">-->
						</div>
					</div>
	
		</div>
	</div>
	</div>
<script>
$(function() {
    $('#tbl_countries').DataTable({
        processing: true,
        serverSide: true,
        bStateSave: true,
        ajax: {"url":'{{url("/admin/city-geo-settings-data/list")}}',"complete":afterRequestComplete},
        columnDefs: [{
        "defaultContent": "-",
        "targets": "_all"
      }],
        columns: [
//            {data:   "id",
//              render: function ( data, type, row ) {
//                
//                      if ( type === 'display' ) {
//                        
//                        return '<div class="cust-chqs">  <p> <input class="checkboxes" type="checkbox"  id="delete'+row.id+'" name="delete'+row.id+'" value="'+row.id+'"><label for="delete'+row.id+'"></label> </p></div>';
//                    }
//                    return data;
//                },
//                  "orderable": false,
//                  
//               },
            { data: 'id', name: 'id'},
           { data: 'city', name: 'city',searchable: true},
           { data: 'location1', name: 'location1',searchable: true},
           { data: 'location2', name: 'location2' },
            { data: 'updated_at', name: 'updated_at' },
            {data:   "Action",
              render: function ( data, type, row ) {
               
                    if ( type === 'display' ) {
                        
                        return '<a class="btn btn-sm btn-primary" href="{{url("admin/geo-city-setting/update/")}}/'+row.id+'">Update</a>';
                    }
                    return data;
                },
                  orderable: false,
                  name: 'Action'
                  
            },
 
            {data:   "Delete",
              render: function ( data, type, row ) {
               
                    if ( type === 'display' ) {
                        
                        return '<form id="delete_country_'+row.id+'"  method="post" action="{{url("/admin/geo-city-setting/delete")}}/'+row.id+'">{{ method_field("DELETE") }} {!! csrf_field() !!}<button onclick="confirmDelete('+row.id+');" class="btn btn-sm btn-danger" type="button">Delete</button></form>';
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
    if(confirm("Do you really want to delete this record?"))
    {
        $("#delete_country_"+id).submit();
    }
    return false;
    }
</script>
@endsection
