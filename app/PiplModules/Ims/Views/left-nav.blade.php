<div class="row">
<div class="col-sm-12 col-md-12">
<h2>&nbsp;</h2>
<div class="list-group">
<a role="button"  class="list-group-item  list-group-item-warning" href="{{url('ims/compose')}}"><i class="fa fa-pencil"></i> Compose </a>
<a role="button"  class="list-group-item @if(Request::segment(2)=='') active @endif" href="{{url('ims')}}"><i class="fa fa-inbox"></i> Inbox</a>
<a role="button"  class="list-group-item @if(Request::segment(2)=='sent') active  @endif list-group-item-info" href="{{url('ims/sent')}}"><i class="fa fa-send"></i> Sent</a>
<a role="button" class="list-group-item @if(Request::segment(2)=='trash') active  @endif list-group-item-danger" href="{{url('ims/trash')}}"><i class="fa fa-trash"></i> Trash</a>
</div>
</div>
</div>