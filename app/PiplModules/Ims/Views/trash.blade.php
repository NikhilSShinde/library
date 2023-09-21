@extends(config("piplmodules.front-view-layout-location"))

@section("meta")
<title>Trash Messages</title>
<style>
form.form-inline {display:inline-block;}
</style>
@endsection

@section("content")
<section class="container">
<div class="row">
<div class="col-sm-12 col-md-2">
@include('ims::left-nav')
</div>
<div class="col-sm-12 col-md-10">
<h1>Trash</h1>

@if (session('status'))
               <div class="alert alert-success">
                {{ session('status') }}
            	</div>
@endif

@include('ims::filters')
<div id="messages">

@foreach($messages as $key=>$message_object)

<?php
$recipient = "";
$cov_users = $message_object->message->conversation->users;
foreach($cov_users as $conv_user)
{
	if($conv_user->user_id != $message_object->message->sender_id)
	{
		$recipient = $conv_user;
		break;
	}
}
?>

<div
          data-project="{{$message_object->message->conversation->title}}"
            data-subject="{{$message_object->message->subject}}"
            data-date="{{$message_object->message->created_at->format("d M Y")}}"
            data-user="{{$message_object->message->sender->userInformation->first_name." ".$message_object->message->sender->userInformation->last_name}}" 
            class="alert @if( $message_object->message_status == '0') alert-warning @else alert-info @endif message-item"
            data-default-sort="{{count($messages) - $key}}" >

<div class="row">
<div class="col-md-9 col-sm-12"><b>From: {{$message_object->message->sender->userInformation->first_name." ".$message_object->message->sender->userInformation->last_name}}</b></div>
<div class="col-sm-12 col-md-3 text-right">{{$message_object->message->conversation->title}}</div>
</div>
<div class="row">
<div class="col-md-12 col-sm-12"><b>To: {{$recipient->user->userInformation->first_name." ".$recipient->user->userInformation->last_name}}</b></div>
</div>
<div class="row">
<div class="col-sm-12 col-md-9"><h5>{{$message_object->message->subject}}</h5></div>
<div class="col-sm-12 col-md-3 text-right">{{$message_object->message->created_at->format("l d M, Y h:i A")}}</div>
</div>
<br />
<div class="row">
<div class="col-sm-12 col-md-12">
 {{{ str_limit(strip_tags($message_object->message->content), 100, '&hellip;') }}}
</div>
</div>
<div class="row">
<div class="col-md-12 text-right">
<form action="{{ url('ims/restore-message') }}" method="post" class="form-inline">
{!! csrf_field() !!} <input type="hidden" name="msg_id" value="{{ $message_object->message->id}}" />
<div class="form-group"><button class="btn btn-sm btn-success" title="Restore this message"><i class="fa fa-history "></i></button></div>
</form>
<form  class="form-inline" onsubmit="return confirm('Are you sure to delete this message permanently? It can\'t revert back!!');" action="{{ url('ims/delete-message') }}" method="post">
{!! csrf_field() !!} <input type="hidden" name="msg_id" value="{{ $message_object->message->id}}" />
<div class="form-group"><button class="btn btn-sm btn-danger" title="Delete this message permanently"><i class="fa fa-trash "></i></button></div>
</form>
</div>
</div>
</div>
@endforeach
</div>

@if(count($messages) < 1)
<div class="alert alert-info">There are no messages in Trash</div>
@endif
</div>
</div>
</section>
@endsection