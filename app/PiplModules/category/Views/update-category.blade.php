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
					<a href="{{url('admin/categories-list')}}">Manage Services</a>
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
                 <form class="form-horizontal" role="form" action="" method="post" >
            
                 {!! csrf_field() !!}
                 <div class="form-body">
                   <div class="row">
                      <div class="col-md-12">    
                        <div class="col-md-8">  
                         <div class="form-group @if ($errors->has('name')) has-error @endif">
                          <label class="col-md-6 control-label">Name<sup>*</sup></label>
                       
                            <div class="col-md-6">     
                            <input name="name" type="text" class="form-control" id="name" value="{{old('name',$category->name)}}" />
                            @if ($errors->has('name'))
                              <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('name') }}</strong>
                              </span>
                              @endif
                          </div>
                       
                      </div>
                   
                    </div>
                        <div class="col-md-8">  
                         <div class="form-group @if ($errors->has('name')) has-error @endif">
                          <label class="col-md-6 control-label">Request Range (KM)<sup>*</sup></label>
                       
                            <div class="col-md-6">     
                            <input name="request_range" type="text" class="form-control" id="request_range" value="{{old('request_range',$main_info->request_range)}}" />
                            @if ($errors->has('request_range'))
                              <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('request_range') }}</strong>
                              </span>
                              @endif
                          </div>
                       
                      </div>
                   
                    </div>
               <div class="col-md-8">  
                      <div class="form-group">
                          <label class="col-md-6 control-label">Is drop off location required<sup>*</sup></label>
                            <div class="col-md-6">     
                                <input type='radio'  name='is_drop_location' id='is_drop_location' value='0' @if(old('is_drop_location',$main_info->is_drop_location)=='0') checked @endif> No
                                <input type='radio' name='is_drop_location' id='is_drop_location' value='1' @if(old('is_drop_location',$main_info->is_drop_location)=='1') checked @endif> Yes
                                @if ($errors->has('is_drop_location'))
                              <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('is_drop_location') }}</strong>
                              </span>
                              @endif
                          </div>
                       
                      </div>
                     
                </div>
                  <div class="col-md-8">  
                      <div class="form-group">
                          <label class="col-md-6 control-label">Is number of users required<sup>*</sup></label>
                            <div class="col-md-6">     
                                <input type='radio'  name='number_of_person' id='number_of_person' value='0' @if(old('number_of_person',$main_info->number_of_person)=='0') checked @endif> No
                                    <input type='radio' name='number_of_person' id='number_of_person' value='1' @if(old('number_of_person',$main_info->number_of_person)=='1') checked @endif> Yes
                                @if ($errors->has('number_of_person'))
                              <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('number_of_person') }}</strong>
                              </span>
                              @endif
                          </div>
                       
                      </div>
                     
                </div>
                  <div class="col-md-8">  
                      <div class="form-group">
                          <label class="col-md-6 control-label">Description (optional)</label>
                       
                            <div class="col-md-6">     
                                <textarea name="description" class="form-control" id="description">{{old('description',$category->description)}}</textarea>
                           
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
 @endsection