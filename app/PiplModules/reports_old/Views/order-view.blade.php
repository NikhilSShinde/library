@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Order Details</title>

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
                <a href="{{url('admin/reports/order-report-list')}}">Manage Reports</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="javascript:void(0);">Order Summary</a>
            </li>
        </ul>



        <!-- BEGIN SAMPLE FORM PORTLET-->
        <div class="portlet box blue">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i>Order Summary
                </div>

            </div>
            <div class="portlet-body form">
                <form class="form-horizontal" role="form" action="" method="post">
                    {!! csrf_field() !!}
                    <div class="form-body">
                        <div class="row">
                            <div class="col-md-12">    
                                 <div class='divider'> <span>Order Details</span></div> 
                                <div class="col-md-6 col-md-border">
                                    
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Order Number : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                {{isset($oder_details->order_unique_id)?$oder_details->order_unique_id:'-'}}
                                            </span>
                                        </div>
                                    </div>
                                     <div class="form-group">
                                        <label class="col-md-6 control-label">Order Status : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                               @if($oder_details->status == '0')  Pending @elseif($oder_details->status == '1') Active @elseif($oder_details->status == '2') Completed @elseif($oder_details->status == '3') Cancelled @else Expired @endif

                                            </span>
                                        </div>
                                    </div>
                                  <div class="form-group">
                                        <label class="col-md-6 control-label">Order Posted Date : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                {{isset($oder_details->created_at)?$oder_details->created_at:'-'}}

                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Order Date and Time : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                {{isset($oder_details->order_place_date_time)?$oder_details->order_place_date_time:'-'}}
                                                
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Order Completed Date Time : </label>
                                         <div class="col-md-6">     
                                            <span class="help-block">
                                                 {{isset($oder_details->order_complete_date_time)?$oder_details->order_complete_date_time:'-'}}
                                                
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Order Type : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                @if($oder_details->order_type == '0')
                                                 Instant Order
                                               @elseif($oder_details->order_type == '1') 
                                                Scheduled Order
                                               @elseif($oder_details->order_type == '2') 
                                                Pick Instant Deliver Later Order
                                             
                                               @endif 
                                            </span>
                                        </div>
                                    </div>
                                     <div class="form-group">
                                        <label class="col-md-6 control-label">Payment Type : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                @if($oder_details->payment_type == '1')
                                                 Online
                                               @elseif($oder_details->payment_type == '2') 
                                                COD
                                               @elseif($oder_details->payment_type == '3') 
                                               Wallet
                                             
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
                                 <div class='divider'> <span>Order Charges Details</span></div> 
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
                                        <label class="col-md-6 control-label">Other Charges : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                    {{isset($oder_details->other_charges)?$oder_details->other_charges:'0'}}

                                            </span>
                                        </div>
                                    </div>
                                     <div class="form-group">
                                        <label class="col-md-6 control-label">Payment Type Selected : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                   @if(isset($oder_details->payment_type)=='1')
                                                   Online
                                                   @elseif (isset($oder_details->payment_type)=='2')
                                                   COD
                                                    @elseif (isset($oder_details->payment_type)=='3')
                                                    Wallet
                                                   @endif 

                                            </span>
                                        </div>
                                    </div>
                                    

                                </div>
                                 
                                  
                            </div>
                        </div>
                        
                         <div class="row">
                            <div class="col-md-12">    
                                 <div class='divider'> <span>Order Users</span></div> 
                                   <div class="col-md-6 col-md-border">
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Customer User Id : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                    {{isset($oder_details->mate_id)?$oder_details->mate_id:''}}

                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Customer User  : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                    {{isset($oder_details->getUserMateInformation->first_name)?$oder_details->getUserMateInformation->first_name." ".$oder_details->getUserMateInformation->last_name:''}}

                                            </span>
                                        </div>
                                    </div>
                                 </div>
                                 <div class="col-md-6 col-md-border">
                                      <div class="form-group">
                                        <label class="col-md-6 control-label">Delivery User Id: </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                    {{isset($oder_details->driver_id)?$oder_details->driver_id:''}}

                                            </span>
                                        </div>
                                    </div>
                                      <div class="form-group">
                                        <label class="col-md-6 control-label">Delivery User  : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                    {{isset($oder_details->getUserStarInformation->first_name)?$oder_details->getUserStarInformation->first_name." ".$oder_details->getUserMateInformation->last_name:''}}

                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                         </div>
                        
                        <div class="row">
                            <div class="col-md-12">    
                                 <div class='divider'> <span>Order Address Details</span></div> 
                                <div class="col-md-6 col-md-border">
                                    
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Order Place Latitude : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                    {{isset($oder_details->getOrderTransInformation->selected_pickup_lat)?$oder_details->getOrderTransInformation->selected_pickup_lat:''}}

                                            </span>
                                        </div>
                                    </div>
                                     <div class="form-group">
                                        <label class="col-md-6 control-label">Order Place Latitude : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                    {{isset($oder_details->getOrderTransInformation->selected_pickup_long)?$oder_details->getOrderTransInformation->selected_pickup_long:''}}

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
                                        <label class="col-md-6 control-label">Order Drop Latitude : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                    {{isset($oder_details->getOrderTransInformation->selected_drop_lat)?$oder_details->getOrderTransInformation->selected_drop_lat:''}}

                                            </span>
                                        </div>
                                    </div>
                                     <div class="form-group">
                                        <label class="col-md-6 control-label">Order Drop Latitude : </label>
                                        <div class="col-md-6">     
                                            <span class="help-block">
                                                    {{isset($oder_details->getOrderTransInformation->selected_drop_long)?$oder_details->getOrderTransInformation->selected_pickup_long:''}}

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
                                <div class='divider'> <span>Order Delivery Cancellations</span></div> 
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
                                <div class='divider'> <span>Order Item Images</span></div> 
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
</div>
</div>
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
@endsection