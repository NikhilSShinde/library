@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Update City</title>

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
					<a href="{{url('admin/cities')}}">Manage Cities</a>
                                        <i class="fa fa-circle"></i>
					
				</li>
				<li>
					<a href="javascript:void(0);">Update City</a>
					
				</li>
                        </ul>
      <!-- BEGIN SAMPLE FORM PORTLET-->
        <div class="portlet box blue">
           <div class="portlet-title">
                <div class="caption">
                        <i class="fa fa-gift"></i> Update city
                </div>
           </div>
            <div class="portlet-body form">
  	      <form class="form-horizontal" role="form" id="frm_city_update" name="frm_city_update" action="" method="post" >
                 {!! csrf_field() !!}
                 <div class="form-body">
                   <div class="row">
                        <div class="col-md-12">    
                        <div class="col-md-8">  
                         <div class="form-group">
                          <label class="col-md-6 control-label">Name<sup>*</sup></label>
                            <div class="col-md-6">     
                            <input name="name" type="text" class="form-control" id="name" value="{{old('name',$city_info->name)}}">
                            @if ($errors->has('name'))
                              <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('name') }}</strong>
                              </span>
                              @endif
                          </div>
                      </div>
                         <div class="form-group">
                          <label class="col-md-6 control-label">Support Number<sup>*</sup></label>
                            <div class="col-md-6">     
                            <input name="support_number" type="text" class="form-control" id="support_number" value="{{old('support_number',$city->support_number)}}">
                            @if ($errors->has('support_number'))
                              <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('support_number') }}</strong>
                              </span>
                              @endif
                          </div>
                       
                      </div>
                        <div class="form-group">
                          <label class="col-md-6 control-label">Choose Country<sup>*</sup></label>
                       
                            <div class="col-md-6">     
                                <select name="country" id="country" onchange="getAllStates(this.value)" class="form-control">
                                    <option value="" selected="">--Select--</option>
                            @foreach($countries as $country)
                                <option value="{{$country->id}}" @if($country->id==$city->country_id) selected @endif>{{$country->name}}</option>
                            @endforeach
                            </select>
                            @if ($errors->has('country'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('country') }}</strong>
                                    </span>
                            @endif
                          </div>
                       
                      </div>
                      <div class="form-group">
                          <label class="col-md-6 control-label">Choose State<sup>*</sup></label>
                       
                            <div class="col-md-6">     
                            <select name="state" id="state" class="form-control">
                               <option value="">--Select--</option>
                                @foreach($states as $state)
                                <option value="{{$state->id}}" @if($state->id==$city->state_id) selected @endif>{{$state->name}}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('state'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('state') }}</strong>
                                    </span>
                            @endif
                          </div>
                       
                      </div>
                        </div>
                  @foreach($categories as $category)
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
                              <!--<div class="form-group">-->
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
                                        $night_percentage=$user_service->night_percentage;
                                        $night_time_to=$user_service->night_time_to;
                                        $night_time_from=$user_service->night_time_from;
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
<!--                              <div class="col-md-2">
                                  <input type="radio" name="is_sharable_{{$service->id}}" value="0" @if(isset($is_sharable) && $is_sharable=='0') checked @endif  id="is_sharable"/> No Sharable
                                <input type="radio" name="is_sharable_{{$service->id}}" value="1"  @if(isset($is_sharable) && $is_sharable=='1') checked @endif id="is_sharable" /> Sharable
                              </div>-->
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
                                     <label class=" control-label">Price/100 Meters</label>
                               @endif 
                               <input name="price_per_km_{{$service->id}}" type="text"  class="form-control" id="price_per_km_{{$service->id}}" value="{{old('price_per_km_'.$service->id,isset($per_km)?$per_km:'')}}">
                              </div>
                              </div>
                              </div>
                          <div class="row">    
                             <div class="col-md-12"> 
                                 <div class="col-md-2">
                                    <label class=" control-label">Price/Min (Default 1)</label>
                                      <input  name="price_per_min_{{$service->id}}" type="text"  class="form-control" id="price_per_min_{{$service->id}}" value="{{old('price_per_min_'.$service->id,isset($per_min)?$per_min:'')}}">
                             
                                 </div>   
                                 <div class="col-md-2">
                                   <label class=" control-label">Night Percentage (Default 0)</label>
                                    <input name="night_percentage_{{$service->id}}" type="text"  class="form-control" id="night_percentage_{{$service->id}}" value="{{old('night_percentage_'.$service->id,isset($night_percentage)?$night_percentage:'')}}">
                               </div>   
<!--                              <div class="col-md-3">
                               <label class=" control-label">Checkpoint Distance</label>
                                <input name="check_point_distance_{{$service->id}}" type="text"  class="form-control" id="check_point_distance_{{$service->id}}" value="{{old('check_point_distance_'.$service->id,isset($check_point_distance)?$check_point_distance:'')}}">
                              </div>
                              <div class="col-md-3">
                               <label class=" control-label">Flat Price</label>
                                <input name="flat_price_{{$service->id}}" type="text"  class="form-control" id="flat_price_{{$service->id}}" value="{{old('flat_price_'.$service->id,isset($flat_price)?$flat_price:'')}}">
                              </div>-->
                              <div class="col-md-2">
                               <label class=" control-label">Night Time From</label>
                                <input name="night_time_from_{{$service->id}}" type="text"  class="form-control" id="night_time_from_{{$service->id}}" value="{{old('night_time_from_'.$service->id,isset($night_time_from)?$night_time_from:'')}}">PM
                              </div>
                             
                              <div class="col-md-2">
                               <label class=" control-label">Night Time To</label>
                                <input name="night_time_to_{{$service->id}}" type="text"  class="form-control" id="night_time_to_{{$service->id}}" value="{{old('night_time_to_'.$service->id,isset($night_time_to)?$night_time_to:'')}}">AM
                              </div>
                             
                             </div>
                              </div>
                              --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
                              @endif
                             @endforeach
                        
                        
                    </div>           
                    </div>           
                    </div>           
                    @endforeach
                            
                     <div class="form-group">
                         <div class="col-md-12">   
                            <button type="submit" id="submit" class="btn btn-primary  pull-right">Update</button>
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
            
        function getAllStates(country_id)
        {
            if(country_id!='' && country_id!=0)
            {
                $.ajax({
                   url:"{{url('/admin/states/getAllStates')}}/"+country_id,
                   method:'get',
                   success:function(data)
                   {

                        $("#state").html(data);

                   }

                });
            }
        }
 </script>
        
 @endsection
  