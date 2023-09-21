@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Update Blog Post</title>

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
                <a href="javascript:void(0);">Update Blog</a>

            </li>
        </ul>



        <!-- BEGIN SAMPLE FORM PORTLET-->
        <div class="portlet box blue">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i>Update Blog
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
                                            <input class="form-control" name="title" value="{{old('title',$post->title)}}" />
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
                                            <textarea class="form-control" name="short_description" >{{old('short_description',$post->short_description)}}</textarea>
                                            @if ($errors->has('short_description'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('short_description') }}</strong>
                                            </span>
                                            @endif
                                        </div>

                                    </div>
                                    <div class="form-group @if ($errors->has('description')) has-error @endif">
                                        <label class="col-md-6 control-label">Description <sup>*</sup></label>
                                        @if ($errors->has('question'))
                                        <span class="help-block">
                                            <strong class="text-danger">{{ $errors->first('description') }}</strong>
                                        </span>
                                        @endif
                                        <div class="col-md-6">     
                                            <textarea class="form-control" name="description" id="description" >{{old('description',$post->description)}}</textarea>
                                            @if ($errors->has('description'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('description') }}</strong>
                                            </span>
                                            @endif
                                        </div>

                                    </div>
                                    
                                    <div class="form-group @if ($errors->has('url')) has-error @endif">
                                        <label class="col-md-6 control-label">URL<sup>*</sup></label>
                                        <div class="col-md-6">     
                                            <input class="form-control" name="url" value="{{old('url',$ori_post->post_url)}}" readonly="" />
                                            @if ($errors->has('url')) 
                                            <span class="help-block"> 
                                                <strong class="text-danger">{{ $errors->first('url') }}</strong> 
                                            </span>
                                            @endif
                                        </div>

                                    </div>         

                                    <div class="form-group @if ($errors->has('tags')) has-error @endif">
                                        <label class="col-md-6 control-label">Tags</label>
                                        <div class="col-md-6">
                                            <input type="text" id="tags" class="form-control" name="tags" value="{{old('tags',$post_tags)}}"  />
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
                                            <input class="form-control" name="seo_title" value="{{old('seo_title',$post->seo_title)}}" />
                                            @if ($errors->has('seo_title')) <span class="help-block"> <strong class="text-danger">{{ $errors->first('seo_title') }}</strong> </span> @endif
                                        </div>

                                    </div>
                                    <div class="form-group @if ($errors->has('seo_keywords')) has-error @endif">
                                        <label class="col-md-6 control-label">SEO Keyword<sup>*</sup></label>

                                        <div class="col-md-6">     
                                            <input class="form-control" name="seo_keywords" value="{{old('seo_keywords',$post->seo_keywords)}}" />
                                            @if ($errors->has('seo_keywords')) <span class="help-block"> <strong class="text-danger">{{ $errors->first('seo_keywords') }}</strong> </span> @endif
                                        </div>

                                    </div>
                                    <div class="form-group @if ($errors->has('seo_description')) has-error @endif">
                                        <label class="col-md-6 control-label">SEO Description<sup>*</sup></label>

                                        <div class="col-md-6">     
                                            <input class="form-control" name="seo_description" value="{{old('seo_description',$post->seo_description)}}" />
                                            @if ($errors->has('seo_description')) <span class="help-block"> <strong class="text-danger">{{ $errors->first('seo_description') }}</strong> </span> @endif
                                        </div>

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