@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Update Newsletter</title>

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
					<a href="{{url('admin/newsletters')}}">Manage Newsletters</a>
                                        <i class="fa fa-mail-forward"></i>
					
				</li>
				<li>
					<a href="javascript:void(0);">Update Newsletter</a>
					
				</li>
                        </ul>

  
    
      <div class="portlet box blue">
             <div class="portlet-title">
                        <div class="caption">
                                <i class="fa fa-gift"></i> Update Newsletter
                        </div>

             </div>
             <div class="portlet-body form">
                 
                 <form class="form-horizontal"role="form" method="post" enctype="multipart/form-data">
              
                 {!! csrf_field() !!}
                 <div class="form-body">
                   <div class="row">
                        <div class="col-md-12">    
                      <div class="col-md-9">  
                         <div class="form-group  @if ($errors->has('subject')) has-error @endif">
                          <label for="page_title" class="col-md-3 control-label">Subject<sup>*</sup></label>
                       
                            <div class="col-md-9">     
                             <input class="form-control" name="subject" value="{{old('subject',$newsletter->subject)}}" />
                            @if ($errors->has('subject'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('subject') }}</strong>
                                    </span>
                             @endif
                          </div>
                       
                      </div>
                          <div class="form-group  @if ($errors->has('content')) has-error @endif">
                          <label for="page_content" class="col-md-3 control-label">Contents<sup>*</sup></label>
                       
                            <div class="col-md-9">     
                            <textarea class="form-control" name="content">{{old('content',$newsletter->content)}}</textarea>
                           @if ($errors->has('content'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('content') }}</strong>
                                    </span>
                            @endif
                          </div>
                      </div>
                    <div class="form-group  @if ($errors->has('page_status')) has-error @endif">
                          <label for="page_status" class="col-md-3 control-label">Page Status<sup>*</sup></label>
                       
                            <div class="col-md-9">     
                                <select class='form-control' name="status" id="status">
                                    <option value="">--Select--</option>
                                    <option value="1" @if(old('status',$newsletter->status)=='1') selected @endif>Published</option>
                                    <option value="0" @if(old('status',$newsletter->status)=='0') selected @endif >UnPublished</option>
                                </select>
                               @if ($errors->has('status'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('status') }}</strong>
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
        <style>
            .submit-btn{
                padding: 10px 0px 0px 18px;
            }
        </style>
<script src="{{url('/vendor/unisharp/laravel-ckeditor/ckeditor.js')}}"></script> 
<script>
        CKEDITOR.replace( 'content' );
    </script> 
</section>
@endsection