@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Support Ticket Details</title>

@endsection

@section('content')
<div class="page-content-wrapper">
    <div class="page-content">
        <ul class="page-breadcrumb breadcrumb hide">
            <li>
                <a href="{{url('admin/dashbaord')}}">Home</a><i class="fa fa-circle"></i>
            </li>
            <li class="active">
                Dashboard
            </li>
        </ul>
        
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{url('admin/dashboard')}}">Dashboard</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="{{url('admin/support-tickets')}}">Support Ticket Details</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="javascript:void(0)">View & Reply</a>
            </li>
        </ul>
        @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
        @endif
        <div class="profile-content">
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet light">
                        <div class="portlet-title tabbable-line">
                            <div class="caption caption-md">
                                <i class="icon-globe theme-font hide"></i>
                                <span class="caption-subject font-blue-madison bold uppercase">Support Ticket View</span>
                            </div>
                            <ul class="nav nav-tabs">
                                <li class="@if(!($errors->has('message') || $errors->has('assigned_to'))) active @endif">
                                    <a href="#tab_1_1" data-toggle="tab">Request Details</a>
                                </li>
                                <li class="@if($errors->has('message') || $errors->has('assigned_to')) active @endif">
                                    <a href="#tab_1_3" data-toggle="tab">Post A reply</a>
                                </li>
                                <li class="">
                                    <a href="#tab_1_2" data-toggle="tab">Conversation Messages</a>
                                </li>

                            </ul>
                        </div>
                        @if (session('profile-updated'))
                        <div class="alert alert-success">
                            {{ session('profile-updated') }}
                        </div>
                        @endif
                        @if (session('password-update-fail'))
                        <div class="alert alert-danger">
                            {{ session('password-update-fail') }}
                        </div>
                        @endif
                        <div class="portlet-body">
                            <div class="tab-content">
                                <!-- PERSONAL INFO TAB -->
                                <div class="tab-pane @if(!($errors->has('message') || $errors->has('assigned_to'))) active @endif" id="tab_1_1">
                                    <form class="form-horizontal">
                                        <div class="form-group row">
                                            <label class="control-label col-sm-4"><b>Subject:</b></label>
                                            <div class="col-sm-5">
                                                <label class="control-label"> {{$support_ticket->support_subject}}</label>
                                            </div>  
                                        </div>
                                        <div class="form-group row">
                                            <label class="control-label col-sm-4"><b>Added By:</b></label>
                                            <div class="col-sm-5">
                                                <label class="control-label"> {{$support_ticket->UserInformation->first_name . ' ' . $support_ticket->UserInformation->last_name}}</label>
                                            </div>  
                                        </div>
                                        <div class="form-group row">
                                            <label class="control-label col-sm-4"><b>Assigned To:</b></label>
                                            <div class="col-sm-5">
                                                <label class="control-label">
                                                    @if(isset($ticket_assigned_data->UserInformation->first_name))
                                                    {{$ticket_assigned_data->UserInformation->first_name.' '.$ticket_assigned_data->UserInformation->last_name}}
                                                    @else
                                                    Not Yet Assigned
                                                    @endif
                                                </label>
                                            </div>  
                                        </div>
                                        <div class="form-group row">
                                            <label class="control-label col-sm-4"><b>Ticket Unique Id:</b></label>
                                            <div class="col-sm-5">
                                                <label class="control-label">{{$support_ticket->ticket_unique_id}}</label>
                                            </div>  
                                        </div>
                                        <div class="form-group row">
                                            <label class="control-label col-sm-4"><b>Trip Unique Id:</b></label>
                                            <div class="col-sm-5">
                                                <label class="control-label">@if(isset($support_ticket->orderInformation->order_unique_id))
                                                    {{$support_ticket->orderInformation->order_unique_id}}
                                                    @else
                                                    --
                                                    @endif</label>
                                            </div>  
                                        </div>
                                       @if(isset($support_ticket->support_attachment))
                                       
                                          <div class="form-group row">
                                            <label class="control-label col-sm-4"><b>Attachment:</b></label>
                                            <div class="col-sm-5">
                                               <img width="200px" title="Ticket Image" src="{{ url("/storageasset/suport-files/".$support_ticket->support_attachment)}}"/>
                                               <a target="_blank" href="{{ url("/storageasset/suport-files/".$support_ticket->support_attachment)}}">Download</a>
                                            </div>  
                                        </div>
                                       @endif
                                    </form>   
                                </div>
                                <div class="tab-pane @if(($errors->has('message') || $errors->has('assigned_to'))) active @endif" id="tab_1_3">
                                    @if($support_ticket->status==2)
                                    
                                    Ticket is closed.
                                    @else
                                    <form role="form" class="form-horizontal" method="post" action="{{url('/')}}/admin/suppot-ticket-reply" enctype="multipart/form-data">
                                        {!! csrf_field() !!}
                                        <div class="form-group @if($errors->has('message')) active @endif">
                                            <label class="control-label col-sm-4"><b>Message:</b></label>
                                            <div class="col-sm-5">
                                                <textarea class="form-control" name="message">{{old('message')}}</textarea>
                                                @if ($errors->has('message'))
                                                <span class="help-block">
                                                    <strong class="text-danger">{{$errors->first('message') }}</strong>
                                                </span>
                                                @endif
                                                @if ($errors->has('assigned_to'))
                                                <span class="help-block">
                                                    <strong class="text-danger">Ticket is not yet assigned to any user, Please assign ticket first.</strong>
                                                </span>
                                                @endif
                                            </div>  
                                        </div>
                                        <div class="form-group @if ($errors->has('message')) has-error @endif">
                                            <label class="control-label col-sm-4"></label>
                                            <input type="checkbox" name="is_completed" id="is_completed" value="1">Mark As Completed
                                        </div>    
                                        
                                        <div class="form-group @if ($errors->has('message')) has-error @endif">
                                            <label class="control-label col-sm-4"></label>
                                            <div class="col-sm-5">
                                                <button type="submit" class="btn btn-md btn-primary">Post Reply</button>
                                                <input type="hidden" id="ticket_id" value="{{$ticket_id}}" name="ticket_id">
                                                <input type="hidden" id="assigned_to" value="{{isset($ticket_assigned_data->assign_to) ? $ticket_assigned_data->assign_to : ''}}" name="assigned_to">
                                                <button type="button" class="btn btn-md btn-default" onclick="jQuery('#post-reply').toggle()">Cancel</button>
                                            </div>  
                                        </div>
                                    </form> 
                                   
                                    @endif
                                </div>    
                                <div class="tab-pane" id="tab_1_2">
                                    <form role="form">
                                        @if(count($reply_on_ticket)>0)
                                        @foreach($reply_on_ticket as $key=>$reply)
                                        <div class="form-group row">
                                            <div class="col-sm-9">
                                                <label class="control-label">{{$reply->UserInformation->first_name . ' ' . $reply->UserInformation->last_name}} <i class="fa fa-calendar"></i> {{$reply->created_at->format('d M, Y')}}</label>
                                            </div>  
                                        </div>
                                        <div class="form-group row">
                                            <div class="col-sm-8">
                                                <label class="control-label">{!! $reply->description !!}</label>
                                            </div>  
                                        </div>
                                        <hr>
                                        @endforeach
                                        @else
                                        No message found
                                        @endif
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END PAGE CONTENT INNER -->
    </div>
</div>
<style>
    .attachments{
        border-bottom: 1px solid #ccc;
    }
</style>
<!--<script src="{{url('/vendor/unisharp/laravel-ckeditor/ckeditor.js')}}"></script>-->
<script>
//                                                    CKEDITOR.replace('message');
</script>
@endsection
