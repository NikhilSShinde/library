@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Update Country Info</title>

@endsection

@section('content')
<script type="text/javascript">
    function selectAllServices(cat_id){
 
        if($("#category_"+cat_id).prop('checked')){
            $(".services_"+cat_id).prop('checked',true);
   
        }else{
           $(".services_"+cat_id).prop('checked',false);
        }
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
					<a href="{{url('admin/countries/list')}}">Manage Countries</a>
                                        <i class="fa fa-circle"></i>
					
				</li>
				<li>
					<a href="javascript:void(0);">Update Country</a>
					
				</li>
                        </ul>

  
    
      <!-- BEGIN SAMPLE FORM PORTLET-->
        <div class="portlet box blue">
             <div class="portlet-title">
                        <div class="caption">
                                <i class="fa fa-gift"></i> Update Country Info
                        </div>

             </div>
             <div class="portlet-body form">
              <form class="form-horizontal" role="form" id="frm_country_update" name="frm_country_update" action="" method="post" >
            
                 {!! csrf_field() !!}
                 <div class="form-body">
                   <div class="row">
                     <div class="col-md-12">    
                      <div class="col-md-8">  
                         <div style="margin-bottom: 15px;">
                          <label class=" control-label">Name<sup>*</sup></label>
                       
                          <!--<div class="col-md-6">-->     
                           <input name="name" type="text" class="form-control" id="name" value="{{old('name',$country_info->name)}}">
                            @if ($errors->has('name'))
                              <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('name') }}</strong>
                              </span>
                              @endif
                          <!--</div>-->
                       
                          </div>
                          
  
                 </div>
                <div class="col-md-8">  
                         <div style="margin-bottom: 15px;">
                          <label class="control-label">Country ISO<sup>*</sup></label>
                       
                          <!--<div class="col-md-6">-->     
                           <input name="iso" type="text" class="form-control" id="iso" value="{{old('iso',$main_info->iso)}}">
                            @if ($errors->has('iso'))
                              <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('iso') }}</strong>
                              </span>
                              @endif
                          <!--</div>-->
                       
                            </div>
                           
                 </div>     
                 <div class="col-md-8">  
                         <div style="margin-bottom: 15px;">
                          <label class=" control-label">Support Number<sup>*</sup></label>
                          <!--<label class="col-md-6 control-label">Max Mobile Digits<sup>*</sup></label>-->
                       
                          <!--<div class="col-md-6">-->     
                            <input name="support_number" type="text" min="1" class="form-control" id="support_number" value="{{old('support_number',$country_info->support_number)}}">
                            @if ($errors->has('support_number'))
                              <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('support_number') }}</strong>
                              </span>
                              @endif
                          <!--</div>-->
                            </div>
                 </div>         
                <div class="col-md-8">  
                         <div style="margin-bottom: 15px;">
                          <label class=" control-label">Country Code<sup>*</sup></label>
                       
                          <!--<div class="col-md-6">-->     
                           <input name="country_code" type="text" class="form-control" id="country_code" value="{{old('country_code',$main_info->country_code)}}">
                            @if ($errors->has('country_code'))
                              <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('country_code') }}</strong>
                              </span>
                              @endif
                          <!--</div>-->
                       
                            </div>
                           
                 </div> 
                <div class="col-md-8">  
                         <div style="margin-bottom: 15px;">
                          <label class=" control-label">Cancellation charge<sup>*</sup></label>
                       
                          <!--<div class="col-md-6">-->     
                           <input name="cancellation_charge" type="text" class="form-control" id="cancellation_charge" value="{{old('country_code',$main_info->cancellation_charge)}}">
                            @if ($errors->has('cancellation_charge'))
                              <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('cancellation_charge') }}</strong>
                              </span>
                              @endif
                          <!--</div>-->
                       
                            </div>
                           
                 </div> 
                <div class="col-md-8">  
                         <div style="margin-bottom: 15px;">
                          <label class=" control-label">Timezone<sup>*</sup></label>
                       
                          <!--<div class="col-md-6">-->     
                           <input name="time_zone" type="text" class="form-control" id="time_zone" value="{{old('time_zone',$main_info->time_zone)}}">
                            @if ($errors->has('cancellation_charge'))
                              <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('time_zone') }}</strong>
                              </span>
                              @endif
                          <!--</div>-->
                       
                            </div>
                           
                 </div> 
                   <div class="col-md-8">  
                         <div style="margin-bottom: 15px;">
                          <label class=" control-label">Currency<sup>*</sup></label>                       
                          <!--<div class="col-md-6">-->     
                            <select name="currency_code" class="form-control" id="currency_code">
                                  <option value="">--Select--</option>
