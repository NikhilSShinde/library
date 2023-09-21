@extends(config("piplmodules.front-view-layout-location"))

@section("meta")
<title>Conversation</title>
@endsection

@section("content")
<section class="container">
<div class="row">
<div class="col-sm-12 col-md-2">
  @include('ims::left-nav')
</div>
<div class="col-sm-12 col-md-10">
@if (session('status'))
               <div class="alert alert-success">
                {{ session('status') }}
            	</div>
@endif
<div class="row">
<div class="col-sm-12 col-md-8">
<h2>Subject: {{$initial_message->message->subject}}</h2>
</div>
<div class="col-md-4 text-right">
<form class="form-inline" onsubmit="return confirm('Are you sure to move this message to trash? Please note entire conversation will be moved to trash');" method="post" action="{{url('ims/trash-conversation')}}">{!! csrf_field() !!} <input type="hidden" name="conversation_id" value="{{ $initial_message->message->conversation->id}}" />
<div class="form-group"><button class="btn btn-sm btn-danger" type="submit"><i class="fa fa-trash"></i> Delete Entire Conversation</button></div>
<div class="form-group"><a class="btn btn-primary pull-right" href="javascript:void(0)" data-toggle="modal" data-target="#reply">Post a reply</a></div>
</form></div>
</div>
<div class="well">
<div class="row">
<div class="col-md-12 text-right"><form onsubmit="return confirm('Are you sure to move this message to trash?');" method="post" action="{{url('ims/trash-message')}}">{!! csrf_field() !!} <input type="hidden" name="msg_id" value="{{ $initial_message->message->id}}" /><button class="btn btn-sm btn-danger" type="submit"><i class="fa fa-trash"></i> </button></form>
</div>
</div>
<div class="row">
<div class="col-sm-12 col-md-9"><b>From: {{$initial_message->message->sender->userInformation->first_name}} {{$initial_message->message->sender->userInformation->last_name}}</b>
</div>
<div class="col-sm-12 col-md-3 text-right"><b>{{$initial_message->message->conversation->title}}</b></div>
</div>
<div class="row">
<div class="col-sm-12 col-md-12 text-right"><i class="fa fa-calendar"></i> {{$initial_message->message->created_at->format("l d M, Y h:i A")}}</div></div>
@if(count($initial_message->message->attachments) > 0)
<div class="row">
<div class="col-sm-12 col-md-12">
<ul class="list-inline">
<li><i class="fa fa-paperclip"></i> </li>
@foreach($initial_message->message->attachments as $attachment)
<li><a target="new" href="{{asset('storageasset/ims/'.$initial_message->message->conversation->id.'/'.$attachment['original_name'])}}">{{$attachment['display_name']}}</a></li>
@endforeach
</ul>
</div>
</div>
@endif
<br />
<div class="row">
<div class="col-sm-12 col-md-12">
{!! $initial_message->message->content !!}
</div>
</div>
</div>


<div class="panel panel-default">
<div class="panel-heading">History</div>
<div class="panel-body">
@foreach($messages as $key => $message_object)
@if($key > 0) <br /> @endif

<div class="@if( $message_object->message->sender_id == Auth::user()->id) alert alert-warning @else alert alert-info @endif">

<div class="row">
<div class="col-md-9 col-sm-9">From: {{$message_object->message->sender->userInformation->first_name}}</div>
<div class="col-md-3 col-sm-3 text-right"><form onsubmit="return confirm('Are you sure to move this message to trash?');" method="post" action="{{url('ims/trash-message')}}">{!! csrf_field() !!} <input type="hidden" name="msg_id" value="{{ $message_object->message->id}}" /><button class="btn btn-sm btn-danger" type="submit"><i class="fa fa-trash"></i></button></form></div>
</div>
<div class="row">
<div class="col-sm-12 col-md-12">Subject: {{$message_object->message->subject}}</div>
</div>
<div class="row">
<div class="col-sm-12 col-md-12"><small><i class="fa fa-calendar"></i> {{$message_object->message->created_at->format("l d M, Y h:i A")}}</small></div>
</div>
@if(count($message_object->message->attachments) > 0)
<div class="row">
<div class="col-sm-12 col-md-12">
<ul class="list-inline">
<li><i class="fa fa-paperclip"></i> </li>
@foreach($message_object->message->attachments as $attachment)
<li><a target="new" href="{{asset('storage/ims/'.$message_object->message->conversation->id.'/'.$attachment['original_name'])}}">{{$attachment['display_name']}}</a></li>
@endforeach
</ul>
</div>
</div>
@endif
<hr />
<div class="row">
<div class="col-sm-12 col-md-12">
{!! $message_object->message->content !!}
</div>
</div>

</div>
@endforeach
</div>
</div>
</div>
</div>
</section>

<div class="modal fade" id="reply" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
    <form method="post" enctype="multipart/form-data">
    {!! csrf_field() !!}
    <input type="hidden" name="recipient" value="{{$reply_to}}" />
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Post a reply</h4>
      </div>
      <div class="modal-body">
        
        <fieldset>
        	<div class="form-group">
            <label for="subject">Subject</label>
            <input type="text" class="form-control" name="subject" value="Re: {{$initial_message->message->subject}}" />
            </div>
            <div class="form-group">
            <label for="content">Content</label>
            <textarea class="form-control" name="content" ></textarea>
            </div>
            <div class="form-group">
            <label for="attachment">Attachment(s)</label>
            <input type="file" name="attachment[]" multiple="multiple" class="form-control" ></textarea>
            </div>
        </fieldset>
        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Submit</button>
      </div>
      </form>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<script src="{{url('/vendor/unisharp/laravel-ckeditor/ckeditor.js')}}"></script> 
<script>
	CKEDITOR.replace( 'content' );
</script> 
@endsection