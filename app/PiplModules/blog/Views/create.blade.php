@extends(config("piplmodules.back-view-layout-location"))

@section("meta")
<title>Create Blog post</title>
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
					<a href="{{url('admin/blog')}}">Manage Blogs</a>
                                        <i class="fa fa-circle"></i>
					
				</li>
				<li>
					<a href="javascript:void(0);">Create Blog</a>
					
				</li>
                        </ul>

  
    
      <!-- BEGIN SAMPLE FORM PORTLET-->
        <div class="portlet box blue">
             <div class="portlet-title">
                        <div class="caption">
                                <i class="fa fa-gift"></i> Create Blog
                        </div>

             </div>
             <div class="portlet-body form">
                 <form class="form-horizontal" role="form" action="" method="post" enctype="multipart/form-data">
            
                 {!! csrf_field() !!}
                 <div class="form-body">
                   <div class="row">
                        <div class="col-md-12">    
                        <div class="col-md-8">  
                         <div class="form-group @if ($errors->has('title')) has-error @endif">
                          <label class="col-md-6 control-label">Title<sup>*</sup></label>
                       
                            <div class="col-md-6">     
                              <input class="form-control" name="title" value="{{old('title')}}" />
                               @if ($errors->has('title')) 
                                    <span class="help-block"> 
                                        <strong class="text-danger">{{ $errors->first('title') }}</strong> 
                                    </span>
                               @endif
                          </div>
                       
                      </div>
                       <div class="form-group @if ($errors->has('short_description')) has-error @endif">
                          <label class="col-md-6 control-label">Short Description <sup>*</sup></label>
                          @if ($errors->has('question'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('short_description') }}</strong>
                                    </span>
                             @endif
                            <div class="col-md-6">     
                                <textarea class="form-control" name="short_description" >{{old('short_description')}}</textarea>
                            @if ($errors->has('short_description'))
                              <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('short_description') }}</strong>
                              </span>
                              @endif
                          </div>
                       
                      </div>
                     <div class="form-group @if ($errors->has('url')) has-error @endif">
                          <label class="col-md-6 control-label">URL<sup>*</sup></label>
                            <div class="col-md-6">     
                              <input class="form-control" name="url" value="{{old('url')}}" />
                               @if ($errors->has('url')) 
                                    <span class="help-block"> 
                                        <strong class="text-danger">{{ $errors->first('url') }}</strong> 
                                    </span>
                               @endif
                          </div>
                       
                      </div>         
                   @if(count($tree)>0 && empty($locale))
                     <div class="form-group @if ($errors->has('category')) has-error @endif">
                        <label for="category" class="col-md-6 control-label">Choose Category </label>
                      <div class="col-md-6">     
                        <select name="category" class="form-control">
                           <option value="0">No Category</option>
                                    @foreach($tree as $ls_category)
                                    <option value="{{$ls_category->id}}">{{$ls_category->display}}</option>
                                    @endforeach
                        </select>
                            @if ($errors->has('category'))
                                <span class="help-block">
                                    <strong class="text-danger">{{ $errors->first('category') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                    @endif 
                        
                        <div class="form-group @if ($errors->has('photo')) has-error @endif">
                          <label class="col-md-6 control-label">Image<sup>*</sup></label>
                            <div class="col-md-6">
                               <input type="file" class="form-control" name="photo" />
                                    @if ($errors->has('photo')) <span class="help-block"> <strong class="text-danger">{{ $errors->first('photo') }}</strong> </span> @endif </div>
                
                          </div>
                       
                       <div class="form-group @if ($errors->has('allow_comments')) has-error @endif">
                          <label class="col-md-6 control-label">Allow comments?<sup>*</sup></label>
                            <div class="col-md-6">     
                               <label class="radio-inline"><input type="radio" name="allow_comments" value="1" >Yes</label> <label class="radio-inline"><input type="radio" name="allow_comments" value="0" >No</label>
                               @if ($errors->has('allow_comments')) <span class="help-block"> <strong class="text-danger">{{ $errors->first('allow_comments') }}</strong> </span> @endif 
                          </div>
                       
                      </div> 
                     <div class="form-group @if ($errors->has('allow_attachments_in_comments')) has-error @endif">
                          <label class="col-md-6 control-label">Allow attachments in comments?</label>
                            <div class="col-md-6">     
                               <label class="radio-inline"><input type="radio" name="allow_attachments_in_comments" value="1">Yes</label> <label class="radio-inline"><input type="radio" name="allow_attachments_in_comments" value="0">No</label>
                               @if ($errors->has('allow_attachments_in_comments')) <span class="help-block"> <strong class="text-danger">{{ $errors->first('allow_attachments_in_comments') }}</strong> </span> @endif 
                          </div>
                       
                      </div> 
                     <div class="form-group @if ($errors->has('allow_attachments_in_comments')) has-error @endif">
                          <label class="col-md-6 control-label">Publish Status</label>
                            <div class="col-md-6">     
                             <label class="radio-inline"><input type="radio" name="post_status" value="1" >Published</label> <label class="radio-inline"><input type="radio" name="post_status" value="0">Not Published</label>
                             @if ($errors->has('post_status')) <span class="help-block"> <strong class="text-danger">{{ $errors->first('post_status') }}</strong> </span> @endif 
                          </div>
                       
                      </div> 
                     <div class="form-group @if ($errors->has('tags')) has-error @endif">
                          <label class="col-md-6 control-label">Tags</label>
                            <div class="col-md-6">
                               <input type="text" id="tags" class="form-control" name="tags" value="{{old('tags')}}"  />
                                 @if ($errors->has('tags'))
                                        <span class="help-block">
                                            <strong class="text-danger">{{ $errors->first('tags') }}</strong>
                                        </span>
                                    @endif
                          </div>
                       
                      </div> 
                      <div class="form-group @if ($errors->has('seo_title')) has-error @endif">
                          <label class="col-md-6 control-label">SEO Title<sup>*</sup></label>
                       
                            <div class="col-md-6">     
                                <input class="form-control" name="seo_title" value="{{old('seo_title')}}" />
                                @if ($errors->has('seo_title')) <span class="help-block"> <strong class="text-danger">{{ $errors->first('seo_title') }}</strong> </span> @endif
                          </div>
                       
                      </div>
                    <div class="form-group @if ($errors->has('seo_keywords')) has-error @endif">
                          <label class="col-md-6 control-label">SEO Keyword<sup>*</sup></label>
                       
                            <div class="col-md-6">     
                                <input class="form-control" name="seo_keywords" value="{{old('seo_keywords')}}" />
                                @if ($errors->has('seo_keywords')) <span class="help-block"> <strong class="text-danger">{{ $errors->first('seo_keywords') }}</strong> </span> @endif
                          </div>
                       
                      </div>
                     <div class="form-group @if ($errors->has('seo_description')) has-error @endif">
                          <label class="col-md-6 control-label">SEO Description<sup>*</sup></label>
                       
                            <div class="col-md-6">     
                                <input class="form-control" name="seo_description" value="{{old('seo_description')}}" />
                                @if ($errors->has('seo_description')) <span class="help-block"> <strong class="text-danger">{{ $errors->first('seo_description') }}</strong> </span> @endif
                          </div>
                       
                      </div>
                     
                      <div class="form-group @if ($errors->has('attachments')) has-error @endif">
                          <label class="col-md-6 control-label">Attachments</label>
                            <div class="col-md-6">
                                 <input type="file" class="form-control" multiple="multiple" name="attachments[]" />
                                 @if ($errors->has('attachments')) <span class="help-block"> <strong class="text-danger">{{ $errors->first('attachments') }}</strong> </span> @endif </div>
                                
                      </div>
                         
                      <div class="form-group">
                           <label class="col-md-6 control-label">Description</label>
                         <div class="col-md-6">   
                             <textarea class="form-control" name="description" id="description" >{{old('description')}}</textarea>
                         </div>
                    </div>
                     
                    <div class="form-group">
                         <div class="col-md-12">   
                            <button type="submit" id="submit" class="btn btn-primary  pull-right">Create</button>
                         </div>
                  </div>
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
   <!--<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">-->
<!--  <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>-->
  <script src="{{url('/vendor/unisharp/laravel-ckeditor/ckeditor.js')}}"></script> 
<script>
        CKEDITOR.replace( 'description' );
    </script>  
@endsection