<!--                                  <option value="BHD" @if(old('currency_code',$main_info->currency_code)=='BHD') selected @endif>Bahraini Riyal</option>
                                  <option value="KD"  @if(old('currency_code',$main_info->currency_code)=='KD') selected @endif>Kuwaiti Dinar</option>
                                  <option value="OMR" @if(old('currency_code',$main_info->currency_code)=='OMR') selected @endif>Omani Riyal</option>
                                  <option value="QAR" @if(old('currency_code',$main_info->currency_code)=='QAR') selected @endif>Qatari Riyal</option>
                                  <option value="SR"  @if(old('currency_code',$main_info->currency_code)=='SR') selected @endif>Saudi Riyal</option>
                                  <option value="AED" @if(old('currency_code',$main_info->currency_code)=='AED') selected @endif>Dirham AED</option>
                                  <option value="OMR" @if(old('currency_code',$main_info->currency_code)=='OMR') selected @endif>Omani rial</option>-->
                                  <option value="INR" @if(old('currency_code',$main_info->currency_code)=='INR') selected @endif>INR</option>
                                  <option value="JPY" @if(old('currency_code',$main_info->currency_code)=='JPY') selected @endif>Japanese yen</option>
                                  <!--<option value="ETB" @if(old('currency_code',$main_info->currency_code)=='ETB') selected @endif>Ethiopian birr</option>-->
<!--                                  <option value="IDR" @if(old('currency_code',$main_info->currency_code)=='IDR') selected @endif>Indonesian rupiah</option>
                                  <option value="EGP" @if(old('currency_code',$main_info->currency_code)=='EGP') selected @endif>Egyptian pound</option>
                                  <option value="ALL" @if(old('currency_code',$main_info->currency_code)=='ALL') selected @endif>Albanian lek</option>
                                  <option value="JOD" @if(old('currency_code',$main_info->currency_code)=='JOD') selected @endif>Jordanian dinar</option>
                                  <option value="TRY" @if(old('currency_code',$main_info->currency_code)=='TRY') selected @endif>Turkish lira</option>
                                  <option value="RON" @if(old('currency_code',$main_info->currency_code)=='RON') selected @endif>Romanian leu</option>-->
                            </select>
                            @if ($errors->has('currency_code'))
                              <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('currency_code') }}</strong>
                              </span>
                              @endif
                          <!--</div>-->
                       
                            </div>
                            
  
                 </div> 
                 <div class="col-md-8">  
                         <div style="margin-bottom: 15px;">
                          <label class=" control-label">Max Mobile Digits<sup>*</sup></label>
                          <!--<label class="col-md-6 control-label">Max Mobile Digits<sup>*</sup></label>-->
                       
                          <!--<div class="col-md-6">-->     
                            <input name="max_mobile_digit" type="number" min="1" class="form-control" id="max_mobile_digit" value="{{old('currency_code',$main_info->max_mobile_digit)}}">
                            @if ($errors->has('max_mobile_digit'))
                              <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('max_mobile_digit') }}</strong>
                              </span>
                              @endif
                          <!--</div>-->
                            </div>
                 </div>
                
