@extends(config("piplmodules.back-view-layout-location"))

@section("meta")
<title>Contact Us - Post Reply</title>
@endsection

@section("content")
<section class="container">

<h1>Reply</h1>

<form role="form" method="post" enctype="multipart/form-data">
{!! csrf_field() !!}
<div class="row">
<div class="col-md-12 col-sm-12">
<fieldset>
<legend>Please fill below form to submit your reply</legend>


<div class="form-group @if ($errors->has('email')) has-error @endif">
<label for="email" >Your email </label><input class="form-control" name="email" value="{{old('email',$contact_email->value)}}" />
@if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('email') }}</strong>
                                    </span>
        @endif
</div>



<div class="form-group @if ($errors->has('subject')) has-error @endif">
<label for="subject" >Subject </label><input class="form-control" name="subject" value="{{old('subject','Re: '.$request->contact_subject)}}" />
@if ($errors->has('subject'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('subject') }}</strong>
                                    </span>
        @endif
</div>

<div class="form-group @if ($errors->has('message')) has-error @endif">
<label for="message" >Your message </label><textarea class="form-control" name="message">{{old('message')}}</textarea>

@if ($errors->has('message'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('message') }}</strong>
                                    </span>
        @endif
</div>

<!--<div class="form-group @if ($errors->has('attachment')) has-error @endif">
<label for="attachment" >Attach a file </label><input class="form-control" name="attachment[]" multiple="multiple"  type="file" value="{{old('attachment')}}" />

@if ($errors->has('attachment'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('attachment') }}</strong>
                                    </span>
        @endif
        
</div>-->




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
<script src="{{url('/vendor/unisharp/laravel-ckeditor/ckeditor.js')}}"></script>
    <script>
        CKEDITOR.replace( 'message' );
    </script>
@endsection