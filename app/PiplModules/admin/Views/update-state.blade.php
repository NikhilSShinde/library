@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Update Region</title>

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
					<a href="{{url('admin/states/list')}}">Manage Regions</a>
                                        <i class="fa fa-circle"></i>
					
				</li>
				<li>
					<a href="javascript:void(0);">Update Region</a>
					
				</li>
                        </ul>
      <!-- BEGIN SAMPLE FORM PORTLET-->
        <div class="portlet box blue">
             <div class="portlet-title">
                        <div class="caption">
                                <i class="fa fa-gift"></i> Update Region
                        </div>

             </div>
             <div class="portlet-body form">
  	     <form class="form-horizontal" id="frm_state_update" name="frm_state_update" role="form" action="" method="post" >
            
                 {!! csrf_field() !!}
                 <div class="form-body">
                   <div class="row">
                        <div class="col-md-12">    
                        <div class="col-md-8">  
                         <div class="form-group">
                          <label class="col-md-6 control-label">Name<sup>*</sup></label>
                          
                            <div class="col-md-6">     
                             <input name="name" type="text" class="form-control" id="name" value="{{old('name',$state_info->name)}}">
                              @if ($errors->has('name'))
                              <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('name') }}</strong>
                              </span>
                              @endif
                          </div>
                       
                      </div>
                        <div class="form-group">
                          <label class="col-md-6 control-label">Choose Country<sup>*</sup></label>
                       
                            <div class="col-md-6">     
                                <select name="country" id="country" class="form-control">
                                  <option value="">--Select--</option>
                                 @foreach($countries as $country)
                                 <option value="{{$country->id}}" @if(old('country',$state->country_id)==$country->id) selected @endif>{{$country->name}}</option>
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

 @endsection
  