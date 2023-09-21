@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Update Testimonial</title>

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
					<a href="{{url('admin/testimonials/list')}}">Manage Testimonials</a>
                                        <i class="fa fa-circle"></i>
					
				</li>
				<li>
					<a href="javascript:void(0);">Update Testimonial</a>
					
				</li>
                        </ul>

  
    
      <!-- BEGIN SAMPLE FORM PORTLET-->
        <div class="portlet box blue">
             <div class="portlet-title">
                        <div class="caption">
                                <i class="fa fa-gift"></i> Update A Testimonial
                        </div>

             </div>
             <div class="portlet-body form">
                 <form class="form-horizontal" enctype="multipart/form-data" role="form" action="" method="post" >
            
                 {!! csrf_field() !!}
                 <div class="form-body">
                   <div class="row">
                        <div class="col-md-12">    
                        <div class="col-md-8">  
                         <div class="form-group @if ($errors->has('name')) has-error @endif">
                          <label class="col-md-6 control-label">Name<sup>*</sup></label>
                       
                            <div class="col-md-6">     
                            <input name="name" type="text" class="form-control" id="name" value="{{old('name',$testimonial->name)}}">
                            @if ($errors->has('name'))
                              <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('name') }}</strong>
                              </span>
                              @endif
                          </div>
                       
                        </div>
                       <div class="form-group @if ($errors->has('user_description')) has-error @endif">
                          <label class="col-md-6 control-label">User's Short Description<sup>*</sup></label>
                       
                            <div class="col-md-6">     
                           <textarea class="form-control" name="user_description">{{old('user_description',$testimonial->user_description)}}</textarea>
                             @if ($errors->has('user_description'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('user_description') }}</strong>
                                    </span>
                             @endif
                          </div>
                       
                        </div>
                        <div class="form-group @if ($errors->has('user_description')) has-error @endif">
                          <label class="col-md-6 control-label">Testimonial<sup>*</sup></label>
                           <div class="col-md-6">     
                            <textarea class="form-control" name="description">{{old('description',$testimonial->description)}}</textarea>
                              @if ($errors->has('description'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('description') }}</strong>
                                    </span>
                            @endif
        
                          </div>
                       
                        </div>
                        <div class="form-group @if ($errors->has('photo')) has-error @endif">
                          <label class="col-md-6 control-label">Photo</label>
                           <div class="col-md-6">     
                             <span class="input-group-addon" id="basic-addon3"><img width="25" onclick="window.open('{{asset('storage/testimonials/'.$testimonial->photo)}}','Image','width=200,height=200,left=('+screen.width-200+'),top=('+screen.height-200+')')" src="{{asset('storage/testimonials/'.$testimonial->photo)}}" style="cursor:pointer" alt="{{$testimonial->photo}}" title="Click to see big image" /></span>
                             <input type="file" class="form-control" name="photo">
                               @if ($errors->has('photo'))
                                <span class="help-block">
                                    <strong class="text-danger">{{ $errors->first('photo') }}</strong>
                                </span>
                               @endif
        
                          </div>
                       
                        </div>
                         <div class="form-group @if ($errors->has('publish_status')) has-error @endif">
                          <label class="col-md-6 control-label">Publish Status <sup>*</sup></label>
                           <div class="col-md-6">     
                             <div class="radio-list">
                                            <label class="radio-inline">
                                            <input type="radio" name="publish_status" id="unpublish" value="0" @if(old("publish_status",$testimonial->status) === "0") checked @endif> Unpublished </label>
                                            <label class="radio-inline">
                                            <input type="radio" name="publish_status" id="publish" value="1" @if(old("publish_status",$testimonial->status) === "1") checked @endif> Published </label>
                                            
                          </div>
                            @if ($errors->has('publish_status'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('publish_status') }}</strong>
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