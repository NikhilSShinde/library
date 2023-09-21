@extends('layouts.app')

@section('meta')
    <title>Update Your Email</title>
@endsection
 
 
@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Update User Email</div>
                <div class="panel-body">
                    <form class="form-horizontal" name="update_email" id="update_email" role="form" method="POST" action="{{ url('/change-email-post') }}">
                        {!! csrf_field() !!}
                         <div class="form-group">
                            <label class="col-md-4 control-label">Your current Email</label>
                            <div class="col-md-6">
                                <label class="col-md-4 control-label">{{$user_info->email}}</label>
                            </div>
                        </div>
                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">New Email</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" id="email" name="email" value="{{old('email')}}">
                                 @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                         <div class="form-group{{ $errors->has('confirm_email') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">Confirm Email</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" id="confirm_email" name="confirm_email" value="{{old('confirm_email')}}">
                                 @if ($errors->has('confirm_email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('confirm_email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                      
                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-btn fa-save"></i>Update Email
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
