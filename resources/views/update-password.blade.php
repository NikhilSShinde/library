@extends('layouts.app')

@section('meta')
    <title>Change Your Password</title>
@endsection
 
 
@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Change Your Password</div>
              @if (session('password-update-fail'))
               <div class="alert alert-danger">
                {{ session('password-update-fail') }}
            	</div>
                @endif
              
                <div class="panel-body">
                    <form class="form-horizontal" name="update_password" id="update_password" role="form" method="POST" action="{{ url('/change-password-post') }}">
                        {!! csrf_field() !!}
                         
                        <div class="form-group{{ $errors->has('current_password') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">Current Password</label>
                            <div class="col-md-6">
                                <input type="password" class="form-control" id="current_password" name="current_password" value="{{old('current_password')}}">
                                 @if ($errors->has('current_password'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('current_password') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                         <div class="form-group{{ $errors->has('new_password') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">New Password</label>
                            <div class="col-md-6">
                                <input type="password" class="form-control" id="new_password" name="new_password" value="{{old('new_password')}}">
                                 @if ($errors->has('new_password'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('new_password') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                      <div class="form-group{{ $errors->has('confirm_password') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">Confirm Password</label>
                            <div class="col-md-6">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" value="{{old('confirm_password')}}">
                                 @if ($errors->has('confirm_password'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('confirm_password') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-btn fa-save"></i>Update Password
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
