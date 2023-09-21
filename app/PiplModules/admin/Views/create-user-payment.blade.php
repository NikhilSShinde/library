@extends(config("piplmodules.back-view-layout-location-payment"))

@section("meta")

<title>Create Payment</title>

@endsection

@section('content')
 @if (session('user-payment-error'))
            <div class="alert alert-success">
                  {{session('user-payment-error')}}
            </div>
 @endif    
<div class="page-content-wrapper">
		<div class="page-content">
			<ul class="page-breadcrumb breadcrumb">
				<li>
					<a href="{{url('admin/dashbard')}}">Dashboard</a>
					<i class="fa fa-circle"></i>
				</li>
				<li>
					<a href="{{url('admin/users-payments/list')}}">Manage Payments</a>
                                        <i class="fa fa-circle"></i>
					
				</li>
				<li>
					<a href="javascript:void(0);">Create Payment</a>
					
				</li>
                        </ul>
      <!-- BEGIN SAMPLE FORM PORTLET-->
        <div class="portlet box blue">
             <div class="portlet-title">
                        <div class="caption">
                                <i class="fa fa-gift"></i> Create A Payment
                        </div>

             </div>
             <div class="portlet-body form">
  	  <form class="form-horizontal" id="create_payment" name="create_payment" role="form" action="{{url('admin/create-user-payment')}}" method="post">
            
                 {!! csrf_field() !!}
                 <div class="form-body">
                   <div class="row">
                        <div class="col-md-12">    
                        <div class="col-md-8">
                        <div class="form-group">
                           <label class="col-md-6 control-label">User<sup>*</sup></label>
                           <div class="col-md-6">     
                               <input type="text" name="user" class="form-control" placeholder="Start typing to auto suggest (min 3 characters)..."  id="user">
                               <input type="hidden" name="user_id" class="form-control"  id="user_id">
                                @if ($errors->has('user_id'))
                                        <span class="help-block">
                                            <strong class="text-danger">{{ $errors->first('user_id') }}</strong>
                                        </span>
                                @endif
                          </div>
                       
                        </div>
                         <div class="form-group">
                          <label class="col-md-6 control-label">Payment Mode<sup>*</sup></label>
                           <div class="col-md-6">     
                               <input name="payment_mode" value="Cash" type="radio" onclick="hideAll();" checked="checked"   id="payment_mode1">Cash
                               <input name="payment_mode" value="Cheque" type="radio" onclick="showCheque();"  id="payment_mode2">Cheque
                               <input name="payment_mode" value="Online" type="radio" onclick="showOnline();"  id="payment_mode3">Online
                            @if ($errors->has('payment_mode'))
                              <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('payment_mode') }}</strong>
                              </span>
                              @endif
                          </div>
                       
                      </div>
                        
                        <div class="form-group" id="bank_div" style="display: none;">
                           <label class="col-md-6 control-label">Bank Name<sup>*</sup></label>
                           <div class="col-md-6">     
                               <input type="text" name="bank_name" value="{{old('bank_name')}}" class="form-control" placeholder="Bank name"  id="bank_name">
                                @if ($errors->has('bank_name'))
                                        <span class="help-block">
                                            <strong class="text-danger">{{ $errors->first('bank_name') }}</strong>
                                        </span>
                                @endif
                          </div> 
                         </div>  
                        
                        <div class="form-group" id="cheque_div" style="display: none;">
                           <label class="col-md-6 control-label">Cheque Number<sup>*</sup></label>
                           <div class="col-md-6">     
                               <input type="text" name="cheque_number" value="{{old('cheque_number')}}" class="form-control" placeholder="Cheque number"  id="cheque_number">
                                @if ($errors->has('cheque_number'))
                                        <span class="help-block">
                                            <strong class="text-danger">{{ $errors->first('cheque_number') }}</strong>
                                        </span>
                                @endif
                          </div> 
                        </div>  
                        <div class="form-group" id="transaction_div" style="display: none;">
                           <label class="col-md-6 control-label">Transaction  Number<sup>*</sup></label>
                           <div class="col-md-6">     
                               <input type="text" name="transaction_number" value="{{old('transaction_number')}}" class="form-control" placeholder="Transaction number"  id="transaction_number">
                                @if ($errors->has('transaction_number'))
                                        <span class="help-block">
                                            <strong class="text-danger">{{ $errors->first('transaction_number') }}</strong>
                                        </span>
                                @endif
                          </div> 
                        </div>     
                        <div class="form-group amount_to_pay_div" id="" style="display:none">
                           <label class="col-md-6 control-label">TOTAL AMOUNT (THE %100)<sup>*</sup></label>
                           <div class="col-md-6">     
                               <input type="text" disabled="" name="total_order_amount"  class="form-control" placeholder=""  id="total_order_amount">
                              
                          </div> 
                        </div>     
                        <div class="form-group amount_to_pay_div" id="" style="display:none">
                           <label class="col-md-6 control-label"> TOTAL AMOUNT PAID BY CASH<sup>*</sup></label>
                           <div class="col-md-6">     
                               <input type="text" disabled="" name="total_order_cash_amount"  class="form-control" placeholder=""  id="total_order_cash_amount">
                              
                          </div> 
                        </div>     
                        <div class="form-group amount_to_pay_div" id="" style="display:none">
                           <label class="col-md-6 control-label">TOTAL AMOUNT PAID BY CARD<sup>*</sup></label>
                           <div class="col-md-6">     
                               <input type="text" disabled="" name="total_order_online_amount"  class="form-control" placeholder=""  id="total_order_online_amount">
                              
                          </div> 
                        </div>     
                        <div class="form-group amount_to_pay_div" id="" style="display:none">
                           <label class="col-md-6 control-label">Driver BALANCE (OF ALL TRIPS)<sup>*</sup></label>
                           <div class="col-md-6">     
                               <input type="text" disabled="" name="total_star_amount"  class="form-control" placeholder=""  id="total_star_amount">
                              
                          </div> 
                        </div>     
                        <div class="form-group amount_to_pay_div" id="" style="display:none">
                           <label class="col-md-6 control-label">Driver PERCENTAGE OF CASH (%80)<sup>*</sup></label>
                           <div class="col-md-6">     
                               <input type="text" disabled="" name="total_star_cash_amount"  class="form-control" placeholder=""  id="total_star_cash_amount">
                              
                          </div> 
                        </div>     
                        <div class="form-group amount_to_pay_div" id="" style="display:none">
                           <label class="col-md-6 control-label">Driver PERCENTAGE OF CARDS (%80)<sup>*</sup></label>
                           <div class="col-md-6">     
                               <input type="text" disabled="" name="total_star_online_amount"  class="form-control" placeholder=""  id="total_star_online_amount">
                              
                          </div> 
                        </div>     
                        <div class="form-group amount_to_pay_div" id="" style="display:none">
                           <label class="col-md-6 control-label"> WHAT Driver HAS TO PAY US FROM CASH (%20)<sup>*</sup></label>
                           <div class="col-md-6">     
                               <input type="text" disabled="" name="total_star_payable_amount"  class="form-control" placeholder=""  id="total_star_payable_amount">
                              
                          </div> 
                        </div> 
