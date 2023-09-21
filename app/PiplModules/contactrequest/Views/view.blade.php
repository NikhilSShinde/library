@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Contact Request View</title>

@endsection

@section('content')
<div class="page-content-wrapper">
    <div class="page-content">

        <!-- BEGIN PAGE BREADCRUMB -->
        <ul class="page-breadcrumb breadcrumb hide">
            <li>
                <a href="{{url('admin/dashbaord')}}">Home</a><i class="fa fa-circle"></i>
            </li>
            <li class="active">
                Dashboard
            </li>
        </ul>

        <!-- BEGIN PAGE BREADCRUMB -->
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{url('admin/dashboard')}}">Dashboard</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="{{url('admin/contact-requests')}}">Contact Request</a>
            </li>
            <li>
                <a href="javascript:void(0)">View & Reply</a>
            </li>
        </ul>
        <div class="profile-content">
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet light">
                        <div class="portlet-title tabbable-line">
                            <div class="caption caption-md">
                                <i class="icon-globe theme-font hide"></i>
                                <span class="caption-subject font-blue-madison bold uppercase">Contact Request View</span>
                            </div>
                            <ul class="nav nav-tabs">
                                <li class="@if(!($errors->has('email') || $errors->has('subject') || $errors->has('message'))) active @endif">
                                    <a href="#tab_1_1" data-toggle="tab">Request Details</a>
                                </li>
                                <li class="@if(($errors->has('email') || $errors->has('subject') || $errors->has('message'))) active @endif">
                                    <a href="#tab_1_3" data-toggle="tab">Post A reply</a>
                                </li>
                                <li class="">
                                    <a href="#tab_1_2" data-toggle="tab">Your Replies</a>
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
                                <div class="tab-pane @if(!($errors->has('email') || $errors->has('subject') || $errors->has('message'))) active @endif" id="tab_1_1">
                                    <form class="form-horizontal">
                                        <div class="form-group row">
                                            <label class="control-label col-sm-4"><b>Name:</b></label>
                                          <div class="col-sm-5">
                                           <label class="control-label">{{$request->contact_name}}</label>
                                          </div>  
                                        </div>
                                       <div class="form-group row">
                                            <label class="control-label col-sm-4"><b>Email:</b></label>
                                          <div class="col-sm-5">
                                           <label class="control-label">{{$request->contact_email}}</label>
                                          </div>  
                                        </div>
                                         <div class="form-group row">
                                            <label class="control-label col-sm-4"><b>Phone:</b></label>
                                          <div class="col-sm-5">
                                           <label class="control-label">{{{$request->contact_phone}}}</label>
                                          </div>  
                                         </div>
                                         <div class="form-group row">
                                            <label class="control-label col-sm-4"><b>Category:</b></label>
                                          <div class="col-sm-5">
                                           <label class="control-label">@if($request->category) {{$request->category->translate()->name}} @else - @endif</label>
                                          </div>  
                                         </div>
                                         <div class="form-group row">
                                            <label class="control-label col-sm-4"><b>Date:</b></label>
                                          <div class="col-sm-5">
                                           <label class="control-label">{{$request->created_at->format('d M, Y')}}</label>
                                          </div>  
                                        </div>
                                         <div class="form-group row">
                                            <label class="control-label col-sm-4"><b>Subject:</b></label>
                                          <div class="col-sm-5">
                                           <label class="control-label">{{$request->contact_subject}}</label>
                                          </div>  
                                        </div>
                                        <div class="form-group row">
                                            <label class="control-label col-sm-4"><b>message:</b></label>
                                          <div class="col-sm-5">
                                           <label class="control-label">{{ $request->contact_message }}</label>
                                          </div>  
                                        </div>
                                         <div class="form-group row">
                                            <label class="control-label col-sm-4"><b>Attachment(s):</b></label>
                                          <div class="col-sm-5">
                                           <ul class="list-inline">
                                          
                                            @if(!empty($request->contact_attachment))
                                                @foreach($request->contact_attachment as $attachment)
                                                    <li><a target="new" href="{{asset('storage/contact-requests/'.$request->reference_no.'/'.$attachment)}}"><i class="fa fa-download"></i> {{$attachment}}</a></li>
                                                @endforeach
                                            
                                             @else    
                                                    No attachemnt found
                                            @endif
                                            </ul>
                                          </div>  
                                        </div>
                                     </form>   
                                </div>
                                <div class="tab-pane @if(($errors->has('email') || $errors->has('subject') || $errors->has('message'))) active @endif" id="tab_1_3">
                                    <form role="form" class="form-horizontal" method="post" action="{{url('/admin/contact-request-reply/'.$request->reference_no)}}" enctype="multipart/form-data">
                                        {!! csrf_field() !!}
                                        <h4>Please fill below form to submit your reply</h4>
                                         <div class="form-group @if ($errors->has('email')) has-error @endif">
                                            <label class="control-label col-sm-4"><b>Email From:</b></label>
                                            <div class="col-sm-5">
                                              <input class="form-control" name="email" value="{{old('email',$contact_email->value)}}" />
                                               @if ($errors->has('email'))
                                                <span class="help-block">
                                                    <strong class="text-danger">{{ $errors->first('email') }}</strong>
                                                </span>
                                             @endif
                                            </div>  
                                         </div>
                                        <div class="form-group @if ($errors->has('subject')) has-error @endif">
                                            <label class="control-label col-sm-4"><b>Subject:</b></label>
                                            <div class="col-sm-5">
                                              <input class="form-control" name="subject" value="{{old('subject','Re: '.$request->contact_subject)}}" />
                                              @if ($errors->has('subject'))
                                                <span class="help-block">
                                                    <strong class="text-danger">{{ $errors->first('subject') }}</strong>
                                                </span>
                                             @endif
                                            </div>  
                                         </div>
                                        <div class="form-group @if ($errors->has('message')) has-error @endif">
                                            <label class="control-label col-sm-4"><b>Message:</b></label>
                                            <div class="col-sm-5">
                                             <textarea class="form-control" name="message">{{old('message')}}</textarea>
                                             @if ($errors->has('message'))
                                                    <span class="help-block">
                                                        <strong class="text-danger">{{ $errors->first('message') }}</strong>
                                                    </span>
                                             @endif
                                            </div>  
                                        </div>
                                        <div class="form-group @if ($errors->has('attachment')) has-error @endif">
                                            <label class="control-label col-sm-4"><b>Browse:</b></label>
                                            <div class="col-sm-5">
                                                <input class="form-control" name="attachment[]" multiple="multiple"  type="file" value="{{old('attachment')}}" />
                                                @if ($errors->has('attachment'))
                                                            <span class="help-block">
                                                                <strong class="text-danger">{{ $errors->first('attachment') }}</strong>
                                                            </span>
                                                @endif
        
                                            </div>  
                                        </div>
                                        <div class="form-group @if ($errors->has('message')) has-error @endif">
                                          <label class="control-label col-sm-4"></label>
                                            <div class="col-sm-5">
                                                <button type="submit" class="btn btn-md btn-primary">Post Reply</button>
                                             <button type="button" class="btn btn-md btn-default" onclick="jQuery('#post-reply').toggle()">Cancel</button>
                                            </div>  
                                        </div>
                                    </form>   
                                </div>    
                                <!-- CHANGE PASSWORD TAB -->
                                <div class="tab-pane" id="tab_1_2">
                                    <form role="form">
                                      @if(count($request->replies())>0)
                                      
                                       @foreach($request->replies()->orderBy('created_at','desc')->get() as $key=>$reply)
                                      
                                        <div class="form-group row">
                                          <div class="col-sm-9">
                                              <label class="control-label">{{$reply->reply_email}} <i class="fa fa-calendar"></i> {{$reply->created_at->format('d M, Y')}}</label>
                                          </div>  
                                        </div>
                                       <div class="form-group row">
                                           
                                          <div class="col-sm-9">
                                           <label class="">{{$reply->reply_subject}}</label>
                                          </div>  
                                        </div>
                                        
                                          <div class="form-group row">
                                          <div class="col-sm-8">
                                           <label class="control-label">{!! $reply->reply_message !!}</label>
                                          </div>  
                                        </div>
                                       <div class="form-group row attachments">
                                            <label class="control-label col-sm-4"><b>Attachment(s):</b></label>
                                          <div class="col-sm-5">
                                           <ul class="list-inline">
                                          
                                            @if(!empty($reply->reply_attachment))
                                                @foreach($reply->reply_attachment as $attachment)
                                                    <li><a target="new" href="{{asset('storageasset/contact-requests/'.$request->reference_no.'/'.$attachment)}}"><i class="fa fa-download"></i> {{$attachment}}</a></li>
                                                @endforeach
                                            
                                             @else    
                                                    No Attachemnt
                                            @endif
                                            </ul>
                                          </div>  
                                        </div>
                                       @endforeach
                                       
                                       @else
                                            No message found
                                       @endif
                                    </form>
                                </div>

                                <!-- END CHANGE PASSWORD TAB -->
                                <!-- PRIVACY SETTINGS TAB -->

                                <!-- END PRIVACY SETTINGS TAB -->
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
<script src="{{url('/vendor/unisharp/laravel-ckeditor/ckeditor.js')}}"></script>
    <script>
        CKEDITOR.replace( 'message' );
    </script>
@endsection
