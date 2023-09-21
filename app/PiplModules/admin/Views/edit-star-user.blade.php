@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Update Driver Profile</title>

@endsection

@section('content')
<link rel="stylesheet" type="text/css" href="{{url('public/media/backend/css/datepicker/jquery-ui.css')}}">

<script type="text/javascript"  src="{{url('public/media/backend/js/jquery-ui.min.js')}}"></script>
<script type="text/javascript">
    $(document).ready(function(){
        @if (!($errors->has('comp_name') || $errors->has('comp_reg_no')) || (!($user_info->userInformation->is_company)) && old('type')==0 )
        $("#company_div").hide(); 
//        $("#gender").show(); 
        $("#gender_option").prop('checked',true); 
        $("#company_option").prop('checked',false); 
        
        @endif
        
        @if ((!($errors->has('gender')) && old('type')==1) || ($user_info->userInformation->is_company))
//        $("#gender").hide(); 
        $("#company_div").show(); 
        $("#gender_option").prop('checked',false); 
        $("#company_option").prop('checked',true); 
        @endif
        
      $(document).on('click','#company_option',function(){
         // $("#gender").hide();
          $("#company_div").show();
      }); 
      
      $(document).on('click','#gender_option',function(){
        //  $("#gender").show();
          $("#company_div").hide();
      }); 
    });
</script>
 <?php $html = '';
                    if ($user_info->userInformation->user_status == 0) {
                        $html = '<div  class="pull-right" id="active_div' . $user_info->id . '" style="display:none">
                                        <a class="label label-success" title="User is Active Now" onClick="javascript:void(0)" href="javascript:void(0);" id="status_' . $user_info->id . '">Active</a> </div>';
                        $html = $html . '<div class="pull-right" id="inactive_div' . $user_info->id . '"  style="display:inline-block" >
                                        <a class="label label-warning" title="Click to Change Status" onClick="javascript:changeStatus(' . $user_info->id . ', 1);" href="javascript:void(0);" id="status_' . $user_info->id . '">Click to Activate </a> </div>';
                              
                       
                    } 
                   
?>
<script type="text/javascript">
         function changeStatus(user_id, user_status)
            {
                /* changing the user status*/
                var obj_params = new Object();
                obj_params.user_id = user_id;
                obj_params.user_status = user_status;
                if (user_status == 1)
                { 
                   
                    $("#active_div" + user_id).css('display', 'inline-block');
                    $("#active_div_block" + user_id).css('display', 'inline-block');
                    $("#blocked_div" + user_id).css('display', 'none');
                    $("#blocked_div_block" + user_id).css('display', 'none');
                    $("#inactive_div" + user_id).css('display', 'none');
                }
                jQuery.post("{{url('admin/change_status')}}", obj_params, function (msg) {
                    if (msg.error == "1")
                    {
                        alert(msg.error_message);
                        $("#active_div" + user_id).css('display', 'none');
                        $("#active_div_block" + user_id).css('display', 'none');
                        $("#inactive_div" + user_id).css('display', 'block');
                    }
                    else
                    {
                        
                        /* toogling the bloked and active div of user*/
                        if (user_status == 1)
                        { 
                            alert(msg.message);
                            $("#active_div" + user_id).css('display', 'inline-block');
                            $("#active_div_block" + user_id).css('display', 'inline-block');
                            $("#blocked_div" + user_id).css('display', 'none');
                            $("#blocked_div_block" + user_id).css('display', 'none');
                            $("#inactive_div" + user_id).css('display', 'none');
                        }
                        else if(user_status == 0)
                        { 
                             $("#active_div" + user_id).css('display', 'inline-block');
                             $("#active_div_block" + user_id).css('display', 'inline-block');
                            $("#blocked_div" + user_id).css('display', 'none');
                            $("#blocked_div_block" + user_id).css('display', 'none');
                            $("#inactive_div" + user_id).css('display', 'none');
                            
                        }else{
                            $("#active_div" + user_id).css('display', 'none');
                            $("#active_div_block" + user_id).css('display', 'none');
                            $("#blocked_div" + user_id).css('display', 'inline-block');
                            $("#blocked_div_block" + user_id).css('display', 'inline-block');
                            $("#inactive_div" + user_id).css('display', 'none');
                        }
                    }

                }, "json");

            }
    function selectAllServices(cat_id){
 
        if($("#category_"+cat_id).prop('checked')){
            $(".services_"+cat_id).prop('checked',true);
   
        }else{
           $(".services_"+cat_id).prop('checked',false);
        }
    }
    function selectAllLang(){
 
        if($("#language").prop('checked')){
            $(".lang").prop('checked',true);
   
        }else{
           $(".lang").prop('checked',false);
        }
    }
