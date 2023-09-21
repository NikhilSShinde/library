@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Manage Payments</title>

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
					<a href="javascript:void(0)">Manage Payments</a>
					
				</li>
                        </ul>
    
        @if (session('update-user-payment'))
          <div class="alert alert-warning">
                {{ session('update-user-payment') }}
          </div>
         @endif
      
        @if (session('delete-user-payment'))
           <div class="alert alert-success">
                  {{ session('delete-user-payment') }}
           </div>
       @endif    
         <div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="glyphicon glyphicon-globe"></i>Manage Payments
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
                                                                <div class="col-md-5">
                                                                        <div class="btn-group">
                                                                                <a href="{{url('admin/create-user-payment')}}" id="sample_editable_1_new" class="btn green">
                                                                                Create A Payment <i class="fa fa-plus"></i>
                                                                                </a>
                                                                        </div>
                                                                </div>
                                                               <div class="col-md-3">
								 <label><strong>Filter By</strong> : </label>  
                                                               </div>  
                                                                <div class="col-md-4">
                                                                   
                                                                 
                                                                    <select class="form-control" name="filter_type" id="filter_type">
                                                                        <option value="">Choose Filter</option>
                                                                        <option value="today">Todays</option>
                                                                        <option value="week">Last 7 Days</option>
                                                                        <option value="month">Current Month</option>
                                                                        <option value="year">Current Year</option>
                                                                    </select> 
                                                                 </div>
                                                               
                                                                <div class="clearfix"></div>

                                                            </div>
                                                        </form>
						</div>	
							<table class="table table-striped table-bordered table-hover" id="tbl_payment">
							 <thead>
							  <tr>
<!--								 <th>
									<div class="cust-chqs">  <p><input type="checkbox" id="select_all_delete" ><label for="select_all_delete"></label>  </p></div>
                                                                 </th>-->
                                                                 <th>Id</th>
                                                                 <th>User Name</th>
                                                                 <th>Payment mode</th>
                                                                 <th>Date</th>
                                                                 <th>Amount</th>
                                                                 <th>Bank name</th>
                                                                 <th>Cheque no.</th>
                                                                 <th>Transaction no.</th>
                                                          
                                                         </tr>
							</thead>
                                                       </table>
                                                       <!--<input type="button" onclick='javascript:deleteAll("{{url('/admin/delete-selected-user-payments')}}")' name="delete" id="delete" value="Delete Selected" class="btn btn-danger">-->
						</div>
					</div>
	
		</div>
	</div>
      
                </div> 
                </div> 
       @if (session('pdf_file_to_download'))      
         <input type="hidden" name="file_name" id="file_name" value="{{url('download-pdf-file-admin')}}/{{session('pdf_file_to_download')}}">
        <?php Session::put('pdf_file_to_download','');?>
       @endif  
<script>
     $("#filter_type").on("change", function() {
               initDatatable();
      });
        
    function initDatatable()
            {
                  $("#tbl_payment").dataTable().fnDestroy();
                     $('#tbl_payment').DataTable({
        processing: true,
        serverSide: true,
        bStateSave: true,
        ajax: {"url":'{{url("/admin/users-payments/list-data")}}',
            "complete":afterRequestComplete,
        "data": function(d)
            {
                    d.order_filter_by = $("#filter_type").val();
            }
        },
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
//                         return '<div class="cust-chqs">  <p> <input class="checkboxes" type="checkbox"  id="delete'+row.id+'" name="delete'+row.id+'" value="'+row.user_id+'"><label for="delete'+row.id+'"></label> </p></div>';
//                    }
//                    return data;
//                },
//                  "orderable": false,
//                  
//             },
            { data: 'id', name: 'id'},
            { data: 'user_name', name: 'user_name'},
            { data: 'payment_mode', name: 'payment_mode',searchable: true},
            { data: 'payment_on', name: 'payment_on',searchable: true},
            { data: 'amount', name: 'amount',searchable: true},
            { data: 'bank_name', name: 'bank_name',searchable: true},
            { data: 'cheque_number', name: 'cheque_number',searchable: true},
            { data: 'transaction_number', name: 'transaction_number',searchable: true}
        ]
    });
                    
            }
$(function() {
    $('#tbl_payment').DataTable({
        processing: true,
        serverSide: true,
        bStateSave: true,
        ajax: {"url":'{{url("/admin/users-payments/list-data")}}',"complete":afterRequestComplete},
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
//                         return '<div class="cust-chqs">  <p> <input class="checkboxes" type="checkbox"  id="delete'+row.id+'" name="delete'+row.id+'" value="'+row.user_id+'"><label for="delete'+row.id+'"></label> </p></div>';
//                    }
//                    return data;
//                },
//                  "orderable": false,
//                  
//             },
            { data: 'id', name: 'id'},
            { data: 'user_name', name: 'user_name'},
            { data: 'payment_mode', name: 'payment_mode',searchable: true},
            { data: 'payment_on', name: 'payment_on',searchable: true},
            { data: 'amount', name: 'amount',searchable: true},
            { data: 'bank_name', name: 'bank_name',searchable: true},
            { data: 'cheque_number', name: 'cheque_number',searchable: true},
            { data: 'transaction_number', name: 'transaction_number'}
        ]
    });
});
function confirmDelete(id)
{
    if(confirm("Do you really want to delete this payment record?"))
    {
        
        $("#delete_payment_"+id).submit();
    }
    return false;
}
</script>
<script>
       
    function startDownload()  
    {  
          var file_name=$("#file_name").val();  
            if(file_name!='')
            {
                window.location.href=file_name;
             // 
           }
    }  
    $(document).ready(function()
        {
            var file_name=$("#file_name").val();  
            
            if(typeof(file_name)!='undefined' && file_name!='')
            {
                setTimeout('startDownload()', 6000); 
            }
        });
   
 </script>
@endsection