<!--                    @foreach($categories as $category)
                    <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading" style="padding: 10px 1px;font-size: 14px; font-weight: 400;">
                              {{$category->name}}
                               <p class="pull-right">
                                    Select All <input type="checkbox"  id="category_{{$category->id}}"  onClick="javascript:selectAllServices({{$category->id}});" value="{{$category->id}}" > 
                                </p>
                        </div>

                            <input type="hidden" id="category_{{$category->id}}" name="category[]" value="{{$category->id}}" > 

                        <div class="panel-body" id="cate_div_{{$category->id}}">
                            
                             @foreach($services as $service)
                             
                              @if ($category->id==$service->category_id)
                              <?php $per_km = ''; $base_price = ''; $base_km='';?>
                              <div class="form-group">
                             <div class="row add-margin">
                             <div class="col-md-12">
                                 <div class="col-md-2">
                                <input type="checkbox" class="services_{{$category->id}}" id="service_{{$service->id}}"

                              @foreach($country_services as $user_service)
                                @if ($user_service->service_id==$service->id)
                                <?php   $per_km = $user_service->price_per_km;
                                        $per_min = $user_service->price_per_min; 
                                        $base_price = $user_service->base_price; 
                                        //$is_sharable = $user_service->is_sharable; 
                                        $sort_index = $user_service->sort_index;                                         
                                        $sort_index_arabic = $user_service->sort_index_arabic;                                         
                                        $check_point_distance = $user_service->check_point_distance;                                         
                                        $flat_price = $user_service->flat_price;                                         
                                        $base_km=$user_service->base_km;
                                        $price_type= $user_service->price_type;?>
                                checked="checked"
                                @endif
                               @endforeach  name="services[]" value="{{$service->id}}" >
                               
                               <label class="control-label">{{$service->name}}</label>
                                
                                </div>
                              <div class="col-md-3">
                                  <input type="radio" name="price_type_{{$service->id}}" value="0" @if(isset($price_type) && $price_type=='0') checked @endif  id="price_variable_option"/> Variable
                                <input type="radio" name="price_type_{{$service->id}}" value="1"  @if(isset($price_type) && $price_type=='1') checked @endif id="price_percent_option" /> Fixed
                              </div>
                              <div class="col-md-2">
                                  <input type="radio" name="is_sharable_{{$service->id}}" value="0" @if(isset($is_sharable) && $is_sharable=='0') checked @endif  id="is_sharable"/> No Sharable
                                <input type="radio" name="is_sharable_{{$service->id}}" value="1"  @if(isset($is_sharable) && $is_sharable=='1') checked @endif id="is_sharable" /> Sharable
                              </div>
                              <div class="col-md-2">
                                  
                               <label class=" control-label">Base Price</label>
                               <input name="base_price_{{$service->id}}" type="text"  class="form-control" id="base_price_{{$service->id}}" value="{{old('base_price_'.$service->id,isset($base_price)?$base_price:'')}}">
                              </div>
                               <div class="col-md-2">
                                
                                   @if($service->id ==20 || $service->id==28)
                                     <label class=" control-label">Base Duration (hours)</label>
                                   @else
                                      <label class=" control-label">Base KM</label>
                                   @endif  
                                <input name="base_km_{{$service->id}}" type="text"  class="form-control" id="base_km_{{$service->id}}" value="{{old('base_km_'.$service->id,isset($base_km)?$base_km:'')}}">
                              </div>
                              <div class="col-md-2">
                               @if($service->id ==20 || $service->id==28)
                                     <label class=" control-label">Price/Hour</label>
                                @else
                                     <label class=" control-label">Price/KM</label>
                               @endif 
                               <input name="price_per_km_{{$service->id}}" type="text"  class="form-control" id="price_per_km_{{$service->id}}" value="{{old('price_per_km_'.$service->id,isset($per_km)?$per_km:'')}}">
                              </div>
                              </div>
                              </div>
                          <div class="row">    
                             <div class="col-md-12"> 
                                 <div class="col-md-2">
                                    <label class=" control-label">Price/Min</label>
                                      <input name="price_per_min_{{$service->id}}" type="text"  class="form-control" id="price_per_min_{{$service->id}}" value="{{old('price_per_min_'.$service->id,isset($per_min)?$per_min:'')}}">
                             
                                 </div>   
                              <div class="col-md-3">
                               <label class=" control-label">Checkpoint Distance</label>
                                <input name="check_point_distance_{{$service->id}}" type="text"  class="form-control" id="check_point_distance_{{$service->id}}" value="{{old('check_point_distance_'.$service->id,isset($check_point_distance)?$check_point_distance:'')}}">
                              </div>
                              <div class="col-md-3">
                               <label class=" control-label">Flat Price</label>
                                <input name="flat_price_{{$service->id}}" type="text"  class="form-control" id="flat_price_{{$service->id}}" value="{{old('flat_price_'.$service->id,isset($flat_price)?$flat_price:'')}}">
                              </div>
                              <div class="col-md-2">
                               <label class=" control-label">Sort Index</label>
                                <input name="sort_index_{{$service->id}}" type="text"  class="form-control" id="sort_index_{{$service->id}}" value="{{old('sort_index_'.$service->id,isset($sort_index)?$sort_index:'')}}">
                              </div>
                             
                              <div class="col-md-2">
                               <label class=" control-label">Sort Index Arabic</label>
                                <input name="sort_index_arabic_{{$service->id}}" type="text"  class="form-control" id="sort_index_arabic_{{$service->id}}" value="{{old('sort_index_arabic_'.$service->id,isset($sort_index_arabic)?$sort_index_arabic:'')}}">
                              </div>
                             </div>
                              </div>
                              --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
                              @endif
                             @endforeach
                        
                        
                    </div>           
                    </div>           
                    </div>           
                    @endforeach-->
                    
                 <div class="col-md-8">  
                     <div style="margin-bottom: 15px;">
                          <label class=" control-label">Payment Gateway<sup>*</sup></label>
                       
                          <!--<div class="col-md-6">-->     
                              <select name="payment_gateway"  class="form-control" id="payment_gateway">
                                  <option value="">--Select--</option>
                                  <option value="paytm" @if(old('payment_gateway',$main_info->payment_gateway)=='paytm') selected @endif>Paytm</option>
                                 
                              </select>
                            @if ($errors->has('payment_gateway'))
                              <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('payment_gateway') }}</strong>
                              </span>
                              @endif
                          <!--</div>-->
                       
                            </div>
                            
  
                 </div>     
                    <div class="col-md-8">
                        <div class="form-group">
                          <div class="col-md-12">   
                             <button type="submit" id="submit" class="btn btn-primary  pull-right">Update</button>
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
 @endsection