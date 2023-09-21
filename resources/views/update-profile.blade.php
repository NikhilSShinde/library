@extends('layouts.app')

@section('meta')
    <title>Update User Profile</title>
@endsection
 
 
@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Update User Profile</div>
                <div class="panel-body">
                    <form class="form-horizontal" name="update_profile" id="update_profile" role="form" method="POST" action="{{ url('/update-profile-post') }}">
                        {!! csrf_field() !!}
                        <div class="form-group{{ $errors->has('first_name') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">First Name</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="first_name" value="{{old('first_name',$user_info->userInformation->first_name)}}">
                                 @if ($errors->has('first_name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('first_name') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                         <div class="form-group{{ $errors->has('last_name') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">Last Name</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="last_name" value="{{old('last_name',$user_info->userInformation->last_name)}}">
                                 @if ($errors->has('last_name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('last_name') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                       
                      @if(isset($user_info->userAddress->suburb))
                        <div class="form-group{{ $errors->has('suburb') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">Suburb</label>
                            <div class="col-md-6">

                                <input type="text" class="form-control" name="suburb" value="{{ old('suburb',$user_info->userAddress->suburb )}}">
                                 @if ($errors->has('suburb'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('suburb') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
			@endif
			 @if(isset($user_info->userAddress->zipcode))
                        <div class="form-group{{ $errors->has('post_code') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">Post Code</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="zipcode" value="{{ old('zipcode',$user_info->userAddress->zipcode or '')}}">
                                 @if ($errors->has('zipcode'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('zipcode') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                      @endif
                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-btn fa-user"></i>Update Profile
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
