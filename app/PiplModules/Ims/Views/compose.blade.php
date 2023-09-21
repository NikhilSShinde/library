@extends(config("piplmodules.front-view-layout-location"))

@section("meta")
<title>Compose a message</title>
@endsection

@section("content")
<section class="container">
<div class="row">
<div class="col-sm-12 col-md-2">
@include('ims::left-nav')
</div>
<div class="col-sm-12 col-md-10">
<h1 class="text-center">Compose a Message</h1>

@if (session('status'))
               <div class="alert alert-success">
                {{ session('status') }}
            	</div>
@endif

<form role="form" method="post" enctype="multipart/form-data">
{!! csrf_field() !!}
<div class="row">
<div class="col-md-8 col-md-offset-2 col-sm-12">
<fieldset>
<br />
<div class="form-group">
<div class="row">
<div class="col-sm-12 col-md-6 @if ($errors->has('project')) has-error @endif">
<label for="project" >Project </label><select class="form-control" name="project" >
@foreach($projects as $project)
<option {{$project}}>{{$project}}</option>
@endforeach
</select>
@if ($errors->has('project'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('project') }}</strong>
                                    </span>
@endif
</div>
<div class="col-sm-12 col-md-6 @if ($errors->has('recipient')) has-error @endif">
<label for="recipient" >Recipient </label><select class="form-control" name="recipient" >
@foreach($users as $user)
<option value="{{$user->id}}">{{$user->userInformation->first_name}}</option>
@endforeach
</select>
@if ($errors->has('recipient'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('recipient') }}</strong>
                                    </span>
@endif
</div>
</div>
</div>

<div class="form-group @if ($errors->has('subject')) has-error @endif">
<label for="subject" >Subject </label><input class="form-control" name="subject" value="{{old('subject')}}" />
@if ($errors->has('subject'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('subject') }}</strong>
                                    </span>
        @endif
</div>
<div class="form-group @if ($errors->has('content')) has-error @endif">
<label for="content" >Content </label><textarea class="form-control" id="content" name="content" >{{old('content')}}</textarea>
@if ($errors->has('content'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('content') }}</strong>
                                    </span>
        @endif
</div>


<div class="form-group @if ($errors->has('attachment')) has-error @endif">
<label for="attachment" >Attach a file </label><input class="form-control" name="attachment[]" multiple="multiple"  type="file" value="{{old('attachment')}}" />

@if ($errors->has('attachment'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('attachment') }}</strong>
                                    </span>
        @endif
        
</div>

</fieldset>
</div>

</div>
<div class="row">
<div class="form-group text-center">
<button type="submit" class="btn btn-md btn-primary">Submit</button> 
</div>
</div>

</form>
</div>
</div>
</section>
<script src="{{url('/vendor/unisharp/laravel-ckeditor/ckeditor.js')}}"></script> 
<script>
	CKEDITOR.replace( 'content' );
</script> 
@endsection
