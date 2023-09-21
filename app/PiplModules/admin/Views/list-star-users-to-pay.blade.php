@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Drivers to Pay</title>

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
        @if (session('mobile_exist'))
            <div class="alert alert-danger">
                  {{ session('mobile_exist') }}
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
                                                        <!--<div class="pull-right" style="color: red;">Total Amount : </strong></label>  <span id="total_order" style="font-size: 18px">{{$total_pending_amount}}</span></div>-->
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
							<table class="table table-striped table-bordered table-hover" id="tbladminusers">
							<thead>
							<tr>
								<th>
									<div class="cust-chqs">  <p><input type="checkbox" id="select_all_delete" ><label for="select_all_delete"></label>  </p></div>
                                                                </th>
                                                                 <th>Id</th>
                                                                 <th>First Name</th>
                                                                 <th>Last Name</th>
                                                                 <th>Mobile</th>
                                                                 <th>Rating</th>
                                                                 <!--<th>Pending Amount</th>-->
                                                                 <th>Location</th>
                                                                 <th>Status</th>
                                                                 <th>Wallet Amount</th>
                                                                 <!--<th>Having Active Order?</th>-->
                                                                 <th>Device</th>
                                                                 <th>Registered On</th>
                                                                 
                                                        </tr>
							</thead>
                                                        </table>
                                                       <input type="button" onclick='javascript:deleteAll("{{url('/admin/delete-star-selected-user')}}")' name="delete" id="delete" value="Delete Selected" class="btn btn-danger">              						
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
     $("#filter_type").on("change", function() {
               initDatatable();
            });
            $("#order_country").on("change", function() {

                initDatatable();
            });
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
                        ajax: {"url":'{{url("/admin/star-users-data-to-pay")}}',
                            "complete":afterRequestComplete,
                            "data": function(d)
                            {
                                    d.order_filter_by = $("#filter_type").val();
                                    d.order_country = $("#order_country").val();
                                    d.start_date = $("#start_date").val();
                                    d.end_date = $("#end_date").val();
                                    d.country_name = '{{Request::segment(3)}}';
                            }
                        },
                        
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
                            {data: 'user_id', name: 'user_id'},
                            {data: 'first_name', name: 'first_name',searchable: true},
                            {data: 'last_name', name: 'last_name',searchable: true},
                            {data: 'username', name: 'user.username',searchable: true},
                            {data: 'rating', render: function(data, type, row) {
                                if (type === 'display') {
                                   return '<div id="hearts-existing" class="starrr" data-rating="' + data + '"></div>';
                                 }
                                 return data;
                                 },
                                 "orderable": false, name: 'rating'
                            },
//                            {data: 'pending_amount', name: 'pending_amount',searchable: true},
                            {data: 'location', name: 'location',searchable: true},
                            {data: 'status', name: 'status'},
                            {data: 'wallet_amount', name: 'wallet_amount'},
//                            {data: 'having_active_order', name: 'having_active_order'},
                            {data: 'device', name: 'device'},
                            {data: 'created_at', name: 'created_at'}
                        ]
            });
                    
       }
$(function() {
    $('#tbladminusers').DataTable({
        processing: true,
        serverSide: true,
        bStateSave: true,
        ajax: {"url":'{{url("/admin/star-users-data-to-pay")}}',"complete":afterRequestComplete},
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
            {data: 'user_id', name: 'user_id'},
            {data: 'first_name', name: 'first_name',searchable: true},
            {data: 'last_name', name: 'last_name',searchable: true},
            {data: 'user_mobile', name: 'user_mobile',searchable: true},
            {data: 'rating', render: function(data, type, row) {
                    if (type === 'display') {
                       return '<div id="hearts-existing" class="starrr" data-rating="' + data + '"></div>';
                     }
                     return data;
                     },
                     "orderable": false, name: 'rating'
            },
            {data: 'location', name: 'location',searchable: true},
            {data: 'status', name: 'status'},
            {data: 'wallet_amount', name: 'wallet_amount'},
            {data: 'device', name: 'device'},
            {data: 'created_at', name: 'created_at'}
        ]
    });
     $('#tbladminusers').on('draw.dt', function() {
            $(".starrr").starrr()
     })
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
