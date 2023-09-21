@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Update Service</title>

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
                <a href="{{url('admin/services-list')}}">Manage Services</a>
                <i class="fa fa-circle"></i>

            </li>
            <li>
                <a href="javascript:void(0);">Update Service</a>

            </li>
        </ul>
        <!-- BEGIN SAMPLE FORM PORTLET-->
        <div class="portlet box blue">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i> Update Service
                </div>

            </div>
            <div class="portlet-body form">
                <form class="form-horizontal" role="form" action="" method="post" enctype="multipart/form-data">

                    {!! csrf_field() !!}
                    <div class="form-body">
                        <div class="row">
                            <div class="col-md-12"> 
                                <div class="col-md-8">  
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Category<sup>*</sup></label>                       
                                        <div class="col-md-6">    
                                            <select class="form-control" onchange='getAllServices(this.value)' name="category" id="category">
                                                @foreach($categories as $category)
                                                <option value="{{$category->id}}" @if(old('category',$category->id)==$main_info->category_id) selected @endif>{{$category->name}}</option>
                                                @endforeach
                                            </select>
                                            @if ($errors->has('name'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('name') }}</strong>
                                            </span>
                                            @endif
                                        </div>

                                    </div>
                                </div> 
<!--                                 <div class="col-md-8">  
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Main Service</label>                       
                                        <div class="col-md-6">    
                                            <select class="form-control" name="parent_id" id='services_list'>
                                              <option value="0">--No Parent--</option>
                                                @foreach($services as $service_detaiils)
                                                 
                
                                                <option value="{{$service_detaiils->id}}" @if(old('parent_id',$service_detaiils->id)==$main_info->parent_id) selected @endif>{{$service_detaiils->name}}</option>
                                                
                                                @endforeach
                                            </select>
                                          
                                        </div>

                                    </div>
                                </div>-->
                                <div class="col-md-8">  
                                    <div class="form-group @if ($errors->has('name')) has-error @endif">
                                        <label class="col-md-6 control-label">Name<sup>*</sup></label>
                                        <div class="col-md-6">     
                                            <input name="name" type="text" class="form-control" id="name" value="{{old('name',$service->name)}}" />
                                            @if ($errors->has('name'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('name') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-8">
                                   
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Upload Service Image</label>
                                        <div class="col-md-6">     
                                            <input type="file" class="form-control" name="service_image" id="service_image">
                                            @if ($errors->has('service_image'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('service_image') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-6 control-label"></label>
                                        <div class="col-md-6" style="background-color: #ccc;"> 
                                            <?php if ($main_info->service_image != '') { ?>
                                                <img src="{{asset('storageasset/service-image/'.$main_info->service_image)}}" height="150" width="150">
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Upload Selected Service Image</label>
                                        <div class="col-md-6">     
                                            <input type="file" class="form-control" name="service_selected_image" id="service_selected_image">
                                            @if ($errors->has('service_selected_image'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('service_selected_image') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                     <div class="form-group">
                                        <label class="col-md-6 control-label"></label>
                                        <div class="col-md-6" style="background-color: #ccc;">
                                            <?php if ($main_info->service_selected_image != '') { ?>
                                                <img src="{{asset('storageasset/service-image/'.$main_info->service_selected_image)}}" height="150" width="150">
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                              <div class="col-md-8">  
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Type<sup>*</sup></label>
                                          <div class="col-md-6">     
                                              <input type='radio'  name='service_type' @if($main_info->service_type=='0' || $main_info->service_type=='') checked @endif id='service_type' value='0' >Instant                                  
                                              <input type='radio' name='service_type' id='service_type' @if($main_info->service_type=='1') checked @endif value='1' >Scheduled
                                              <input type='radio' name='service_type' id='service_type' @if($main_info->service_type=='2') checked @endif value='2' > Both (Instant and Schedule)
                                              <!--<input type='radio' name='service_type' id='service_type' @if($main_info->service_type=='3') checked @endif value='3' >Instant and Pickup now Deliver Later-->
                                          </div>

                                     </div>
                               </div>
<div class="col-md-8">  
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Required PickUp Address?<sup>*</sup></label>
                                          <div class="col-md-6">     
                                              <input type='radio'  name='required_pick_up_address' id='required_pick_up_address' value='0' @if(old('required_pick_up_address',$main_info->required_pick_up_address)=='0') checked @endif> No
                                              <input type='radio' name='required_pick_up_address' id='required_pick_up_address' value='1' @if(old('required_pick_up_address',$main_info->required_pick_up_address)=='1') checked @endif> Yes
                                              @if ($errors->has('required_pick_up_address'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('required_pick_up_address') }}</strong>
                                            </span>
                                            @endif
                                        </div>

                                    </div>

                              </div>
                             <div class="col-md-8">  
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Required Drop Up Address?<sup>*</sup></label>
                                          <div class="col-md-6">     
                                              <input type='radio'  name='required_drop_up_address' id='required_drop_up_address' value='0' @if(old('required_drop_up_address',$main_info->required_drop_up_address)=='0') checked @endif> No
                                              <input type='radio' name='required_drop_up_address' id='required_drop_up_address' value='1' @if(old('required_drop_up_address',$main_info->required_drop_up_address)=='1') checked @endif> Yes
                                              @if ($errors->has('required_drop_up_address'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('required_drop_up_address') }}</strong>
                                            </span>
                                            @endif
                                        </div>

                                    </div>

                              </div>
                                <div class="col-md-8">  
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Required Pickup person?<sup>*</sup></label>
                                          <div class="col-md-6">     
                                              <input type='radio'  name='required_pick_up_person' id='required_drop_up_address' value='0' @if(old('required_pick_up_person',$main_info->required_pick_up_person)=='0') checked @endif> No
                                              <input type='radio' name='required_pick_up_person' id='required_drop_up_address' value='1' @if(old('required_pick_up_person',$main_info->required_pick_up_person)=='1') checked @endif> Yes
                                              @if ($errors->has('required_pick_up_person'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('required_pick_up_person') }}</strong>
                                            </span>
                                            @endif
                                        </div>

                                    </div>

                              </div>
                                <div class="col-md-8">  
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Required Drop Off Person?<sup>*</sup></label>
                                          <div class="col-md-6">     
                                              <input type='radio'  name='required_drop_up_person' id='required_drop_up_person' value='0' @if(old('required_drop_up_person',$main_info->required_drop_up_person)=='0') checked @endif> No
                                              <input type='radio' name='required_drop_up_person' id='required_drop_up_person' value='1' @if(old('required_drop_up_person',$main_info->required_drop_up_person)=='1') checked @endif> Yes
                                              @if ($errors->has('required_drop_up_person'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('required_drop_up_person') }}</strong>
                                            </span>
                                            @endif
                                        </div>

                                    </div>

                              </div>
                             <div class="col-md-8">  
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Required Goods Description?<sup>*</sup></label>
                                          <div class="col-md-6">     
                                              <input type='radio'  name='required_goods_description' id='required_goods_description' value='0' @if(old('required_goods_description',$main_info->required_goods_description)=='0') checked @endif> No
                                              <input type='radio' name='required_goods_description' id='required_goods_description' value='1' @if(old('required_goods_description',$main_info->required_goods_description)=='1') checked @endif> Yes
                                              @if ($errors->has('required_goods_description'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('required_goods_description') }}</strong>
                                            </span>
                                            @endif
                                        </div>

                                    </div>

                              </div>
                              <div class="col-md-8">  
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Required Goods Images?<sup>*</sup></label>
                                          <div class="col-md-6">     
                                              <input type='radio'  name='required_goods_image' id='required_goods_image' value='0' @if(old('required_goods_image',$main_info->required_goods_image)=='0') checked @endif> No
                                              <input type='radio' name='required_goods_image' id='required_goods_image' value='1' @if(old('required_goods_image',$main_info->required_goods_image)=='1') checked @endif> Yes
                                              @if ($errors->has('required_goods_image'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('required_goods_image') }}</strong>
                                            </span>
                                            @endif
                                        </div>

                                    </div>

                              </div> 
                                <div class="col-md-8">  
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Number Of hours?<sup>*</sup></label>
                                          <div class="col-md-6">     
                                              <input type='radio'  name='no_of_hours' id='no_of_hours' value='0' @if(old('no_of_hours',$main_info->no_of_hours)=='0') checked @endif> No
                                              <input type='radio' name='no_of_hours' id='no_of_hours' value='1' @if(old('no_of_hours',$main_info->no_of_hours)=='1') checked @endif> Yes
                                              @if ($errors->has('no_of_hours'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('no_of_hours') }}</strong>
                                            </span>
                                            @endif
                                        </div>

                                    </div>

                              </div> 
                           
                                <div class="col-md-8">  
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Detail Pickup Address?<sup>*</sup></label>
                                          <div class="col-md-6">     
                                              <input type='radio'  name='pickup_detail_address' id='pickup_detail_address' value='0' @if(old('pickup_detail_address',$main_info->pickup_detail_address)=='0') checked @endif> No
                                              <input type='radio' name='pickup_detail_address' id='pickup_detail_address' value='1' @if(old('pickup_detail_address',$main_info->pickup_detail_address)=='1') checked @endif> Yes
                                              @if ($errors->has('pickup_detail_address'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('pickup_detail_address') }}</strong>
                                            </span>
                                            @endif
                                        </div>

                                    </div>

                              </div> 
                               <div class="col-md-8">  
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Detail Drop off Address?<sup>*</sup></label>
                                          <div class="col-md-6">     
                                              <input type='radio'  name='dropoff_detail_address' id='dropoff_detail_address' value='0' @if(old('dropoff_detail_address',$main_info->dropoff_detail_address)=='0') checked @endif> No
                                              <input type='radio' name='dropoff_detail_address' id='dropoff_detail_address' value='1' @if(old('dropoff_detail_address',$main_info->dropoff_detail_address)=='1') checked @endif> Yes
                                              @if ($errors->has('dropoff_detail_address'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('dropoff_detail_address') }}</strong>
                                            </span>
                                            @endif
                                        </div>

                                    </div>

                              </div>  
                                <div class="col-md-8">  
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Fuel Price Field?<sup>*</sup></label>
                                          <div class="col-md-6">     
                                              <input type='radio'  name='fuel_price_field' id='fuel_price_field' value='0' @if(old('fuel_price_field',$main_info->fuel_price_field)=='0') checked @endif> No
                                              <input type='radio' name='fuel_price_field' id='fuel_price_field' value='1' @if(old('fuel_price_field',$main_info->fuel_price_field)=='1') checked @endif> Yes
                                              @if ($errors->has('fuel_price_field'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('fuel_price_field') }}</strong>
                                            </span>
                                            @endif
                                        </div>

                                    </div>
 </div> 
                         
<!--                                <div class="col-md-8">  
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Number of person limit Display?<sup>*</sup></label>
                                          <div class="col-md-6">     
                                              <input type='radio'  name='show_number_of_person_limit' id='show_number_of_person_limit' value='0' @if(old('show_number_of_person_limit',$main_info->show_number_of_person_limit)=='0') checked @endif> No
                                              <input type='radio' name='show_number_of_person_limit' id='show_number_of_person_limit' value='1' @if(old('show_number_of_person_limit',$main_info->show_number_of_person_limit)=='1') checked @endif> Yes
                                              @if ($errors->has('show_number_of_person_limit'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('show_number_of_person_limit') }}</strong>
                                            </span>
                                            @endif
                                        </div>

                                    </div>

                              </div> -->
<!--                               <div class="col-md-8">  
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Is Sharable?<sup>*</sup></label>
                                          <div class="col-md-6">     
                                              <input type='radio'  name='is_sharable' id='is_sharable' value='0' @if(old('is_sharable',$main_info->is_sharable)=='0') checked @endif> No
                                              <input type='radio' name='is_sharable' id='is_sharable' value='1' @if(old('is_sharable',$main_info->is_sharable)=='1') checked @endif> Yes
                                              @if ($errors->has('is_sharable'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('is_sharable') }}</strong>
                                            </span>
                                            @endif
                                        </div>

                                    </div>

                              </div> -->
                                <div class="col-md-8">  
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Number of person limit?<sup>*</sup></label>
                                          <div class="col-md-6">     
                                              <input type='text' class="form-control" name='number_of_person_limit' id='number_of_person_limit' value="{{old('number_of_person_limit',$main_info->number_of_person_limit)}}">
                                              @if ($errors->has('number_of_person_limit'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('number_of_person_limit') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                              </div> 
                              <div class="col-md-8">  
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Min Range (in Km)<sup>*</sup></label>
                                          <div class="col-md-6">     
                                              <input type='text'  class="form-control" value="{{old('min_range',$main_info->min_range)}}" name='min_range' id='min_range'>
                                              @if ($errors->has('min_range'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('min_range') }}</strong>
                                            </span>
                                            @endif
                                        </div>

                                    </div>

                              </div> 
                                  <div class="col-md-8">  
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Max Range (In Km)<sup>*</sup></label>
                                          <div class="col-md-6">     
                                              <input type='text' class="form-control" value="{{old('max_range',$main_info->max_range)}}" name='max_range' id='max_range'>
                                              @if ($errors->has('max_range'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('max_range') }}</strong>
                                            </span>
                                            @endif
                                        </div>

                                    </div>

                              </div>   
                                <div class="col-md-8">  
                                    <div class="form-group">
                                        <label class="col-md-6 control-label">Description (optional)</label>

                                        <div class="col-md-6">     
                                            <textarea name="description" class="form-control" id="description">{{old('description',$service->description)}}</textarea>

                                        </div>

                                    </div>
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

 <script>
            
        function getAllServices(category_id)
        {
            if(category_id!='' && category_id!=0)
            {
                $.ajax({
                   url:"{{url('/admin/service_details/get-service-by-category')}}/"+category_id,
                   method:'get',
                   success:function(data)
                   {

                        $("#services_list").html(data);

                   }

                });
            }
        }
 </script>
@endsection