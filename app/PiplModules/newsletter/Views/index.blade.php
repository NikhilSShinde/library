@extends(config("piplmodules.front-view-layout-location"))

@section("meta")
<title>Subscribe to Newsletter</title>
@endsection

@section("content")
<section class="container">
<div class="row">
<div class="col-md-6 col-sm-12 col-md-offset-3">
<h1>Subscribe to Newsletter</h1>


@if (session('status'))
               <div class="alert alert-success">
                {{ session('status') }}
            	</div>
@endif

<form method="post" action="{{url('/newsletter/subscribe')}}">


<div class="message alert"></div>
<div class="form-group">
<label>Enter your email:</label>
<input type="text" name="email" value="{{old('email')}}" class="form-control"  placeholder="Email"/>
</div>
<div class="form-group">
<button type="button" class="btn btn-primary" id="btnsubscribe">Subscribe</button>
</div>
</form>

</div>
</div>
</section>

<script type="text/javascript">
window.onload = function(){//Even though it's on footer, I just like to make//sure that DOM is ready
$(function(){
//We hide de the result div on start
$('div.message').hide();
//This part is more jQuery Related. In short, we //make an Ajax post request and get the response//back from server
$('#btnsubscribe').click(function(e){
e.preventDefault();
$.post('{{url("/newsletter/subscribe")}}', {
email: $('input[name="email"]').val(),
_token: '{!! csrf_token() !!}'
}, function($data){
if($data=='1') {
$('div.message').hide().removeClass('alert-success alert-danger').addClass('alert-success').html('You\'ve successfully subscribed to our newsletter').fadeIn('fast');
} else {
//This part echos our form validation errors
$('div.message').hide().removeClass('alert-success alert-danger').addClass('alert-danger').html('There has been an error occurred:<br /><br />'+$data).fadeIn('fast');
}
});
});
//We prevented to submit by pressing enter or anyother way
$('form').submit(function(e){
e.preventDefault();
$('#btnsubscribe').click();
});
});
}
</script>
@endsection