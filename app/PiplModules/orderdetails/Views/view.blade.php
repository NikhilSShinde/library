@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Trip Details</title>

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
                <a href="{{url('admin/order-list')}}">Manage Trips</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="javascript:void(0);">Trip Summary</a>
            </li>
        </ul>



        <!-- BEGIN SAMPLE FORM PORTLET-->
        <div class="portlet box blue">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i>Trip Summary
                </div>

            </div>
            <div class="portlet-body form">
                <form class="form-horizontal" role="form" action="" method="post">
                    {!! csrf_field() !!}
                    <div class="form-body">
                        <div class="row">
                            <div class="col-md-12">    
                               
                                 <div class='divider'> <span>Trip Details</span></div> 
                                @if($oder_details->status=='0')
                                <a href="{{url('/admin/assign-star')}}/{{$oder_details->id}}"  id="assign-star" class="btn btn-success pull-right" name="assign-star" >Assign a Driver</a>
                               @endif   
                                  <div class="col-md-6 col-md-border">
                                    
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Trip Number : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                {{isset($oder_details->order_unique_id)?$oder_details->order_unique_id:'-'}}
                                            </span>
                                        </div>
                                    </div>
                                     <div class="form-group">
                                        <label class="col-md-6 control-label">Trip Status : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                               @if($oder_details->status == '0')  Pending @elseif($oder_details->status == '1') Active @elseif($oder_details->status == '2') Completed @elseif($oder_details->status == '3') Cancelled @else Expired @endif

                                            </span>
                                        </div>
                                    </div>
                                  <div class="form-group">
                                        <label class="col-md-6 control-label">Trip Posted Date : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                {{isset($oder_details->created_at)?$oder_details->created_at:'-'}}

                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        @if($oder_details->order_type == '3') 
                                          <label class="col-md-6 control-label">Trip  Date and Time : </label>
                                        @else
                                        <label class="col-md-6 control-label">Trip Date and Time : </label>
                                        @endif 
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                {{isset($oder_details->order_place_date_time)?$oder_details->order_place_date_time:'-'}}
                                                
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Trip Completed Date Time : </label>
                                         <div class="col-md-6">     
                                            <span class="help-block">
                                                 {{isset($oder_details->order_complete_date_time) && $oder_details->status==2?$oder_details->order_complete_date_time:'Not Yet Completed'}}
                                                
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Trip Type : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                @if($oder_details->order_type == '1')
                                                 Instant Trip
                                               @elseif($oder_details->order_type == '2') 
                                                Scheduled Trip
                                               @elseif($oder_details->order_type == '3') 
                                                Pick Instant Deliver Later Trip
                                             
                                               @endif 
                                            </span>
                                        </div>
                                    </div>
                                     <div class="form-group">
                                        <label class="col-md-6 control-label">Payment Type : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                @if($oder_details->payment_type == '1')
                                                 Pay By Card
                                               @elseif($oder_details->payment_type == '2') 
                                                Pay By Wallet
                                               @elseif($oder_details->payment_type == '3') 
                                                COD
                                             
                                               @endif 
                                            </span>
                                        </div>
                                    </div>
                                   
                                </div>

                                 <div class="col-md-6">  
                                  
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Service Name : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                  {{isset($oder_details->getServicesDetails->getServiceTransDetails->name)?$oder_details->getServicesDetails->getServiceTransDetails->name:''}}
                                         
                                            </span>
                                        </div>
                                    </div>
                                   
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Contact Person For Pickup : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                {{isset($oder_details->getOrderTransInformation->contact_person_for_pickup)?$oder_details->getOrderTransInformation->contact_person_for_pickup:''}}

                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Contact Person For Destination : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                 {{isset($oder_details->getOrderTransInformation->contact_person_for_destination)?$oder_details->getOrderTransInformation->contact_person_for_destination:''}}

                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Pickup Person Contact Number : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                 {{isset($oder_details->getOrderTransInformation->pickup_person_contact_no)?$oder_details->getOrderTransInformation->pickup_person_contact_no:''}}
                                            
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Destination Person Contact Number : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                 {{isset($oder_details->getOrderTransInformation->destination_person_contact_no)?$oder_details->getOrderTransInformation->destination_person_contact_no:''}}

                                            </span>
                                        </div>
                                    </div>
                                     <div class="form-group">
                                        <label class="col-md-6 control-label">Coupon Code : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                 {{isset($oder_details->getOrderTransInformation->coupon_code)?$oder_details->getOrderTransInformation->coupon_code:''}}

                                            </span>
                                        </div>
                                    </div>
                                     <div class="form-group">
                                        <label class="col-md-6 control-label">Item Description : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                 {{isset($oder_details->getOrderTransInformation->item_description)?$oder_details->getOrderTransInformation->item_description:'-'}}

                                            </span>
                                        </div>
                                    </div>
                                </div>
                                 
                                  
                            </div>
                        </div>
                         <div class="row">
                            <div class="col-md-12">    
                                 <div class='divider'> <span>Trip Charges Details</span></div> 
                                <div class="col-md-6 col-md-border">
                                    
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Approx Fare Amount : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                    {{isset($oder_details->fare_amount)?$oder_details->fare_amount:'0'}}

                                            </span>
                                        </div>
                                    </div>
                                     <div class="form-group">
                                        <label class="col-md-6 control-label">Waiting Charges : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                    {{isset($oder_details->waiting_charge)?$oder_details->waiting_charge:'0'}}

                                            </span>
                                        </div>
                                    </div>
                                    </div>
                                   

                                 <div class="col-md-6">  
                                   <div class="form-group">
                                        <label class="col-md-6 control-label">Total Charge Amount : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                    {{(isset($oder_details->total_amount) && $oder_details->total_amount>0)?$oder_details->total_amount:'Status is not completed'}}

                                            </span>
                                        </div>
                                    </div>
                                     
                                </div>
                                 
                                  
                            </div>
                        </div>
                        
                         <div class="row">
                            <div class="col-md-12">    
                                 <div class='divider'> <span>Trip Users</span></div> 
                                   <div class="col-md-6 col-md-border">
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Customer Id : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                    {{isset($oder_details->mate_id)?$oder_details->mate_id:''}}

                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Customer  : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                    {{isset($oder_details->getUserMateInformation->first_name)?$oder_details->getUserMateInformation->first_name." ".$oder_details->getUserMateInformation->last_name:''}}

                                            </span>
                                        </div>
                                    </div>
                                 </div>
                                 <div class="col-md-6 col-md-border">
                                      <div class="form-group">
                                        <label class="col-md-6 control-label">Driver User Id: </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                    {{isset($oder_details->driver_id)?$oder_details->driver_id:''}}

                                            </span>
                                        </div>
                                    </div>
                                      <div class="form-group">
                                        <label class="col-md-6 control-label">Driver User  : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                    {{isset($oder_details->getUserStarInformation->first_name)?$oder_details->getUserStarInformation->first_name." ".$oder_details->getUserStarInformation->last_name:''}}

                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                         </div>
                        
                        <div class="row">
                            <div class="col-md-12">    
                                 <div class='divider'> <span>Trip Address Details</span></div> 
                                <div class="col-md-6 col-md-border">
                                    
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Trip Place Latitude : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                    {{isset($oder_details->getOrderTransInformation->selected_pickup_lat)?$oder_details->getOrderTransInformation->selected_pickup_lat:''}}

                                            </span>
                                        </div>
                                    </div>
                                     <div class="form-group">
                                        <label class="col-md-6 control-label">Trip Place Latitude : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                    {{isset($oder_details->getOrderTransInformation->selected_pickup_long)?$oder_details->getOrderTransInformation->selected_pickup_long:''}}

                                            </span>
                                        </div>
                                    </div>
                                     <div class="form-group">
                                        <label class="col-md-6 control-label">Trip Pick Up Latitude : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                    {{isset($oder_details->getOrderTransInformation->pickup_lat)?$oder_details->getOrderTransInformation->pickup_lat:''}}

                                            </span>
                                        </div>
                                    </div>
                                     <div class="form-group">
                                        <label class="col-md-6 control-label">Trip Pick Up Longitude Latitude : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                    {{isset($oder_details->getOrderTransInformation->pickup_long)?$oder_details->getOrderTransInformation->pickup_long:''}}

                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Distance : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                     {{isset($oder_details->getOrderTransInformation->distance)?$oder_details->getOrderTransInformation->distance:''}}

                                            </span>
                                        </div>
                                    </div>
                                   
                                </div>

                                 <div class="col-md-6">  
                                   <div class="form-group">
                                        <label class="col-md-6 control-label">Trip Drop Latitude : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                    {{isset($oder_details->getOrderTransInformation->selected_drop_lat)?$oder_details->getOrderTransInformation->selected_drop_lat:''}}

                                            </span>
                                        </div>
                                    </div>
                                     <div class="form-group">
                                        <label class="col-md-6 control-label">Trip Drop Longitude : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                    {{isset($oder_details->getOrderTransInformation->selected_drop_long)?$oder_details->getOrderTransInformation->selected_drop_long:''}}

                                            </span>
                                        </div>
                                    </div>
                                     
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Pickup Area : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                  {{isset($oder_details->getOrderTransInformation->pickup_area)?$oder_details->getOrderTransInformation->pickup_area:''}}

                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Drop Area : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                  {{isset($oder_details->getOrderTransInformation->drop_area)?$oder_details->getOrderTransInformation->drop_area:''}}

                                            </span>
                                        </div>
                                    </div>

                                </div>
                                 
                                  
                            </div>
                        </div>
                         @if (isset($oder_details->getOrderCancellations))
                            <div class="row">
                            <div class="col-md-12">    
                                <div class='divider'> <span>Trip Cancellations</span></div> 
                                @if (isset($oder_details->getOrderCancellations))
                                  
                                    @foreach($oder_details->getOrderCancellations as $cancel_val)
                                   
                                   <div class="col-md-6">  
                                       
                                    <div class="form-group">
                                        <label class="col-md-4 control-label">User Id: </label>
                                        <div class="col-md-8">     
                                            <span class="help-block">
                                                    {{isset($cancel_val->user_id)?$cancel_val->user_id:'0'}}

                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-4 control-label">User : </label>
                                        <div class="col-md-8">     
                                            <span class="help-block">
                                                    {{isset($cancel_val->userDetails->userInformation->first_name)?$cancel_val->userDetails->userInformation->first_name." ".$cancel_val->userDetails->userInformation->last_name:'0'}}

                                            </span>
                                        </div>
                                    </div>
                                   <div class="form-group">
                                        <label class="col-md-4 control-label">Reason : </label>
                                        <div class="col-md-8">     
                                            <span class="help-block">
                                                    {{isset($cancel_val->reason_text)?$cancel_val->reason_text:'0'}}

                                            </span>
                                        </div>
                                    </div></div>
                                    @endforeach
                                
                                 @endif
                                  
                            
                        </div>
                        </div>
                         
                         @endif
                         @if (isset($oder_details->getOrderImages))  
                          <div class="row">
                            <div class="col-md-12">    
                                <div class='divider'> <span>Trip Item Images</span></div> 
                                @if (isset($oder_details->getOrderImages))
                                
                                    @foreach($oder_details->getOrderImages as $image)
                                    
                                      @if($image->item_image!='')
                                      <img src="{{asset('storageasset/item-images/')}}/{{$image->item_image}}">
                                      @endif
                                     
                                    @endforeach
                                
                                 @endif
                                  
                            </div>
                        </div>
                        @endif 
                    </div>
            </div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="assign_star" role="dialog">
        <div class="modal-dialog">
            <form name="frm_assign_star" id="frm_assign_star" action="{{url('/admin/assign-star-to-order')}}" method="post">
                <div class="modal-content">
                    {{csrf_field()}}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Assign A Driver user </h4>
                    </div>
                    <div class="modal-body">
                        <ul class="list-group" id="user_list">No Driver user Available</ul>

                    </div>
                    <div for="assign_to" generated="true" class="text-danger text-center"></div>
                    <div class="modal-footer">
                        <input type="hidden" name="order_id" id="order_id" value="">
                        <button type="submit" class="btn btn-success">Assign Now</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </form>
        </div>
    </div><!-- /.modal-dialog -->
