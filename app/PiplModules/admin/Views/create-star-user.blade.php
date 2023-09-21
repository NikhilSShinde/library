@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Create Driver</title>

@endsection

@section('content')
<script type="text/javascript">
    $(document).ready(function(){
        
        @if (!($errors->has('comp_name') || $errors->has('comp_reg_no')) && old('type')==0)
        $("#company_div").hide(); 
        $("#gender").show(); 
        $("#gender_option").prop('checked',true); 
        $("#company_option").prop('checked',false); 
        
        @endif
        
        @if (!($errors->has('gender')) && old('type')==1)
        $("#gender").hide(); 
        $("#company_div").show(); 
        $("#gender_option").prop('checked',false); 
        $("#company_option").prop('checked',true); 
        @endif
        
      $(document).on('click','#company_option',function(){
//          $("#gender").hide();
          $("#company_div").show();
      }); 
      
      $(document).on('click','#gender_option',function(){
//          $("#gender").show();
          $("#company_div").hide();
      }); 
    });
</script>
<div class="page-content-wrapper">
		<div class="page-content">
                    <!-- BEGIN PAGE BREADCRUMB -->
			<ul class="page-breadcrumb breadcrumb">
				<li>
					<a href="{{url('admin/dashbard')}}">Dashboard</a>
				 	<i class="fa fa-circle"></i>
				</li>
				<li>
					<a href="{{url('admin/star-users')}}">Manage Drivers</a>
                                        <i class="fa fa-circle"></i>
					
				</li>
				<li>
					<a href="javascript:void(0)">Create New User</a>
					
				</li>
                        </ul>
    @if (session('create-role-status'))
          <div class="alert alert-success">
                {{ session('create-role-status') }}
          </div>
    @endif
    
        <!-- BEGIN SAMPLE FORM PORTLET-->
        <div class="portlet box blue">
            <div class="portlet-title">
                <div class="caption">
                        <i class="fa fa-gift"></i> Create Driver
                </div>

             </div>
             <div class="portlet-body form">
              <form role="form" name='add_star' id='add_star' class="form-horizontal"  method="post" >
                {!! csrf_field() !!}
                <div class="form-body">
                <div class="row">
                    <div class="col-md-12">    
                      <div class="col-md-8">     
                      <div class="form-group {{ $errors->has('first_name') ? ' has-error' : '' }}">
                          <label class="col-md-6 control-label">First Name:<sup>*</sup></label>
                       
                          <div class="col-md-6">     
                          <input name="first_name" type="text" class="form-control" id="first_name" value="{{old('first_name')}}">
                          @if ($errors->has('first_name'))
                            <span class="help-block">
                                <strong>{{ $errors->first('first_name') }}</strong>
                            </span>
                          @endif
                        </div>
                       
                  </div>
                   
                  <div class="form-group {{ $errors->has('last_name') ? ' has-error' : '' }}">
                        <label class="col-md-6 control-label">Last Name:<sup>*</sup></label>
                         <div class="col-md-6">         
                           <input type="text" class="form-control" id="last_name" name="last_name" value="{{old('last_name')}}">
                          
                           @if ($errors->has('last_name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('last_name') }}</strong>
                                    </span>
                           @endif
                        </div>
                  </div>
                  @if((Auth::user()->userInformation->user_type!='4' && Auth::user()->userInformation->user_type!='5' && Auth::user()->userInformation->user_type!='6') || $country==17)
                  
                     <div class="form-group {{ $errors->has('country') ? ' has-error' : '' }}">
                        <label class="col-md-6 control-label">County <sup style='color:red;'>*</sup> </label>
                         <div class="col-md-6">    
                            <select class="form-control" onchange="getAllStates(this.value)" name="country" id="country">
                             <option value="" selected="">--Select--</option>
                                @foreach($countries as $country)
                                    @if($country->id!='17')
                                    <option value="{{$country->id}}">{{$country->name}}</option>
                                   @endif 
                                @endforeach

                             </select>
                        
                        @if ($errors->has('country'))
                        <span class="help-block">
                            <strong>{{ $errors->first('country') }}</strong>
                        </span>
                        @endif
                         </div>
                     </div>
                   @else
                    <input type='hidden' name='country' id='country' value='{{$country}}'>
                 @endif
                  @if(Auth::user()->userInformation->user_type=='6')                
                     <div class="form-group {{ $errors->has('agent') ? ' has-error' : '' }}">
                        <label class="col-md-6 control-label">Agent <sup style='color:red;'>*</sup> </label>
                         <div class="col-md-6">    
                            <select class="form-control"  name="agent" id="agent">
                             <option value="" selected="">--Select--</option>
                                @foreach($agent_users as $user)
                                    <option value="{{$user->id}}">{{$user->userInformation->first_name}} {{$user->userInformation->last_name}}</option>
                                   
                                @endforeach

                             </select>
                        
                         </div>
                     </div>
                   @endif
                  @if((Auth::user()->userInformation->user_type!='4' && Auth::user()->userInformation->user_type!='5' && Auth::user()->userInformation->user_type!='6') || $state==32)
                      <div class="form-group {{ $errors->has('state') ? ' has-error' : '' }}">
                        <label class="col-md-6 control-label">State <sup style='color:red;'>*</sup> </label>
                         <div class="col-md-6">     
                            <select name="state" id="state" onchange="getAllCities(this.value)" class="form-control">
                                   <option value="" >--Select--</option>
                                    @foreach($states as $state)
                                        @if($state->id!='32')                                        
                                         <option value="{{$state->id}}">{{$state->name}}</option>
                                        @endif 
                                     @endforeach
                            </select>
                            @if ($errors->has('state'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('state') }}</strong>
                                    </span>
                            @endif
                          </div>
                     </div>
                    @else
                   <input name='state' type='hidden' id='state' value='{{$state}}'>
                  
                 @endif
                  @if((Auth::user()->userInformation->user_type!='4' && Auth::user()->userInformation->user_type!='5' && Auth::user()->userInformation->user_type!='6') || $city==22)
                  
                      <div class="form-group {{ $errors->has('city') ? ' has-error' : '' }}">
                        <label class="col-md-6 control-label">City <sup style='color:red;'>*</sup> </label>
                         <div class="col-md-6">    
                            <select class="form-control" name="city" id="city">
                               <option value="">--Select--</option>
                                @foreach($cities as $city)
                                   @if($city->id!='22')
                                     <option value="{{$city->id}}">--{{$city->name}}--</option>
                                   @endif  
                                @endforeach
                            
                             </select>
                        
                        @if ($errors->has('city'))
                        <span class="help-block">
                            <strong>{{ $errors->first('city') }}</strong>
                        </span>
                        @endif
                         </div>
                  </div> 
                  @else
                    <input type='hidden' name='city' id='city' value='{{$city}}'>
                  
                @endif 
                
                   <div class="form-group">
                        <label class="col-md-6 control-label">Email:</label>
                         <div class="col-md-6">      
                        <input type="email" class="form-control" id="email" name="email" value="{{old('email')}}">
                       
                  </div>
                  </div>
                   
                <div class="form-group {{ $errors->has('type') ? ' has-error' : '' }}">
                        <label class="col-md-6 control-label"> Type <sup style='color:red;'>*</sup> </label>
                         <div class="col-md-6">    
                             <input type="radio" name="type" value="0" id="gender_option"/> Driver
                             <input type="radio" name="type" value="1" id="company_option" /> Owner
                        
                        @if ($errors->has('type'))
                        <span class="help-block">
                            <strong>{{ $errors->first('type') }}</strong>
                        </span>
                        @endif
                         </div>
                     </div>      
                          
                          
                       <div class="form-group {{ $errors->has('gender') ? ' has-error' : '' }}" >
                        <label class="col-md-6 control-label">Gender <sup style='color:red;'>*</sup> </label>
                         <div class="col-md-6">    
                            <select class="form-control" name="gender" id="gender">
                             <option value=""  >--Select--</option>
                             <option value="1">Male</option>
                                <option value="2">Female</option>

                             </select>
                        
                        @if ($errors->has('gender'))
                        <span class="help-block">
                            <strong>{{ $errors->first('gender') }}</strong>
                        </span>
                        @endif
                         </div>
                     </div>
                     
                
                     <div class="form-group {{ $errors->has('mobile_code') ? ' has-error' : '' }}">
                        <label class="col-md-6 control-label">Country Code  <sup style='color:red;'>*</sup> </label>
                         <div class="col-md-6 country_code">    
                            <select class="form-control" name="mobile_code" id="mobile_code">
                             <option value="" selected="">--Select--</option>
                                @foreach($countries as $country)
                                    @if($country->id!='17')
                                     <option value="{{$country->country_code}}">{{$country->country_code}}</option>
                                    @endif 
                                @endforeach

                             </select>
                        
                        @if ($errors->has('mobile_code'))
                        <span class="help-block">
                            <strong>{{ $errors->first('mobile_code') }}</strong>
                        </span>
                        @endif
                         </div>
                     </div>
               
                  <div class="form-group {{ $errors->has('user_mobile') ? ' has-error' : '' }}">
                        <label class="col-md-6 control-label">Mobile: <sup style='color:red;'>*</sup> </label>
                        <div class="col-md-6">  
                       <input type="text" class="form-control" id="user_mobile" name="user_mobile" value="{{old('user_mobile')}}">
                        @if ($errors->has('user_mobile'))
                                 <span class="help-block">
                                    <strong>{{ $errors->first('user_mobile') }}</strong>
                                 </span>
                          @endif
                  </div>
                  </div>
                     <div id="company_div">  
                               
                          <div class="form-group {{ $errors->has('comp_reg_no') ? ' has-error' : '' }}">
                                    <label class="col-md-6 control-label">Owner Name:  <sup style='color:red;'>*</sup></label>
                                    <div class="col-md-6">  
                                   <input type="text" class="form-control" id="owner_name" name="owner_name" value="{{old('owner_name')}}">
                                    @if ($errors->has('owner_name'))
                                             <span class="help-block">
                                                <strong>{{ $errors->first('owner_name') }}</strong>
                                             </span>
                                      @endif
                                </div>
                            </div>
                           <div class="form-group {{ $errors->has('description') ? ' has-error' : '' }}">
                                    <label class="col-md-6 control-label">Owner Number: <sup style='color:red;'>*</sup></label>
                                    <div class="col-md-6">  
                                     <input type="text" class="form-control" id="owner_number" name="owner_number" value="{{old('owner_number')}}">
                                    @if ($errors->has('owner_number'))
                                             <span class="help-block">
                                                <strong>{{ $errors->first('owner_number') }}</strong>
                                             </span>
                                      @endif
                                </div>
                                </div>
                          </div>  
               
<!--                  <div class="form-group {{ $errors->has('user_mobile') ? ' has-error' : '' }}">
                        <label class="col-md-6 control-label">Mobile:<sup style='color:red;'>*</sup> </label>
                        <div class="col-md-6">  
                       <input type="text" class="form-control" id="user_mobile" name="user_mobile" value="{{old('user_mobile')}}">
                        @if ($errors->has('user_mobile'))
                                 <span class="help-block">
                                    <strong>{{ $errors->first('user_mobile') }}</strong>
                                 </span>
                          @endif
                  </div>
                  </div>-->
<!--                  <div class="form-group {{ $errors->has('locale') ? ' has-error' : '' }}">
                        <label class="col-md-6 control-label">User Language <sup style='color:red;'>*</sup> </label>
                         <div class="col-md-6">    
                            <select class="form-control" name="locale" id="locale">
                             <option value=""  >--Select--</option>
                             <option value="en">English</option>
                                <option value="ar">Arabic</option>

                             </select>
                        
                        @if ($errors->has('locale'))
                        <span class="help-block">
                            <strong>{{ $errors->first('locale') }}</strong>
                        </span>
                        @endif
                         </div>
                     </div>
                          -->
                   <div class="form-group">
                         <div class="col-md-12">   
                            <button type="submit" id="submit" class="btn btn-primary  pull-right">Create User</button>
                         </div>
                  </div>
             </div>
              </div>
            </div>
                
             </div>
    
            </form>
        </div>
    </div>
    </div>
    </div>
 <script>
            
        function getAllStates(country_id)
        {
            if(country_id!='' && country_id!=0)
            {
                $.ajax({
                   url:"{{url('/admin/states/getAllStatesRegistration')}}/"+country_id,
                   method:'get',
                   success:function(data)
                   {

                        $("#state").html(data);

                   }

                });
                $.ajax({
                   url:"{{url('/admin/country_info')}}/"+country_id,
                   method:'get',
                   success:function(data1)
                   {
                       $("div.country_code > select > option[value='" + data1.data.country_code + "']").attr("selected",true);

                      
                    
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
 </script>
            
 @endsection