<!--                        <div class="form-group amount_to_pay_div" id="" style="display:none">
                           <label class="col-md-6 control-label"> WHAT YOU HAVE TO PAY To ADMIN FROM CASH (%10)<sup>*</sup></label>
                           <div class="col-md-6">     
                               <input type="text" disabled="" name="total_payable_amount_to_admin"  class="form-control" placeholder=""  id="total_payable_amount_to_admin">
                              
                          </div> 
                        </div> 
                       <div class="form-group amount_to_pay_div" id="" style="display:none">
                           <label class="col-md-6 control-label"> WHAT ADMIN HAVE To PAY TO YOU ONLINE (%10)<sup>*</sup></label>
                           <div class="col-md-6">     
                               <input type="text" disabled="" name="total_payable_amount_to_admin_online"  class="form-control" placeholder=""  id="total_payable_amount_to_admin_online">
                              
                          </div> 
                        </div>    -->
                        <div class="form-group amount_to_pay_div" style="display:none;">
                           <label class="col-md-6 control-label">Amount (Driver Wallet Balance)<sup>*</sup></label>
                            <div class="col-md-6">     
                               <input type="text"  name="amount" value="{{old('amount')}}" class="form-control" placeholder="Amount" disabled=""  id="amount">
                               <input type="hidden"  name="amount_to_check" value="{{old('amount_to_check')}}" class="form-control" placeholder=""  id="amount_to_check">
                                ** This is current Driver balance (Online star percentage amount), It does not consider the amount, Driver has to pay to us.**
                            </div> 
                        </div>     
                        <div class="form-group">
                         <div class="col-md-12">   
                             <button type="submit" onclick="return submitPayment()" id="submit" class="btn btn-primary  pull-right">Submit</button>
                             
                         </div>
                        </div>
                </div>
              </div>
            </div>
                
             </div>
    
            </form>
        </div>
    </div>
    </div>
    </div>
        <style>
            .submit-btn{
                padding: 10px 0px 0px 18px;
            }
        </style>
  
  
<script>      
 $(function() {
    $( "#user" ).autocomplete({
      source: "{{url('/admin/getAllStarAgents')}}",
      minLength: 3,
      select: function( event, ui ) {
          
        $("#user_id").val(ui.item.id );
        if(ui.item.amount!='')
        {
            $(".amount_to_pay_div").show();
            $("#total_order_amount").val(ui.item.total_order_amount);
            $("#total_order_cash_amount").val(ui.item.total_order_cash_amount);
            $("#total_order_online_amount").val(ui.item.total_order_online_amount);
            $("#total_star_amount").val(ui.item.total_star_amount);
            $("#total_star_cash_amount").val(ui.item.total_star_cash_amount);
            $("#total_star_online_amount").val(ui.item.total_star_online_amount);
            $("#total_star_payable_amount").val(ui.item.total_star_payable_amount);
            $("#total_payable_amount_to_admin").val(ui.item.total_payable_amount_to_admin);
            $("#total_payable_amount_to_admin_online").val(ui.item.total_payable_amount_to_admin_online);
            $("#amount").val(ui.item.amount);
            $("#amount_to_check").val(ui.item.amount);
        }
      }
    });
 });
 function submitPayment()
 {
   if($("#amount_to_check").val()>0)
   {
    if(confirm("Are you sure, you want to pay to star? This action can not be revert back?"))
    {
          return true;
    }else{
        return false;
    }
   }else{
        alert("Sorry, The amount to pay is not valid.");
        return false;
   }
 }
 function hideAll()
 {
     $("#bank_div").hide();
     $("#cheque_div").hide();
     $("#transaction_div").hide();
 }
 function showCheque()
 {
     $("#bank_div").show();
     $("#cheque_div").show();
     $("#transaction_div").hide();
 }
 function showOnline()
 {
     $("#bank_div").show();
     $("#cheque_div").hide();
     $("#transaction_div").show();
 }
  </script>
 @endsection
  