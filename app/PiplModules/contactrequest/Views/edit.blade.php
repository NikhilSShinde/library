@extends(config("piplmodules.back-view-layout-location"))

@section("meta")
<title>Update Content Page</title>
@endsection

@section("content")
<section class="container">

<h1>Update Content Page</h1>

<form role="form" method="post">
{!! csrf_field() !!}


    @if (session('status'))
               <div class="alert alert-success">
                {{ session('status') }}
            	</div>
@endif

<div class="row">
<div class="col-md-7 col-sm-12">
<fieldset>
<legend>Page Information</legend>
<div class="form-group @if ($errors->has('page_title')) has-error @endif">
<label for="page_title" >Title </label><input class="form-control" name="page_title" value="{{old('page_title',$page_information->page_title)}}" />
@if ($errors->has('page_title'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('page_title') }}</strong>
                                    </span>
        @endif
</div>

<div class="form-group @if ($errors->has('page_content')) has-error @endif">
<label for="page_content" >Content </label><textarea class="form-control" name="page_content">{{old('page_content',$page_information->page_content)}}</textarea>

@if ($errors->has('page_content'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('page_content') }}</strong>
                                    </span>
        @endif
</div>

<div class="form-group @if ($errors->has('page_alias')) has-error @endif">
<label for="page_alias" >Page Alias </label><input class="form-control" name="page_alias" value="{{old('page_alias',$page->page_alias)}}" />

@if ($errors->has('page_alias'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('page_alias') }}</strong>
                                    </span>
        @endif
        
</div>

<div class="form-group">
<label for="page_alias" >Publish Status </label>

 <div class="radio-list">
											<label class="radio-inline">
											<input type="radio" name="page_status" id="unpublish" value="0" @if(old("page_status",$page->page_status) === "0") checked @endif> Unpublished </label>
											<label class="radio-inline">
											<input type="radio" name="page_status" id="publish" value="1" @if(old("page_status",$page->page_status) === "1") checked @endif> Published </label>
											
	</div>
	@if ($errors->has('page_status'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('page_status') }}</strong>
                                    </span>
        @endif
  </div>


</fieldset>
</div>
<div class="col-md-4 col-sm-12 col-md-offset-1">

<fieldset>
<legend>Seo Settings</legend>

<div class="form-group">
<label for="page_seo_title" >Page Title </label><input class="form-control" name="page_seo_title" value="{{old('page_seo_title',$page_information->page_seo_title)}}" />
</div>

<div class="form-group">
<label for="page_meta_keywords" >Page Meta Keywords </label><textarea class="form-control" name="page_meta_keywords" >{{old('page_meta_keywords',$page_information->page_meta_keywords)}}</textarea>
</div>

<div class="form-group">
<label for="page_meta_descriptions" >Page Meta Descriptions </label><textarea class="form-control" name="page_meta_descriptions" >{{old('page_meta_descriptions',$page_information->page_meta_descriptions)}}</textarea>
</div>

</fieldset>
</div>
</div>
<div class="row">
<div class="form-group">
<button type="submit" class="btn btn-md btn-primary">Submit</button> 
</div>
</div>

</form>

</section>
@endsection