@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Manage Customers</title>

@endsection
    
@section('content')
<script type="text/javascript"  src="{{url('public/media/backend/js/star-rate.js')}}"></script>
<script type="text/javascript"  src="{{url('public/media/backend/js/jquery-ui.min.js')}}"></script>
<link rel="stylesheet" type="text/css" href="{{url('public/media/backend/css/datepicker/jquery-ui.css')}}">
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
					<a href="javascript:void(0)">Manage Customers</a>
					
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
								<i class="glyphicon glyphicon-globe"></i>Manage Customers
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
							  <form class="navbar-form navbar-left" id="frm_filter" name="frm_filter" method="POST">
                                                          <div class="row" class="col-md-12">
                                                                <div class="col-md-1">
                                                                        <div class="btn-group">
                                                                                <a href="{{url('admin/create-registered-user')}}" id="sample_editable_1_new" class="btn green">
                                                                                Add <i class="fa fa-plus"></i>
                                                                                </a>
                                                                        </div>
                                                                </div>
                                                                
                                                                <div class="col-md-2">
                                                                   
                                                                 
                                                                    <select class="form-control" name="filter_type" id="filter_type">
                                                                        <option value="">Select A  Status</option>
                                                                        <option value="1">Active</option>
                                                                        <option value="0">Inactive</option>
                                                                        <option value="2">Blocked</option>
                                                                    </select> 
                                                                 </div>
                                                                <div class="col-md-2">
                                                                    @if(Request::segment(3)=="")
                                                                    <select class="form-control" id="order_country"  name="order_country">
                                                                        <option value="">--Select Country--</option>
                                                                        @foreach($all_countries as $key=>$name)
                                                                        <option value="{{str_replace("+","",$name->country_code)}}">{{$name->translate()->name}}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    @endif
                                                                </div>
                                                              
                                                               <div class="col-md-7">
                                                                 <form class="navbar-form navbar-left" id="frm_filter" name="frm_filter" method="POST">
                                                                    
                                                                            
                                                                            <div class="form-group" style="margin-left: 28px;">
                                                                                <input type="text" class="form-control" id="start_date" name="start_date" placeholder="Star Date">
                                                                            </div>
                                                                            <div class="form-group">
                                                                                <input type="text" class="form-control" id="end_date" name="end_date" placeholder="End Date">
                                                                            </div>
                                                                            <button type="button" class="btn btn-primary" id="btn_search" name="btn_search">Search</button>                                

                                                              </form>
                                                                    </div>
                                                                <div class="clearfix"></div>

                                                            </div>
                                                        </form>
						</div>	
							<table class="table table-striped table-bordered table-hover" id="tbl_regusers">
							 <thead>
							  <tr>
								 <th>
									<div class="cust-chqs">  <p><input type="checkbox" id="select_all_delete" ><label for="select_all_delete"></label>  </p></div>
                                                                 </th>
                                                                 <th>Id</th>
                                                                 <th>First</th>
                                                                 <th>Last Name</th>
                                                                 <th>Mobile</th>
                                                                 <!--<th>Civil ID</th>-->
                                                                 <th>Location</th>
                                                                 <th>Status</th>
                                                                 <th>Rating</th>
                                                                 <th>Is Blocked?</th>
                                                                 <th>Registered On</th>
                                                                 <th>Update</th>
                                                                 <th>Ratings</th>
                                                                 <th>Wallet</th>
                                                                 <th>Delete</th>
                                                         </tr>
							</thead>
                                                       </table>
                                                       <input type="button" onclick='javascript:deleteAll("{{url('/admin/delete-selected-user')}}")' name="delete" id="delete" value="Delete Selected" class="btn btn-danger">
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
//     $("#filter_type").on("change", function() {
//               initDatatable();
//            });
//            $("#order_country").on("change", function() {
//
//                initDatatable();
//            });
            $("#btn_search").on("click", function() {
                initDatatable();
    });
    function initDatatable()
            {
                  $("#tbl_regusers").dataTable().fnDestroy();
                   $('#tbl_regusers').DataTable({
                    processing: true,
                    serverSide: true,
                    bStateSave: true,
                    ajax: {"url":'{{url("/admin/list-registered-users-data")}}',
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
                          render: function ( data, type, row ) {

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
                        { data: 'username', name: 'user.username',searchable: true},
//                        { data: 'civil_id', name: 'civil_id',searchable: true},
                        { data: 'location', name: 'location',searchable: true},
                        { data: 'status', name: 'status'},
                        {data: 'rating', render: function(data, type, row) {
                         if (type === 'display') {
                            return '<div id="hearts-existing" class="starrr" data-rating="' + data + '"></div>';
                          }
                          return data;
                          },
                          "orderable": false, name: 'rating'
                        },
                        {data: 'blocked', name: 'blocked'},
                        { data: 'created_at', name: 'user.created_at' },

                        {data:   "Update",
                          render: function ( data, type, row ) {

                                if ( type === 'display' ) {

                                    return '<a class="btn btn-sm btn-primary" href="{{url("admin/update-registered-user/")}}/'+row.user_id+'">Update</a>';
                                }
                                return data;
                            },
                              "orderable": false,
                              name: 'Action'

                        },
                        {data:   "Ratings",
                            render: function ( data, type, row ) {

                                  if ( type === 'display' ) {

                                      return '<a class="btn btn-sm btn-success" href="{{url("admin/rating-review/list")}}/'+row.user_id+'">Ratings</a>';
                                  }
                                  return data;
                              },
                                "orderable": false,
                                name: 'Action'

                          },
                        {data:   "Wallet",
                          render: function ( data, type, row ) {

                                if ( type === 'display' ) {

                                    return '<a class="btn btn-sm btn-warning" href="{{url("admin/wallet-history/")}}/'+row.user_id+'">Wallet</a>';
                                }
                                return data;
                            },
                              "orderable": false,
                              name: 'Action'

                        }, 

                        {data:   "Delete",
                          render: function ( data, type, row ) {

                                if ( type === 'display' ) {

                                    return '<form id="delete_user_'+row.user_id+'" method="post" action="{{url("/admin/delete-user")}}/'+row.user_id+'">{{ method_field("DELETE") }} {!! csrf_field() !!}<button onclick="confirmDelete('+row.user_id+')" class="btn btn-sm btn-danger" type="button">Delete</button></form>';
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
 jQuery.browser = {};
    (function()
    {
        jQuery.browser.msie = false;
        jQuery.browser.version = 0;
        if (navigator.userAgent.match(/MSIE ([0-9]+)\./))
        {
            jQuery.browser.msie = true;
            jQuery.browser.version = RegExp.$1;
        }
    })();
    //For Start Date Calender:
    $("#start_date").datepicker({
        dateFormat: "yy-mm-dd",
        //minDate: 0,
        onSelect: function(date) {
            var date2 = $('#start_date').datepicker('getDate');
            date2.setDate(date2.getDate() + 1);
            $('#end_date').datepicker('setDate', date2);
            $('#end_date').datepicker('option', 'minDate', date2);
        }
    });
    //For End Date Calender:
    $('#end_date').datepicker({
        dateFormat: "yy-mm-dd",
        onClose: function() {
            var dt1 = $('#start_date').datepicker('getDate');
            console.log(dt1);
            var dt2 = $('#end_date').datepicker('getDate');
            if (dt2 <= dt1) {
                var minDate = $('#end_date').datepicker('option', 'minDate');
                $('#end_date').datepicker('setDate', minDate);
            }
        }
    });
    $('#tbl_regusers').DataTable({
        processing: true,
        serverSide: true,
        bStateSave: true,
        ajax: {"url":'{{url("/admin/list-registered-users-data")}}',"complete":afterRequestComplete},
        columnDefs: [{
        "defaultContent": "-",
        "targets": "_all"
      }],
        columns: [
            {data:   "id",
              render: function ( data, type, row ) {
                
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
            { data: 'user_mobile', name: 'user_mobile',searchable: true},
//            { data: 'civil_id', name: 'civil_id',searchable: true},
//            { data: 'email', name: 'user.email',searchable: true},
  { data: 'location', name: 'location',searchable: true},
                      
            { data: 'status', name: 'status'},
            { data: 'rating', name: 'rating'},
//             {data: 'rating', render: function(data, type, row) {
//                         if (type === 'display') {
//                            return '<div id="hearts-existing" class="starrr" data-rating="' + data + '"></div>';
//                          }
//                          return data;
//                          },
//                          "orderable": false, name: 'rating'
//            },
            { data: 'blocked', name: 'blocked'},
            { data: 'created_at', name: 'user.created_at' },
       
            {data:   "Update",
              render: function ( data, type, row ) {
               
                    if ( type === 'display' ) {
                        
                        return '<a class="btn btn-sm btn-primary" href="{{url("admin/update-registered-user/")}}/'+row.user_id+'">Update</a>';
                    }
                    return data;
                },
                  "orderable": false,
                  name: 'Action'
                  
            },
            {data:   "Ratings",
              render: function ( data, type, row ) {
               
                    if ( type === 'display' ) {
                        
                        return '<a class="btn btn-sm btn-success" href="{{url("admin/rating-review/list")}}/'+row.user_id+'">Ratings</a>';
                    }
                    return data;
                },
                  "orderable": false,
                  name: 'Action'
                  
            },
            {data:   "Wallet",
              render: function ( data, type, row ) {
               
                    if ( type === 'display' ) {
                        
                        return '<a class="btn btn-sm btn-warning" href="{{url("admin/wallet-history/")}}/'+row.user_id+'">Wallet</a>';
                    }
                    return data;
                },
                  "orderable": false,
                  name: 'Action'
                  
            }, 
             
            {data:   "Delete",
              render: function ( data, type, row ) {
               
                    if ( type === 'display' ) {
                        
                        return '<form id="delete_user_'+row.user_id+'" method="post" action="{{url("/admin/delete-user")}}/'+row.user_id+'">{{ method_field("DELETE") }} {!! csrf_field() !!}<button onclick="confirmDelete('+row.user_id+')" class="btn btn-sm btn-danger" type="button">Delete</button></form>';
                    }
                    return data;
                },
                  "orderable": false,
                  name: 'Action'
                  
            }
               
        ]
    });
     $('#tbl_regusers').on('draw.dt', function() {
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
