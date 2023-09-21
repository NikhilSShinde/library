@extends(config("piplmodules.front-view-layout-location"))

@section("meta")
<title>Inbox</title>
@endsection

@section("content")
<section class="container">
  <div class="row">
    <div class="col-sm-12 col-md-2"> @include('ims::left-nav') </div>
    <div class="col-sm-12 col-md-10">
      <h1>Inbox</h1>
      @if (session('status'))
      <div class="alert alert-success"> {{ session('status') }} </div>
      @endif
      
      @include('ims::filters')
      
      <div id="messages">
      
      @foreach($messages as $key=>$message_object)
      
                <div
                            data-project="{{$message_object->message->conversation->title}}"
                            data-subject="{{$message_object->message->subject}}"
                            data-date="{{$message_object->message->created_at->format("d M Y")}}"
                            data-user="{{$message_object->message->sender->userInformation->first_name}} {{$message_object->message->sender->userInformation->last_name}}"
                            class="alert @if( $message_object->message_status == '0') alert-warning @else alert-info @endif message-item"
                            data-default-sort="{{count($messages) - $key}}" >
                  <div class="row">
                    <div class="col-md-9 col-sm-12">
                      <h4><a href="{{url('ims/conversation/'.$message_object->message->conversation->id)}}">{{$message_object->message->sender->userInformation->first_name}} {{$message_object->message->sender->userInformation->last_name}}</a></h4>
                    </div>
                    <div class="col-sm-12 col-md-3 text-right">{{$message_object->message->conversation->title}}</div>
                  </div>
                  <div class="row">
                    <div class="col-sm-12 col-md-8">
                      <h5><a href="{{url('ims/conversation/'.$message_object->message->conversation->id)}}">{{$message_object->message->subject}}</a></h5>
                    </div>
                    <div class="col-sm-12 col-md-4 text-right"><i class="fa fa-calendar"></i> {{$message_object->message->created_at->format("l d M, Y h:i A")}}</div>
                  </div>
                  <br />
                  <div class="row">
                    <div class="col-sm-12 col-md-12"> {{{ str_limit(strip_tags($message_object->message->content),100, '&hellip;') }}} </div>
                  </div>
                </div>
                
        @endforeach
        </div>
        
      <div class="row">
        <div class="col-md-12 text-right">
          <div id="paginate"></div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection