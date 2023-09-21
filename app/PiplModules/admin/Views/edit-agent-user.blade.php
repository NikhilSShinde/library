@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Update Agent User Profile </title>

@endsection

@section('content')
<script type="text/javascript">
    $(document).ready(function(){
        @if (!($errors->has('comp_name') || $errors->has('comp_reg_no')) || (!($user_info->userInformation->is_company)) && old('type')==0 )
        $("#company_div").hide(); 
        $("#gender").show(); 
        $("#gender_option").prop('checked',true); 
        $("#company_option").prop('checked',false); 
        
        @endif
        
        @if ((!($errors->has('gender')) && old('type')==1) || ($user_info->userInformation->is_company))
        $("#gender").hide(); 
        $("#company_div").show(); 
        $("#gender_option").prop('checked',false); 
        $("#company_option").prop('checked',true); 
        @endif
        
      $(document).on('click','#company_option',function(){
          $("#gender").hide();
          $("#company_div").show();
      }); 
      
      $(document).on('click','#gender_option',function(){
          $("#gender").show();
          $("#company_div").hide();
      }); 
    });
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
                <a href="{{url('admin/agent-users')}}">Manage Agent users</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="javascript:void(0);">Update Agent User Profile</a>
            </li>
        </ul>
        <div class="profile-content">
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet light">
                        <div class="portlet-title tabbable-line">
                            <div class="caption caption-md">
                                <i class="icon-globe theme-font hide"></i>
                                <span class="caption-subject font-blue-madison bold uppercase">Update Agent User Profile</span>
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
                                    <form name="frm_agent_update"  id="frm_agent_update" role="form" method="POST" action="{{ url('/admin/update-agent-user/'.$user_info->id)}}">
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
                                     @if((Auth::user()->userInformation->user_type!='4' && Auth::user()->userInformation->user_type!='6') || $country_login=='17' )          
                     
                                       <div class="form-group {{ $errors->has('country') ? ' has-error' : '' }}">
                                        <label class="control-label">County <sup style='color:red;'>*</sup> </label>
                                          
                                            <select class="form-control" onchange="getAllStates(this.value)" name="country" id="country">
                                             <option value="" selected="">--Select--</option>
                                                @foreach($countries as $country)
                                                    <option value="{{$country->id}}" @if($country->id==$user_country) selected @endif>{{$country->name}}</option>
                                                @endforeach

                                             </select>

                                        @if ($errors->has('country'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('country') }}</strong>
                                        </span>
                                        @endif
                                     </div>
                                     @else
                                     <input type='hidden' id='country' name='country' value='{{$user_country}}'>
                                   @endif    
                                   @if((Auth::user()->userInformation->user_type!='4' && Auth::user()->userInformation->user_type!='6') || $state_login=='32' )          
                                      <div class="form-group {{ $errors->has('state') ? ' has-error' : '' }}">
                                        <label control-label">Region <sup style='color:red;'>*</sup> </label>
                                           <select name="state" id="state" onchange="getAllCities(this.value)" class="form-control">
                                               <option value="">--Select--</option>
                                              @if($user_state=='32')
                                                <option value="32" @if($user_state=='32') selected @endif>--ALL--</option>
                                              @endif
                                              
                                               @foreach($states as $state)
                                                    <option value="{{$state->id}}" @if($state->id==$user_state) selected @endif>{{$state->name}}</option>
                                                @endforeach
                                            </select>
                                            @if ($errors->has('state'))
                                                    <span class="help-block">
                                                        <strong class="text-danger">{{ $errors->first('state') }}</strong>
                                                    </span>
                                            @endif
                                          
                                     </div>
                                   @else
                                     <input type='hidden' id='state' name='state' value='{{$user_state}}'>
                                   @endif
                                   @if((Auth::user()->userInformation->user_type!='4' && Auth::user()->userInformation->user_type!='6') || $city_login=='22' )          
                                   
                                      <div class="form-group {{ $errors->has('city') ? ' has-error' : '' }}">
                                        <label class="control-label">City <sup style='color:red;'>*</sup> </label>
                                            
                                            <select class="form-control" name="city" id="city">
                                             <option value=""  >--Select--</option>
                                             @if($user_city=='22')
                                                <option value="22" @if($user_city=='22') selected @endif>--ALL--</option>
                                              @endif
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
                                    @else
                                     <input type='hidden' id='city' name='city' value='{{$user_city}}'>
                                 @endif  
                                       <div class="form-group{{ $errors->has('user_mobile') ? ' has-error' : '' }}">
                                            <label class="control-label">Mobile No.</label>

                                            <input type="text" class="form-control" name="user_mobile" value="{{old('user_mobile',$user_info->userInformation->user_mobile)}}">
                                            @if ($errors->has('user_mobile'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('user_mobile') }}</strong>
                                            </span>
                                            @endif
                                      </div>
                                        
                                        <div class="form-group {{ $errors->has('type') ? ' has-error' : '' }}">
                                            <label class=" control-label"> Type <sup style='color:red;'>*</sup> </label>
                                             
                                                 <input type="radio" name="type" value="0"   id="gender_option"/> Personal
                                                 <input type="radio" name="type" value="1" id="company_option" /> Company

                                            @if ($errors->has('type'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('type') }}</strong>
                                            </span>
                                            @endif
                                             
                                         </div>  
                                        
                                        
                                        <div id="gender" class="form-group{{ $errors->has('gender') ? ' has-error' : '' }}">
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
                                            <div class="form-group {{ $errors->has('comp_name') ? ' has-error' : '' }}">
                                                <label class="control-label">Company Name:</label>
                                                
                                               <input type="text" class="form-control" id="comp_name" name="comp_name" value="{{old('comp_name',isset($company_info->name)?$company_info->name:'')}}">
                                                @if ($errors->has('comp_name'))
                                                         <span class="help-block">
                                                            <strong>{{ $errors->first('comp_name') }}</strong>
                                                         </span>
                                                  @endif
                                           
                                            </div>
                                            <div class="form-group {{ $errors->has('comp_reg_no') ? ' has-error' : '' }}">
                                                <label class=" control-label">Company Reg No:</label>
                                              
                                               <input type="text" class="form-control" id="comp_reg_no" name="comp_reg_no" value="{{old('comp_reg_no',isset($company_info->comp_reg_no)?$company_info->comp_reg_no:'')}}">
                                                @if ($errors->has('comp_reg_no'))
                                                         <span class="help-block">
                                                            <strong>{{ $errors->first('comp_reg_no') }}</strong>
                                                         </span>
                                                  @endif
                                            
                                            </div>
                                            <div class="form-group {{ $errors->has('description') ? ' has-error' : '' }}">
                                                <label class=" control-label">Company Description:</label>
                                                
                                                    <textarea class="form-control" id="description" name="description" >{{old('description',isset($company_info->description)?$company_info->description:'')}}</textarea>
                                                @if ($errors->has('description'))
                                                         <span class="help-block">
                                                            <strong>{{ $errors->first('description') }}</strong>
                                                         </span>
                                                  @endif
                                            
                                            </div>
                                      </div>
                                        
                                        
                                        
                                         <div class="form-group{{ $errors->has('user_status') ? ' has-error' : '' }}">
                                            <label class="control-label">Status<sup style='color:red;'>*</sup> </label>

                                            <select class="form-control" name="user_status" id="user_status">
                                                <option value="">--Select Status--</option>
                                                <option value="1" @if($user_info->userInformation->user_status==1) selected=selected @endif>Active</option>
                                                <option value="2" @if($user_info->userInformation->user_status==2) selected=selected @endif>Blocked</option>

                                            </select>
                                            @if ($errors->has('user_status'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('user_status') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                        <div class="form-group{{ $errors->has('about_me') ? ' has-error' : '' }}">
                                            <label class="control-label">About me</label>

                                            <textarea class="form-control" name="about_me">{{old('about_me',$user_info->userInformation->about_me)}}</textarea>

                                        </div>

                                        <div class="margiv-top-10">
                                            <input type="submit" class="btn green-haze" value="Save Changes">
                                            <a href="{{url('/admin/company-users')}}" class="btn default">
                                                Cancel 
                                            </a>
                                        </div>
                                    </form>
                                </div>
                                <div class="tab-pane @if($errors->has('email') || $errors->has('confirm_email'))  active @endif" id="tab_1_3">
                                    <form name="frm_agent_update_email"  id="frm_agent_update_email" role="form" method="POST" action="{{ url('/admin/update-agent-user-email/'.$user_info->id) }}">
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
                                            <a href="{{url('/admin/admin-users')}}" class="btn default">
                                                Cancel 
                                            </a>
                                        </div>
                                    </form>
                                </div>
                                <!-- CHANGE PASSWORD TAB -->
                                <div class="tab-pane @if($errors->has('current_password')|| $errors->has('new_password') || $errors->has('confirm_password') || session('password-update-fail')) active @endif" id="tab_1_2">
                                    <form name="frm_agent_update_password"  id="frm_agent_update_password" role="form" method="POST" action="{{ url('/admin/update-agent-user-password/'.$user_info->id) }}">
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
                                        <div class="form-group{{ $errors->has('confirm_password') ? ' has-error' : '' }}">
                                            <label class="control-label">Confirm Password</label>
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" value="{{old('confirm_password')}}">
                                            @if ($errors->has('confirm_password'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('confirm_password') }}</strong>
                                            </span>
                                            @endif

                                        </div>
                                        <div class="margiv-top-10">
                                            <input type="submit" class="btn green-haze" value="Change Password">
                                            <a href="{{url('/admin/agent-users')}}" class="btn default">
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
                   url:"{{url('/admin/cities/getAllCities')}}/"+country_id+"/"+state_id,
                   method:'get',
                   success:function(data)
                   {

                        $("#city").html(data);

                   }

                });
            }
        }
 </script>
            
@endsection
