@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Update Customer Profile</title>

@endsection

@section('content')
<link rel="stylesheet" type="text/css" href="{{url('public/media/backend/css/datepicker/jquery-ui.css')}}">

<script type="text/javascript"  src="{{url('public/media/backend/js/jquery-ui.min.js')}}"></script>
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
                <a href="{{url('admin/manage-users')}}">Manage Customers</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="javascript:void(0);">Update Customer Profile</a>
            </li>
        </ul>
        <div class="profile-content">
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet light">
                        <div class="portlet-title tabbable-line">
                            <div class="caption caption-md">
                                <i class="icon-globe theme-font hide"></i>
                                <span class="caption-subject font-blue-madison bold uppercase">Update User Profile</span>
                            </div>
                            <ul class="nav nav-tabs">
                                <li class="@if(!($errors->has('email') || $errors->has('confirm_email')|| $errors->has('current_password')|| $errors->has('new_password') || $errors->has('confirm_password') || session('password-update-fail'))) active @endif">
                                    <a href="#tab_1_1" data-toggle="tab">Personal Informations</a>
                                </li>
                                <li class="@if($errors->has('email') || $errors->has('confirm_email')) active @endif">
                                    <a href="#tab_1_3" data-toggle="tab">Change Email</a>
                                </li>
                                <li class="@if($errors->has('current_password')|| $errors->has('new_password') || $errors->has('confirm_password') || session('password-update-fail')!='') active @endif">
                                    <a href="#tab_1_2" data-toggle="tab">Change Password</a>
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
                                <div class="tab-pane @if(!($errors->has('email') || $errors->has('confirm_email')|| $errors->has('current_password')|| $errors->has('new_password') || $errors->has('confirm_password') || session('password-update-fail'))) active @endif" id="tab_1_1">
                                    <form name="frm_regsitered_user_update"  id="frm_regsitered_user_update" role="form" method="post" action="{{ url('/admin/update-registered-user/'.$user_info->id)}}">
                                        {!! csrf_field() !!}
                                        <div class="form-group{{ $errors->has('first_name') ? ' has-error' : '' }}">
                                            <label class="control-label">First Name <sup style='color:red;'>*</sup></label>

                                            <input type="text" class="form-control" name="first_name" value="{{old('first_name',$user_info->userInformation->first_name)}}">
                                            @if ($errors->has('first_name'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('first_name') }}</strong>
                                            </span>
                                            @endif

                                        </div>

                                        <div class="form-group{{ $errors->has('last_name') ? ' has-error' : '' }}">
                                            <label class="control-label">Last Name <sup style='color:red;'>*</sup></label>

                                            <input type="text" class="form-control" name="last_name" value="{{old('last_name',$user_info->userInformation->last_name)}}">
                                            @if ($errors->has('last_name'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('last_name') }}</strong>
                                            </span>
                                            @endif

                                        </div>
                                         <div class="form-group{{ $errors->has('user_mobile') ? ' has-error' : '' }}">
                                            <label class="control-label">Date of Birth.</label>

                                            <input type="text" id='date_of_birth' class="form-control" name="date_of_birth" value="{{old('date_of_birth',(isset($user_info->userInformation->user_birth_date) && ($user_info->userInformation->user_birth_date!='0000-00-00'))?$user_info->userInformation->user_birth_date:'')}}">
                                            @if ($errors->has('date_of_birth'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('date_of_birth') }}</strong>
                                            </span>
                                            @endif

                                        </div>
                                       <div class="form-group {{ $errors->has('country') ? ' has-error' : '' }}">
                                                <label class="control-label">County <sup style='color:red;'>*</sup> </label>
                                                   <select class="form-control" onchange="getAllStates(this.value)" name="country" id="country">
                                                     <option value="" selected="">--Select--</option>
                                                        @foreach($countries as $country)
                                                            @if($country->id!='17')
                                                                <option value="{{$country->id}}" @if($country->id==$user_country) selected @endif>{{$country->name}}</option>   
                                                            @endif
                                                            @endforeach

                                                  </select>

                                                @if ($errors->has('country'))
                                                <span class="help-block">
                                                    <strong>{{ $errors->first('country') }}</strong>
                                                </span>
                                                @endif
                                       
                                         </div>
                                        <div class="form-group {{ $errors->has('state') ? ' has-error' : '' }}">
                                        <label class="control-label">State <sup style='color:red;'>*</sup> </label>
                                            
                                            <select class="form-control" onchange="getAllCities(this.value)" name="state" id="state">
                                             <option value=""  >--Select--</option>
                                               @foreach($states as $state)
                                                    <option value="{{$state->id}}" @if($state->id==$user_state) selected @endif>{{$state->name}}</option>
                                                @endforeach

                                             </select>

                                        @if ($errors->has('state'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('state') }}</strong>
                                        </span>
                                        @endif
                                      
                                  </div>   
                                      <div class="form-group {{ $errors->has('city') ? ' has-error' : '' }}">
                                        <label class="control-label">City <sup style='color:red;'>*</sup> </label>
                                            
                                            <select class="form-control" name="city" id="city">
                                             <option value=""  >--Select--</option>
                                             @foreach($cities as $city)
                                                    <option value="{{$city->id}}" @if($city->id==$user_city) selected @endif>{{$city->name}}</option>
                                                @endforeach

                                             </select>

                                        @if ($errors->has('city'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('city') }}</strong>
                                        </span>
                                        @endif
                                      
                                  </div>
                                        <div class="form-group{{ $errors->has('user_mobile') ? ' has-error' : '' }}">
                                            <label class="control-label">User Mobile<sup style='color:red;'>*</sup></label>

                                            <input type="text" class="form-control" name="user_mobile" value="{{old('user_mobile',$user_info->userInformation->user_mobile)}}">
                                            @if ($errors->has('user_mobile'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('user_mobile') }}</strong>
                                            </span>
                                            @endif

                                        </div>
                                        <div class="form-group{{ $errors->has('user_status') ? ' has-error' : '' }}">
                                            <label class="control-label">Status<sup style='color:red;'>*</sup> </label>

                                            <select class="form-control" name="user_status" id="user_status">
                                                <option value="">--Select Status--</option>
                                                <option value="1" @if($user_info->userInformation->user_status==1) selected @endif>Active</option>
                                                <option value="2" @if($user_info->userInformation->user_status==2) selected @endif>Blocked</option>

                                            </select>
                                            @if ($errors->has('user_status'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('user_status') }}</strong>
                                            </span>
                                            @endif

                                        </div>
                                        <div class="form-group{{ $errors->has('gender') ? ' has-error' : '' }}">
                                            <label class="control-label">Gender <sup style='color:red;'>*</sup> </label>

                                            <select class="form-control" name="gender" id="gender">
                                                <option value=""  >--Select--</option>
                                                <option value="1" @if($user_info->userInformation->gender==1) selected=selected @endif >Male</option>
                                                <option value="2" @if($user_info->userInformation->gender==2) selected=selected @endif >Female</option>

                                            </select>
                                            @if ($errors->has('gender'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('gender') }}</strong>
                                            </span>
                                            @endif

                                        </div>
                                        <div class="form-group{{ $errors->has('about_me') ? ' has-error' : '' }}">
                                            <label class="control-label">About me</label>
                                            <textarea class="form-control" name="about_me">{{old('about_me',$user_info->userInformation->about_me)}}</textarea>

                                        </div>

                                        <div class="margiv-top-10">
                                            <input type="submit" class="btn green-haze" value="Save Changes">
                                            <a href="{{url('admin/manage-users')}}" class="btn default">
                                                Cancel 
                                            </a>
                                        </div>
                                    </form>
                                </div>
                                <div class="tab-pane @if($errors->has('email') || $errors->has('confirm_email'))  active @endif" id="tab_1_3">
                                    <form name="frm_regsitered_user_update_email"  id="frm_regsitered_user_update_email" role="form" method="POST" action="{{ url('/admin/update-registered-user-email/'.$user_info->id) }}">
                                        {!! csrf_field() !!}
                                        <div class="form-group">
                                            <label class="control-label">Current Email: </label>
                                            <label class="control-label">{{$user_info->email}}</label>
                                        </div>
                                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                            <label class="control-label">New Email</label>

                                            <input type="text" class="form-control" id="email" name="email" value="{{old('email')}}">
                                            @if ($errors->has('email'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('email') }}</strong>
                                            </span>
                                            @endif

                                        </div>
                                        <div class="form-group{{ $errors->has('confirm_email') ? ' has-error' : '' }}">
                                            <label class="control-label">Confirm Email</label>
                                            <input type="text" class="form-control" id="confirm_email" name="confirm_email" value="{{old('confirm_email')}}">
                                            @if ($errors->has('confirm_email'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('confirm_email') }}</strong>
                                            </span>
                                            @endif

                                        </div>
                                        <div class="margiv-top-10">
                                            <input type="submit" class="btn green-haze" value="Change Email">
                                            <a href="{{url('admin/manage-users')}}" class="btn default">
                                                Cancel 
                                            </a>
                                        </div>
                                    </form>
                                </div>
                                <!-- CHANGE PASSWORD TAB -->
                                <div class="tab-pane @if($errors->has('current_password')|| $errors->has('new_password') || $errors->has('confirm_password') || session('password-update-fail')) active @endif" id="tab_1_2">
                                    <form name="frm_regsitered_user_update_password"  id="frm_regsitered_user_update_password" role="form" method="POST" action="{{ url('/admin/update-registered-user-password/'.$user_info->id) }}">
                                        {!! csrf_field() !!}

                                        <div class="form-group{{ $errors->has('new_password') ? ' has-error' : '' }}">
                                            <label class="control-label">New Password</label>

                                            <input type="password" class="form-control" id="new_password" name="new_password" value="{{old('new_password')}}">
                                            @if ($errors->has('new_password'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('new_password') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                        <div class="form-group{{ $errors->has('new_password_confirmation') ? ' has-error' : '' }}">
                                            <label class="control-label">Confirm Password</label>
                                            <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" value="{{old('new_password_confirmation')}}">
                                            @if ($errors->has('confirm_password'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('new_password_confirmation') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                        <div class="margiv-top-10">
                                            <input type="submit" class="btn green-haze" value="Change Password">
                                            <a href="{{url('admin/manage-users')}}" class="btn default">
                                                Cancel 
                                            </a>
                                        </div>
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

 <script>
            
        function getAllStates(country_id)
        {
            if(country_id!='' && country_id!=0)
            {
                $.ajax({
                   url:"{{url('/admin/states/getAllStates')}}/"+country_id,
                   method:'get',
                   success:function(data)
                   {
                         $("#state").html(data);

                   }

                });
                 
            }
        }
        function getAllCities(state_id)
        {
            if(state_id!='' && state_id!=0)
            {
               var country_id=$("#country").val();
                $.ajax({
                   url:"{{url('/admin/cities/getAllCitiesStar')}}/"+country_id+"/"+state_id,
                   method:'get',
                   success:function(data)
                   {

                        $("#city").html(data);

                   }

                });
            }
        }
        function checkForMax(value,id,control_val)
        {
          
           typed_value=($("#"+id+"_"+control_val).val());
           if(typed_value>value)
           {
               alert("Max value you can enter is"+value);
               $("#"+id+"_"+control_val).val(value);
               
           }
        }
  
$(function() {
jQuery.browser = {};
    (function()
    {
        jQuery.browser.msie = false;
        jQuery.browser.version = 0;
        if (navigator.userAgent.match(/MSIE ([0-9]+)\./))
        {
            jQuery.browser.msie = true;
            jQuery.browser.version = RegExp.$1;
        }
    })();
    //For Deliveryt Date Calender:
    $("#date_of_birth").datepicker({
        dateFormat: "yy-mm-dd",
        maxDate: 0,
       
    });
    });      
 </script>
 <style>
     .panel img{
         margin:10px 10px;
     }
 </style>
 
@endsection