</script>
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
                <a href="{{url('admin/star-users')}}">Manage Drivers</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="javascript:void(0);">Update Driver Profile</a>
               
            </li>
        </ul>
        <div class="profile-content">
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet light">
                        <div class="portlet-title tabbable-line">
                            <div class="caption caption-md">
                                <i class="icon-globe theme-font hide"></i>
                                <span style="padding-right:10px;" class="caption-subject font-blue-madison bold uppercase">Update Driver Profile</span>
                                {!!$html!!}
                            </div>
                            
                            <ul class="nav nav-tabs">
                               
                                <li class="@if($errors->has('driver_license')|| $errors->has('licence_no') || $errors->has('geo_fence') ) active @endif">
                                    <a href="#tab_1_8" data-toggle="tab">Document</a>
                                </li>
                                <li class="@if($errors->has('category') || session('service-updated')) active @endif">
                                    <a href="#tab_1_4" data-toggle="tab">Service</a>
                                </li>  
                                <li class="@if($errors->has('vehcile_details')) active @endif">
                                    <a href="#tab_1_9" data-toggle="tab">Vehicle</a>
                                </li>
                                 <li class="@if($errors->has('payment_method') || session('payment-method-updated')) active @endif">
                                    <a href="#tab_1_7" data-toggle="tab">Payment Methods</a>
                                </li>
                                <li class="@if(!($errors->has('email') || session('update-image-success') || $errors->has('confirm_email')|| $errors->has('current_password')|| $errors->has('new_password') || $errors->has('confirm_password') || session('password-update-fail') || $errors->has('category') || session('service-updated')  || $errors->has('language') || session('language-updated') || session('payment-method-updated') )) active @endif">
                                    <a href="#tab_1_1" data-toggle="tab">Profile</a>
                                </li>
                            
                               
                                <li class="@if (session('update-image-success')) active @endif ">
                                    <a href="#tab_1_6" data-toggle="tab">Profile Image</a>
                                </li>

                                <li class="@if($errors->has('language') || session('language-updated')) active @endif">
                                    <a href="#tab_1_5" data-toggle="tab">Preferred language Details</a>
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
                        @if (session('vehicle-updated'))
                        <div class="alert alert-success">
                            {{ session('vehicle-updated') }}
                        </div>
                        @endif
                        @if (session('password-update-fail'))
                        <div class="alert alert-danger">
                            {{ session('password-update-fail') }}
                        </div>
                        @endif
                        @if (session('service-updated'))
                        <div class="alert alert-success">
                            {{ session('service-updated') }}
                        </div>
                        @endif
                        @if (session('language-updated'))
                        <div class="alert alert-success">
                            {{ session('language-updated') }}
                        </div>
                        @endif
                        @if (session('update-image-success'))
                        <div class="alert alert-success">
                            {{ session('update-image-success') }}
                        </div>
                        @endif
                        @if (session('language-updated'))
                        <div class="alert alert-success">
                            {{ session('language-updated') }}
                        </div>
                        @endif
                        @if (session('payment-method-updated'))
                        <div class="alert alert-success">
                            {{ session('payment-method-updated') }}
                        </div>
                        @endif
                        <div class="portlet-body">
                            <div class="tab-content">
                                <!-- PERSONAL INFO TAB -->
                                <div class="tab-pane @if(!($errors->has('email') || $errors->has('password')|| $errors->has('confirm_password')||$errors->has('confirm_email')|| $errors->has('driver_license')|| $errors->has('licence_no') || $errors->has('geo_fence') || $errors->has('category') || session('service-updated')||session('update-image-success') || $errors->has('language') || session('language-updated') || session('payment-method-updated'))) active @endif" id="tab_1_1">
                                    <form name="frm_star_user_update"  id="frm_star_user_update" role="form" method="POST" action="{{ url('/admin/update-star-user/'.$user_info->id)}}">
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
                                            <label class="control-label">Mobile No.<sup style='color:red;'>*</sup></label>

                                            <input type="text" class="form-control" name="user_mobile" value="{{old('user_mobile',$user_info->userInformation->user_mobile)}}">
                                            @if ($errors->has('user_mobile'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('user_mobile') }}</strong>
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
                                      @if(Auth::user()->userInformation->user_type!='4')  
                                       <div class="form-group {{ $errors->has('nationality') ? ' has-error' : '' }}">
                                        <label class="control-label">Nationality <sup style='color:red;'>*</sup> </label>
                                           <select class="form-control"  name="nationality" id="nationality">
                                             <option value="" selected="">--Select--</option>
                                                @foreach($nationality as $national)
                                                    <option value="{{$national->id}}" @if((isset($user_info->userInformation->nationality)) && ($national->id==$user_info->userInformation->nationality)) selected @endif>{{$national->country_name}}</option>
                                                    
                                                    @endforeach

                                             </select>

                                        @if ($errors->has('nationality'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('nationality') }}</strong>
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
                                    <div class="form-group{{ $errors->has('address') ? ' has-error' : '' }}">
                                            <label class="control-label">Address</label>

                                            <textarea class="form-control" name="address">{{old('address',$address)}}</textarea>

                                        </div>
                                    @endif    
                                    
                                         <div class="form-group {{ $errors->has('type') ? ' has-error' : '' }}">
                                            <label class=" control-label"> Type <sup style='color:red;'>*</sup> </label>
                                             
                                                 <input type="radio" name="type" value="0" @if($user_info->userInformation->is_company=='0') checked @endif  id="gender_option"/> Driver
                                                 <input type="radio" name="type" value="1" @if($user_info->userInformation->is_company=='1') checked @endif   id="company_option" /> Owner

                                            @if ($errors->has('type'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('type') }}</strong>
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
                                        <!-- company_info -->
                                        <div id="company_div">  
                                                    
                          <div class="form-group {{ $errors->has('comp_reg_no') ? ' has-error' : '' }}">
                                    <label class="control-label">Owner Name:  <sup style='color:red;'>*</sup></label>
                                    <div class="">  
                                   <input type="text" class="form-control" id="owner_name" name="owner_name" value="{{old('owner_name',isset($user_info->userInformation->owner_name)?$user_info->userInformation->owner_name:'')}}">
                                    @if ($errors->has('owner_name'))
                                             <span class="help-block">
                                                <strong>{{ $errors->first('owner_name') }}</strong>
                                             </span>
                                      @endif
                                </div>
                            </div>
                           <div class="form-group {{ $errors->has('description') ? ' has-error' : '' }}">
                                    <label class="control-label">Owner Number:</label>
                                    <div class="">  
                                     <input type="text" class="form-control" id="owner_number" name="owner_number" value="{{old('owner_number',isset($user_info->userInformation->owner_number)?$user_info->userInformation->owner_number:'')}}">
                                    @if ($errors->has('owner_number'))
                                             <span class="help-block">
                                                <strong>{{ $errors->first('owner_number') }}</strong>
                                             </span>
                                      @endif
                                </div>
                                </div>
                                      </div>
                                        
                                        <div class="form-group{{ $errors->has('working_time') ? ' has-error' : '' }}">
                                            <label class="control-label">Working Time <sup style='color:red;'>*</sup> </label>

                                            <select class="form-control" name="working_time" id="working_time">
                                                <option value="" >--Select--</option>
                                                <option value="0" @if($user_info->userInformation->working_time==0) selected=selected @endif >Part Time</option>
                                                <option value="1" @if($user_info->userInformation->working_time==1) selected=selected @endif >Full Time</option>

                                            </select>
                                            @if ($errors->has('working_time'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('working_time') }}</strong>
                                            </span>
                                            @endif

                                        </div>
                                    
                                        
                                        

                                        <div class="margiv-top-10">
                                            <input type="submit" class="btn green-haze" value="Save Changes">
                                            <a href="{{url('admin/star-users')}}" class="btn default">
                                                Cancel 
                                            </a>
                                        </div>
                                    </form>
                                </div>
                                <div class="tab-pane @if($errors->has('email') || $errors->has('confirm_email'))  active @endif" id="tab_1_3">
                                    <form name="frm_star_user_update_email"  id="frm_star_user_update_email" role="form" method="POST" action="{{ url('/admin/update-star-user-email/'.$user_info->id) }}">
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
                                            <a href="{{url('admin/star-users')}}" class="btn default">
                                                Cancel 
                                            </a>
                                        </div>
                                    </form>
                                </div>
                                
                                  <div class="tab-pane @if($errors->has('current_password')|| $errors->has('password') || $errors->has('password_confirmation') || session('password-update-fail')) active @endif" id="tab_1_2">
                                    <form name="frm_star_user_update_password"  id="frm_star_user_update_password" role="form" method="POST" action="{{ url('/admin/update-star-user-password/'.$user_info->id) }}">
                                        {!! csrf_field() !!}
                                        
                                        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                                            <label class="control-label">New Password</label>

                                            <input type="password" class="form-control" id="password" name="password" value="{{old('password')}}">
                                            @if ($errors->has('password'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('password') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                        <div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
                                            <label class="control-label">Confirm Password</label>
                                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" value="{{old('password_confirmation')}}">
                                            @if ($errors->has('confirm_password'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('password_confirmation') }}</strong>
                                            </span>
                                            @endif

                                        </div>
                                        <div class="margiv-top-10">
                                            <input type="submit" class="btn green-haze" value="Change Password">
                                            <a href="{{url('/admin/star-users')}}" class="btn default">
                                                Cancel 
                                            </a>
                                        </div>
                                    </form>
                                </div>

                                <!-- END CHANGE PASSWORD TAB -->
                                 <!--Document  TAB--> 
                                <div class="tab-pane @if($errors->has('driver_license')|| $errors->has('licence_no') || $errors->has('geo_fence') ) active @endif" id="tab_1_8">
                                    <form name="update_documents"  id="update_document" role="form" method="POST" action="{{ url('/admin/update-star-user-documents/'.$user_info->id) }}" enctype="multipart/form-data">
                                        {!! csrf_field() !!}

                                        <div class="form-group{{ $errors->has('licence_no') ? ' has-error' : '' }}">
                                            <label class="control-label">License Number</label>
                                            <input type="text" class="form-control" id="licence_no" name="licence_no" value="{{old('licence_no',isset($user_info->driverUserInformation->driver_license)?$user_info->driverUserInformation->driver_license:'') }}">
                                            @if ($errors->has('licence_no'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('licence_no') }}</strong>
                                            </span>
                                            @endif

                                        </div>                                       
                                        <div class="form-group{{ $errors->has('id_number') ? ' has-error' : '' }}">
                                            <label class="control-label">BADGE NO</label>
                                            <input type="text" class="form-control" id="id_number" name="id_number" value="{{old('id_number',isset($user_info->driverUserInformation->id_number)?$user_info->driverUserInformation->id_number:'')}}">
                                            @if ($errors->has('id_number'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('id_number') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                        <div class="form-group {{ $errors->has('driver_license') ? ' has-error' : '' }}">
                                            <label class="">Driver License:</label>
                                            <input name="driver_license" dir="ltr" type="file" class="" id="driver_license" value="" size="80"  autocomplete="off">
                                            <div id="img_div" style="display: none" ><img id="imageHolder" src=""accesskey=" " width="150" height="150"></div>
                                            @if ($errors->has('driver_license'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('driver_license') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                        @if($user_info->driverUserInformation->driver_license_flle!='')
                                         <div class="form-group">
                                            <label for="parametername">Driver License:</label>
                                            <div class="controls"> 
                                                <a id="imageHolder" href="{{url('/storageasset/star-document/'.old('driver_license',isset($user_info->driverUserInformation->driver_license_flle)?$user_info->driverUserInformation->driver_license_flle:''))}}" target="_blank"> {{isset($user_info->driverUserInformation->driver_license_flle)?$user_info->driverUserInformation->driver_license_flle:''}}</a></div>
                                        </div>
                                      @endif
                                      <p>-----------------------------</p>
                                      <p><b>OTHER DOCUMENTS</b></p>
                                      @if(count($driverDocuments)>0)
                                         @foreach($driverDocuments as $document)
                                            <div class="form-group">
                                              Document Name:- <label for="parametername">{{isset($document->document_name)?$document->document_name:'-'}}</label>
                                               <div class="controls"> 
                                                 Document:-  <a id="imageHolder" href="{{url('/storageasset/star-document/'.old('driver_license',isset($document->file)?$document->file:''))}}" target="_blank"> {{isset($document->file)?$document->file:''}}</a></div>
                                           </div>
                                        @endforeach 
                                      @endif
                                      
                                        <div class="form-group {{ $errors->has('driver_license') ? ' has-error' : '' }}">
                                            <label class="">Document Name:</label>
                                            <input class='form-control' name="document_name" dir="ltr" type="text" class="" id="document_name" value='document'>
                                            <div id="img_div" style="display: none" ><img id="imageHolder" src=""accesskey=" " width="150" height="150"></div>
                                            @if ($errors->has('document_name'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('document_name') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                        <div class="form-group {{ $errors->has('driver_license') ? ' has-error' : '' }}">
                                            <label class="">Select File:</label>
                                            <input name="file" dir="ltr" type="file" class="" id="file" value="" size="80"  autocomplete="off">
                                            <div id="img_div" style="display: none" ><img id="imageHolder" src=""accesskey=" " width="150" height="150"></div>
                                            @if ($errors->has('file'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('file') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                        <div class="margiv-top-10">
                                            <input type="submit" class="btn green-haze" value="Save Changes">
                                            <a href="{{url('/admin/star-users')}}" class="btn default">
                                                Cancel 
                                            </a>
                                        </div>
                                    </form>
                                </div>

                                 <!--END Document TAB--> 
                                   <!--Services  TAB--> 
                                <div class="tab-pane @if($errors->has('category') || session('service-updated') ) active @endif" id="tab_1_4">
                                    <form name="update_services"  id="update_services" role="form" method="POST" action="{{ url('/admin/update-star-user-services/'.$user_info->id) }}" >
                                        {!! csrf_field() !!}
                                        <div class="form-group {{ $errors->has('category') ? ' has-error' : '' }}">
                                        @if ($errors->has('category'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('category') }}</strong>
                                            </span>
                                        @endif
                                        </div>
                                        @foreach($categories as $category)
                                            
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                  {{$category->name}}
                                                  <p class="pull-right">
                                                      Select All <input type="checkbox"  id="category_{{$category->id}}"  onClick="javascript:selectAllServices({{$category->id}});" value="{{$category->id}}" > 
                                                  </p>
                                            </div>
                                       
                                                <input type="hidden"  name="category[]" value="{{$category->id}}" > 
                                                <div class="panel-body" id="cate_div_{{$category->id}}">
                                                    
                                                 @foreach($services as $service)
                                                 <?php 
                                                   $max_range=$service->max_range;
                                                   ?>
                                               
                                                  @if ($category->id==$service->category_id)
                                                  <div class="form-group">
                                                      <input type="checkbox" class="services_{{$category->id}}" id="service_{{$service->id}}"
                                                   <?php $geo_fence_area=0;?>        
                                                  @foreach($user_services as $user_service)                                                  
                                                    @if ($user_service->service_id==$service->id)
                                                   <?php  
                                                            $geo_fence_area=$user_service->goe_fence_area;
                                                            
                                                   
                                                    ?>
                                                    checked="checked"
                                                    @endif
                                                   @endforeach  name="services[]" value="{{$service->id}}" >
                                                   
                                                   <label class="control-label">{{$service->name}} (the value selected is the maximum value in KM)</label>
                                                      <input onblur="checkForMax({{$max_range}},'geo_area',{{$service->id}})" value="{{(isset($geo_fence_area)&& $geo_fence_area>0)?$geo_fence_area:$max_range}}" type="number" max="{{$max_range}}" class="form-control" name="geo_area_{{$service->id}}" id="geo_area_{{$service->id}}" placeholder="Enter Goe Fence Area (In km)">  
                                                    </div>
                                                  @endif
                                                 @endforeach
                                            </div>
                                            </div>
                                        @endforeach
                                         
                                        
                                        <div class="margiv-top-10">
                                            <input type="submit" class="btn green-haze" value="Save Changes">
                                            <a href="{{url('/admin/star-users')}}" class="btn default">
                                                Cancel 
                                            </a>
                                        </div>
                                    </form>
                                </div>

                                 <!--END Services TAB--> 
                                   <!-- Spoken Language  TAB--> 
                                <div class="tab-pane @if($errors->has('language') || session('language-updated') ) active @endif" id="tab_1_5">
                                    <form name="update_language"  id="language_services" role="form" method="POST" action="{{ url('/admin/update-star-user-spoken-language/'.$user_info->id) }}" >
                                        {!! csrf_field() !!}
                                        
                                       
                                            
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                  Preferred languages
                                                  <p class="pull-right">
                                                      Select All <input type="checkbox"  id="language"  onClick="javascript:selectAllLang();" > 
                                                  </p>
                                            </div>
                                       
                                            <div class="panel-body" id="cate_div_{{$category->id}}">
                                                 @foreach($languages as $lanuage)
                                                 
                                                  <div class="form-group">
                                                      <input type="checkbox" class="lang" id="service_{{$lanuage->id}}"
                                                           
                                                  @foreach($user_languages as $user_language)
                                                    @if ($user_language->spoken_language_id==$lanuage->id)
                                                    checked="checked"
                                                    @endif
                                                   @endforeach  name="language[]" value="{{$lanuage->id}}" >
                                                   <label class="control-label">{{$lanuage->name}}</label>
                                                    </div>
                                                
                                                 @endforeach
                                            </div>
                                            </div>
                                       
                                         
                                        
                                        <div class="margiv-top-10">
                                            <input type="submit" class="btn green-haze" value="Save Changes">
                                            <a href="{{url('/admin/star-users')}}" class="btn default">
                                                Cancel 
                                            </a>
                                        </div>
                                    </form>
                                </div>
                                   
                                <div class="tab-pane @if($errors->has('payment_method') || session('payment-method-updated') ) active @endif" id="tab_1_7">
                                    <form class="form-horizontal" name="update_payment_method"  id="update_payment_method" role="form" method="POST" action="{{ url('/admin/update-star-payment-methods/'.$user_info->id) }}" >
                                        {!! csrf_field() !!}
                                        
                                       
                                            
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                  Select Payment Methods for this star
                                                
                                            </div>
                                        <div class="row">
                                          <div class="col-md-12">     
                                         <div class="col-md-6 col-md-border"> 
                                            <div class="panel-body" id="cate_div">
                                               @if(isset($payment_methods) && count($payment_methods)>0) 
                                                 @foreach($payment_methods as $payment)
                                                  <div class="form-group">
                                                    <input type="checkbox" class="lang" id="payment_method_{{$lanuage->id}}"
                                                    @foreach($user_payment_methods as $user_method)
                                                    @if ($user_method->payment_method_id==$payment->id)
                                                    checked="checked"
                                                    @endif
                                                   @endforeach  name="payment_method[]" value="{{$payment->id}}" >
                                                     <label class="control-label">{{$payment->title}}</label>
                                                  </div>
                                                
                                                 @endforeach
                                                 @endif
                                               <div class="panel-heading">
                                                   <b>Bank Details</b>                                                
                                                 </div>
                                             
                                                  <div class="form-group">
                                                        <label class="col-md-12 pull-left">Bank Name: </label>
                                                        <div class="col-md-12">     
                                                             <input class="form-control" name="bank_name" value="{{old('bank_name', $driverOtherInfo->bank_name)}}" />
                                                                @if ($errors->has('bank_name'))
                                                                <span class="help-block">
                                                                    <strong class="text-danger">{{ $errors->first('bank_name') }}</strong>
                                                                </span>
                                                             @endif
                                                            <span class="help-block">
                                                               
                                                            </span>
                                                        </div>
                                                    </div>
                                                 <div class="form-group">
                                                        <label class="col-md-12 pull-left">Bank IFSC CODE: </label>
                                                        <div class="col-md-12">     
                                                             <input class="form-control" name="ifsc_code" value="{{old('ifsc_code', $driverOtherInfo->ifsc_code)}}" />
                                                                @if ($errors->has('ifsc_code'))
                                                                <span class="help-block">
                                                                    <strong class="text-danger">{{ $errors->first('ifsc_code') }}</strong>
                                                                </span>
                                                             @endif
                                                            <span class="help-block">
                                                               
                                                            </span>
                                                        </div>
                                                    </div>
                                                 <div class="form-group">
                                                        <label class="col-md-12 pull-left">Branch Name </label>
                                                        <div class="col-md-12">     
                                                             <input class="form-control" name="branch_name" value="{{old('branch_name', $driverOtherInfo->branch_name)}}" />
                                                                @if ($errors->has('branch_name'))
                                                                <span class="help-block">
                                                                    <strong class="text-danger">{{ $errors->first('branch_name') }}</strong>
                                                                </span>
                                                             @endif
                                                            <span class="help-block">
                                                               
                                                            </span>
                                                        </div>
                                                    </div>
                                                 <div class="form-group">
                                                        <label class="col-md-12 pull-left">Account Number</label>
                                                        <div class="col-md-12">     
                                                             <input class="form-control" name="account_number" value="{{old('account_number', $driverOtherInfo->account_number)}}" />
                                                                @if ($errors->has('account_number'))
                                                                <span class="help-block">
                                                                    <strong class="text-danger">{{ $errors->first('account_number') }}</strong>
                                                                </span>
                                                             @endif
                                                            <span class="help-block">
                                                               
                                                            </span>
                                                        </div>
                                                    </div>
                                            </div>
                                            </div>
                                            </div>
                                            </div>
                                            </div>
                                        <div class="margiv-top-10">
                                            <input type="submit" class="btn green-haze" value="Save Changes">
                                            <a href="{{url('/admin/star-users')}}" class="btn default">
                                                Cancel 
                                            </a>
                                        </div>
                                    </form>
                                </div>   
                               @if(count($driverVehicle)>0)
                                <div class="tab-pane @if($errors->has('vehicle_details')||$errors->has('vehicle_desc') ||$errors->has('plate_number') ||$errors->has('vehicle_name') || session('vehicle-updated')) active @endif" id="tab_1_9">
                                   <div class="panel panel-default">
                                            <div class="panel-heading">
                                                 Vehicle Details
                                            </div>
                                      </div>
                                   <div class="portlet-body form">
                                     <div class="form-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                             <div class="col-md-6 col-md-border">
                                                    <div class="form-group">
                                                        <label class="col-md-6 control-label">Make: </label>
                                                        <div class="col-md-6">     
                                                            <span class="help-block">
                                                                {{isset($driverVehicle->vehicleInformation->vehicle_name)?($driverVehicle->vehicleInformation->vehicle_name):''}}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="col-md-6 control-label">Plate Number: </label>
                                                        <div class="col-md-6">     
                                                            <span class="help-block">
                                                                {{isset($driverVehicle->vehicleInformation->plate_number)?($driverVehicle->vehicleInformation->plate_number):''}}
                                                            </span>
                                                        </div>
                                                    </div>
                                                     <div class="form-group">
                                                        <label class="col-md-6 control-label">Vehicle Details: </label>
                                                        <div class="col-md-6">     
                                                            <span class="help-block">
                                                                {{isset($driverVehicle->vehicleInformation->vehicle_desc)?($driverVehicle->vehicleInformation->vehicle_desc):''}}
                                                            </span>
                                                        </div>
                                                    </div>
                                                 @if(isset($driverVehicle->vehicleInformation->year_manufacture) && ($driverVehicle->vehicleInformation->year_manufacture!=''))
                                                     <div class="form-group">
                                                        <label class="col-md-6 control-label">Year of Manufacture: </label>
                                                        <div class="col-md-6">     
                                                            <span class="help-block">
                                                                {{isset($driverVehicle->vehicleInformation->year_manufacture)?($driverVehicle->vehicleInformation->year_manufacture):''}}
                                                            </span>
                                                        </div>
                                                    </div>
                                                 @endif
                                                @if(isset($driverVehicle->vehicleInformation->financial_type)) 
                                                  <div class="form-group">
                                                        <label class="col-md-6 control-label">Financial Term: </label>
                                                        <div class="col-md-6">     
                                                            <span class="help-block">
                                                                {{($driverVehicle->vehicleInformation->financial_type=='0')?'Owned':'Finance'}}
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endif 
                                                 @if(isset($driverVehicle->vehicleInformation->vehicle_image))
                                                    <div class="form-group">
                                                        <label class="col-md-6 control-label">Vehicle Image: </label>
                                                        <div class="col-md-6">     
                                                            <span class="help-block">
                                                                <img width="100" src="{{asset("/storageasset/vehicle-images/".$driverVehicle->vehicleInformation->vehicle_image)}}">
                                                            </span>
                                                        </div>
                                                    </div>
                                                 @endif

                                               </div>
                                             </div>
                                             </div>
                                             </div>
                                             </div>
                                    </div> 
                                 @else
                                    <div class="tab-pane @if($errors->has('vehicle_details')||$errors->has('vehicle_desc') ||$errors->has('plate_number') ||$errors->has('vehicle_name') || session('vehicle-updated')) active @endif" id="tab_1_9">
                                  <form class="form-horizontal" name="frm_vehicle_add"  id="frm_vehicle_add" role="form" action="{{ url('/admin/update-star-vehicle/'.$user_info->id) }}" method="post" enctype="multipart/form-data">
                                                <input type='hidden'  name='type' id='type' value='0'>
                                        {!! csrf_field() !!}
                                        <div class="form-body">
                                    <div class="row">
                                        <div class="col-md-12">    
                                        <div class="col-md-8">  
                                            <div class="form-group @if ($errors->has('vehicle_name')) has-error @endif" style='@if($user_info->id=='0') display:none @endif'>
                                                <div class="col-md-6"> 

                                                     <button type="button" onclick="showHideAddNewExisting('add_new_row','select_from_existing')" id="submit" class="btn btn-primary  pull-right">Add New</button>
                                                    </div>
                                                    <div class="col-md-6">  
                                                     <button type="button" onclick="showHideAddNewExisting('select_from_existing','add_new_row')" id="submit" class="btn btn-primary  pull-right">Select From Existing</button>
                                                 </div>
                                             </div>
                                             <div id='add_new_row' style='@if (!($errors->has('vehicle_name')||$errors->has('plate_number')||$errors->has('vehicle_desc')||$errors->has('status'))) @if(isset($user_info->id) && $user_info->id!='0') display:none @endif  @endif'>
                                                <div class="form-group @if ($errors->has('vehicle_name')) has-error @endif">
                                                    <label class="col-md-6 control-label">Make/Model<sup>*</sup></label>
                                                    <div class="col-md-6">     
                                                        <input class="form-control" name="vehicle_name" value="{{old('vehicle_name')}}" />
                                                        @if ($errors->has('vehicle_name'))
                                                        <span class="help-block">
                                                            <strong class="text-danger">{{ $errors->first('vehicle_name') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="form-group @if ($errors->has('plate_number')) has-error @endif">
                                                    <label class="col-md-6 control-label">Plate Number<sup>*</sup></label>
                                                    <div class="col-md-6">     
                                                        <input class="form-control" name="plate_number" value="{{old('plate_number')}}" />
                                                        @if ($errors->has('plate_number'))
                                                        <span class="help-block">
                                                            <strong class="text-danger">{{ $errors->first('plate_number') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>
                                                </div>
                                        <div class="form-group @if ($errors->has('financial_type')) has-error @endif">
                                        <label class="col-md-6 control-label">Finance Type<sup>*</sup></label>
                                        <div class="col-md-6">     
                                            <input checked="" name="financial_type" value="0" type='radio'>Owned
                                            <input name="financial_type" value="1" type='radio'>Finance
                                            @if ($errors->has('financial_type'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('financial_type') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>     
                                    <div class="form-group @if ($errors->has('year_manufacture')) has-error @endif">
                                        <label class="col-md-6 control-label">Manufacture Year<sup>*</sup></label>
                                        <div class="col-md-6">     
                                            <select class='form-control' name='year_manufacture' id='year_manufacture'>
                                                <option value=''>--Select--</option>
                                                 <?php for($i=1980;$i<=date('Y');$i++)
                                                 {?>
                                                     <option value='{{$i}}'>{{$i}}</option>
                                                 <?php }?>    
                                            </select>
                                            @if ($errors->has('year_manufacture'))
                                            <span class="help-block">
                                                <strong class="text-danger">{{ $errors->first('year_manufacture') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>     
                                <div class="form-group @if ($errors->has('vehicle_desc')) has-error @endif">
                                    <label class="col-md-6 control-label">Description<sup>*</sup></label>
                                    <div class="col-md-6">     
                                        <textarea class="form-control" name="vehicle_desc" >{{old('vehicle_desc')}}</textarea>
                                        @if ($errors->has('vehicle_desc'))
                                        <span class="help-block">
                                            <strong class="text-danger">{{ $errors->first('vehicle_desc') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-6 control-label">Vehicle Image</label>
                                    <div class="col-md-6">     
                                        <input type="file" class="form-control" name="vehicle_image" id="vehicle_image">
                                        <div id="image-holder" class="thumb-image"></div>
                                    </div>
                                </div>
                               
                                                                     <div class="form-group">
                                                                    <div class="col-md-12">   
                                                                        <button type="submit" id="submit" class="btn btn-primary  pull-right">Add</button>
                                                                    </div>
                                                                </div>
                                                                 </div> 
                                                                   <div id='select_from_existing' style='@if (!$errors->has('vehicle_list'))  display:none @endif'>
                                                                        @if(count($user_vehicles)>0)
                                                                        <div class="form-group @if ($errors->has('vehicle_list')) has-error @endif">
                                                                       
                                                                            <label class="col-md-6 control-label">Select A vehicle<sup>*</sup></label>
                                                                            <div class="col-md-6">    

                                                                             <select class="form-control" name='vehicle_list' id='vehicle_list'>
                                                                                    <option value=''>--Select--</option>
                                                                                    @foreach($user_vehicles as $vehicle_list)
                                                                                        <option value="{{$vehicle_list->id}}">{{$vehicle_list->vehicle_name}}/{{$vehicle_list->plate_number}}</option>
                                                                                    @endforeach
                                                                             </select>
                                                                             @if ($errors->has('vehicle_list'))
                                                                              <span class="help-block">
                                                                                 <strong class="text-danger">{{ $errors->first('vehicle_list') }}</strong>
                                                                              </span>
                                                                             @endif
                                                                            </div>
                                                                            
                                                                    </div>
                                                                    <div class="form-group">
                                                                      <div class="col-md-12">   
                                                                        <button type="submit" id="submit" class="btn btn-primary  pull-right">Add</button>
                                                                      </div>
                                                                     </div>
                                                                        @else
                                                                        Sorry no unassigned vehicle found
                                                                     @endif   
                                                                   </div>

                                                             </div>
                                                        </div>
                                                        </div>
                                                        </div>
                                  </form>
                                  
                                   
                                   
                                </div>   
                                  
                                 @endif
                                 
                                  <div class="tab-pane @if (session('update-image-success')) active @endif" id="tab_1_6" >
                                       <div class="panel panel-default">
                                            <div class="panel-heading">
                                                Driver Profile Image 
                                            </div>
                                         @if(isset($user_info->userInformation->profile_picture) && $user_info->userInformation->profile_picture!='')
                                         
                                           <img width="300px" alt="Driver Image"  src="{{asset('storageasset/user-images/'.$user_info->userInformation->profile_picture)}}">
                                        @else   
                                            No image uploaded yet
                                       @endif     
                                        </div>
                                    @if($user_info->userInformation->profile_picture_temp)  
                                       <div class="panel panel-default">
                                            <div class="panel-heading">
                                                Driver Image to Approve
                                            </div>
                                           <img width="300px" alt="Driver Image" src="{{asset('storageasset/user-images/'.$user_info->userInformation->profile_picture_temp)}}">
                                            
                                           <a href='{{url('/admin/approve-star-user-image/'.$user_info->id) }}' class="btn btn-info">Approve Image</a>
                                       
                                        </div>
                                    @endif
                                    <form name="update_image"  id="update_image" role="form" enctype="multipart/form-data" method="POST" action="{{ url('/admin/update-star-user-image/'.$user_info->id) }}" >
                                        {!! csrf_field() !!}
                                       
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                  Upload A New Image 
                                            </div>
                                            <input type="file" name="profile_picture" id="profile_picture">
                                        </div>
                                        <div class="margiv-top-10">
                                            <input type="submit" class="btn green-haze" value="Save Changes">
                                            <a href="{{url('/admin/star-users')}}" class="btn default">
                                                Cancel 
                                            </a>
                                        </div>
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
 </script>
 <style>
     .panel img{
         margin:10px 10px;
     }
 </style>
 <script>
    $("#plate_number_image").change(function() {
        var fileExtension = ['jpeg', 'jpg', 'png', 'gif', 'bmp'];
        if ($.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
            $(this).val('');
            alert("Only formats are allowed : " + fileExtension.join(', '));
        } else {
            if (typeof (FileReader) != "undefined") {
                var image_holder = $("#image-holder-number");
                image_holder.empty();
                var reader = new FileReader();
                reader.onload = function(e) {
                    $("<img />", {
                        "src": e.target.result,
                        "width": '200',
                        "hight": '200',
                        "class": "thumb-image-number"
                    }).appendTo(image_holder);
                }
                image_holder.show();
                reader.readAsDataURL($(this)[0].files[0]);
                $(this).prev().css('display', 'none')
            } else {
                alert("This browser does not support FileReader.");
            }
        }
    });
     $("#vehicle_image").change(function() {
        var fileExtension = ['jpeg', 'jpg', 'png', 'gif', 'bmp'];
        if ($.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
            $(this).val('');
            alert("Only formats are allowed : " + fileExtension.join(', '));
        } else {
            if (typeof (FileReader) != "undefined") {
                var image_holder = $("#image-holder");
                image_holder.empty();
                var reader = new FileReader();
                reader.onload = function(e) {
                    $("<img />", {
                        "src": e.target.result,
                        "width": '200',
                        "hight": '200',
                        "class": "thumb-image"
                    }).appendTo(image_holder);
                }
                image_holder.show();
                reader.readAsDataURL($(this)[0].files[0]);
                $(this).prev().css('display', 'none')
            } else {
                alert("This browser does not support FileReader.");
            }
        }
    });
    
    function showHideAddNewExisting(id,id1)
    {
        $("#"+id).show();
        $("#"+id1).hide();
        
        if(id=='select_from_existing')
        {
            $("#type").val('1');
        }else{
             $("#type").val('0');
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
@endsection
