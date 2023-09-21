@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Update Content Page Info</title>

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
					<a href="{{url('admin/content-pages/list')}}">Manage Content Pages</a>
                                        <i class="fa fa-circle"></i>
					
				</li>
				<li>
					<a href="javascript:void(0);">Update Content Page</a>
					
				</li>
                        </ul>
      <!-- BEGIN SAMPLE FORM PORTLET-->
        <div class="portlet box blue">
             <div class="portlet-title">
                        <div class="caption">
                                <i class="fa fa-gift"></i> Update Content Page
                        </div>

             </div>
             <div class="portlet-body form">
                <form name="frm_cms_update" id="frm_cms_update" class="form-horizontal" role="form"  method="post" >
            
                 {!! csrf_field() !!}
                 <div class="form-body">
                   <div class="row">
                        <div class="col-md-12">    
                      <div class="col-md-9">  
                         <div class="form-group  @if ($errors->has('page_title')) has-error @endif">
                          <label for="page_title" class="col-md-3 control-label">Name<sup>*</sup></label>
                       
                            <div class="col-md-9">     
                             <input class="form-control" name="page_title" value="{{old('page_title',$page_information->page_title)}}" />
                            @if ($errors->has('page_title'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('page_title') }}</strong>
                                    </span>
                             @endif
                          </div>
                       
                      </div>
                          <div class="form-group  @if ($errors->has('page_content')) has-error @endif">
                          <label for="page_content" class="col-md-3 control-label">Page Contents<sup>*</sup></label>
                       
                            <div class="col-md-9">     
                            <textarea class="form-control" name="page_content">{{old('page_content',$page_information->page_content)}}</textarea>
                           @if ($errors->has('page_content'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('page_content') }}</strong>
                                    </span>
                            @endif
                          </div>
                       
                      </div>
                          <div class="form-group  @if ($errors->has('page_alias')) has-error @endif">
                          <label for="page_alias" class="col-md-3 control-label">Page Alias<sup>*</sup></label>
                       
                            <div class="col-md-9">     
                                <input class="form-control" readonly="" name="page_alias" value="{{old('page_alias',$page->page_alias)}}" />
                           (Please make sure, it  has unique and valid url (Please don't use any special character) Valid url (Exp: about-us)
                               @if ($errors->has('page_alias'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('page_alias') }}</strong>
                                    </span>
                                @endif
        
                          </div>
                       
                      </div>
                    <div class="form-group  @if ($errors->has('page_status')) has-error @endif">
                          <label for="page_status" class="col-md-3 control-label">Page Status<sup>*</sup></label>
                       
                            <div class="col-md-9">     
                                <select class='form-control' name="page_status" id="page_status">
                                    <option value="">--Select--</option>
                                    <option value="1" @if($page->page_status=='1') selected @endif>Published</option>
                                    <option value="0"  @if($page->page_status=='0') selected @endif>UnPublished</option>
                                </select>
                               @if ($errors->has('page_status'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('page_status') }}</strong>
                                    </span>
                                @endif
        
                          </div>
                       
                      </div>
                       
                     <div class="form-group  @if ($errors->has('page_seo_title')) has-error @endif">
                          <label for="page_alias" class="col-md-3 control-label">Page SEO Title <sup>*</sup></label>
                            <div class="col-md-9">     
                               <input class="form-control" name="page_seo_title" value="{{old('page_seo_title',$page_information->page_seo_title)}}" />
                               @if ($errors->has('page_seo_title'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('page_seo_title') }}</strong>
                                    </span>
                                @endif
        
                          </div>
                      </div>
                      <div class="form-group  @if ($errors->has('page_meta_keywords')) has-error @endif">
                          <label for="page_alias" class="col-md-3 control-label">Page Meta Keywords<sup>*</sup></label>
                       
                            <div class="col-md-9">     
                            <textarea class="form-control" name="page_meta_keywords" >{{old('page_meta_keywords',$page_information->page_meta_keywords)}}</textarea>
                               @if ($errors->has('page_meta_keywords'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('page_meta_keywords') }}</strong>
                                    </span>
                                @endif
        
                          </div>
                       
                      </div>
                          <div class="form-group  @if ($errors->has('page_meta_keywords')) has-error @endif">
                          <label for="page_alias" class="col-md-3 control-label">Page Meta Descriptions<sup>*</sup></label>
                       
                            <div class="col-md-9">     
                           <textarea class="form-control" name="page_meta_descriptions" >{{old('page_meta_descriptions',$page_information->page_meta_descriptions)}}</textarea>
                               @if ($errors->has('page_meta_descriptions'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('page_meta_descriptions') }}</strong>
                                    </span>
                                @endif
        
                          </div>
                       
                      </div>
                  <div class="form-group">
                         <div class="col-md-12">   
                            <button type="submit" id="submit" class="btn btn-primary  pull-right">Update Content Page</button>
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
   <script src="{{url('/vendor/unisharp/laravel-ckeditor/ckeditor.js')}}"></script> 
<script>
        CKEDITOR.replace( 'page_content' );
    </script>  
 @endsection