@extends(config("piplmodules.back-view-layout-location"))
@section("meta")
<title>Manage Global Settings</title>
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
					<a href="javascript:void(0);">Manage Global settings</a>
					
				</li>
                        </ul>
                @if (session('global-setting-status'))
                  <div class="alert alert-success">
                        {{ session('global-setting-status') }}
                  </div>
            @endif
             @if (session('update-setting-status'))
             <div class="alert alert-success">
                       {{ session('update-setting-status') }}
          </div>
          @endif
            <div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="glyphicon glyphicon-cog"></i>Manage Global settings
							</div>
							
						</div>
                                                <div class="portlet-body">
							
							<table class="table table-striped table-bordered table-hover" id="dt_global_settings">
							<thead>
							<tr>
                                                                <th>Id</th>
                                                                <th>Name</th>
                                                                <th>Value</th>
                                                                <th>Updated At</th>
                                                                <th>Action</th>
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
    $('#dt_global_settings').DataTable({
        processing: true,
        serverSide: true,
        bStateSave: true,
        ajax: '{{url("/admin/global-settings-data")}}',
        columnDefs: [{
        "defaultContent": "-",
        "targets": "_all"
      }],
        columns: [
           
           { data: 'id', name: 'id'},
           { data: 'name', name: 'name'},
           {data:   "value",name:'value'},
            { data: 'updated_at', name: 'updated_at' },
            {data:   "Action",
              render: function ( data, type, row ) {
               
                    if ( type === 'display' ) {
                        
                        return '<a class="btn btn-sm btn-primary" href="{{url("admin/update-global-setting")}}/'+row.id+'">Update</a>';
                    }
                    return data;
                },
                  "orderable": false,
                  name: 'Action'
                  
            }
             
             
               
        ]
    });
});
function confirmDelete()
{
    if(confirm("Do you really want to delete this role?"))
    {
        $("#delete_role").submit();
    }
    return false;
    }
</script>
@endsection