</div>
<style>
    .divider{
       background-color: #ccc;
       border: 1px solid;
       width: 100%;
       height: 30px;
       color:#fff;
       text-align: center;
       font-size: 18px;
    }
</style>
<script>
      function assignAStar(order_id)
       {
                            $.ajax({
                            url: '{{url("/admin/order-assign-star")}}',
                                    data: {
                                    order_id:order_id
                                    },
                                    type:'post',
                                    dataType: 'json',
                                    success: function(response) {

                                    if (response.error_code == '0')
                                    {
                                    $('#order_id').val(order_id);
                                            $('#assign_star').modal('show');
                                            var str = '';
                                            $.each(response.data, function(index, value) {

                                            if (value.user_id != null){
                                            str += '<li class="list-group-item"><input  type="radio" name="assign_to" id="assign_to" value="' + value.user_id + '"> <lable>' + value.first_name + ' ' + value.last_name + ' (' + value.distance + ' KM)</lable> </li>';
                                            }
                                            });
                                            $('#user_list').html(str);
                                    } else{
                                    alert("No star is availabe currently for this location and service.")
                                    }}
                            });
         }
     jQuery(document).ready(function() {
                jQuery("#frm_assign_star").validate({
                 errorClass: 'text-danger',
                        errorElement:'div',
                        rules: {
                        assign_to:{
                        required: true
                        }
                        },
                        messages: {
                        assign_to: {
                        required: "Please select atlest one user."
                        }
                        }
                });
     });
</script>    
@endsection