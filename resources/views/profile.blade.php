@extends('layouts.app')

@section('meta')
    <title>User Profile</title>
@endsection
 
@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">
                    User Profile
                </div>
               @if (session('password-update-success'))
               <div class="alert alert-success">
                {{ session('password-update-success') }}
            	</div>
                @endif
               @if (session('profile-updated'))
               <div class="alert alert-success">
                {{ session('profile-updated') }}
            	</div>
                @endif
                
                <div class="panel-body">
                  
                        <div class="form-group">
                            <label class="col-md-4 control-label">First Name</label>
                            <div class="col-md-6">
                                <label class="col-md-4 control-label">{{$user_info->userInformation->first_name}}</label>
                            </div>
                        </div>
                         <div class="form-group">
                            <label class="col-md-4 control-label">Last Name</label>
                            <div class="col-md-6">
                                 <label class="col-md-4 control-label">{{$user_info->userInformation->last_name}}</label>
                              
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label">Email</label>

                           <div class="col-md-6">
                                 <label class="col-md-4 control-label">{{$user_info->email}}</label>
                              
                            </div>
                        </div>
                      
                        
                        @if (isset($user_info->userAddress->suburb))                       
                        <div class="form-group">
                            <label class="col-md-4 control-label">Suburb</label>
                            <div class="col-md-6">
                                 <label class="col-md-4 control-label">{{$user_info->userAddress->suburb}}</label>
                              
                            </div>
                        </div>
                        @endif
                        
                        @if (isset($user_info->userAddress->zipcode))                       
                        <div class="form-group{{ $errors->has('zipcode') ? ' has-error' : '' }}">
                            <label class="col-md-4 control-label">Zip Code</label>
                            <div class="col-md-6">
                                 <label class="col-md-4 control-label">{{$user_info->userAddress->zipcode}}</label>
                              
                            </div>
                        </div>
                        @endif
                        
                        
                    
                     </div>
                  <div class="row">
                         
                        <div class="col-md-3 top-margin">
                            <a href='{{url('update-profile')}}' class="btn btn-primary pull-right">
                                        <i class="fa fa-btn fa-user"></i>Edit Profile
                            </a>
                        </div>   
                       
                       <div class="col-md-3 top-margin">
                         <a href='{{url('change-email')}}' class="btn btn-primary pull-right">
                                    <i class="fa fa-btn fa-envelope"></i>Change Email
                        </a>
                        </div>  
                   
                       <div class="col-md-3 top-margin">     
                            <a href='{{url('change-password')}}' class="btn btn-primary pull-right">
                                       <i class="fa fa-btn fa-lock"></i>Change Password
                           </a>
                        </div>
                     </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
