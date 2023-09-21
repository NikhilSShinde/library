@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Create Admin user</title>

@endsection

@section('content')
<div class="page-content-wrapper">
		<div class="page-content">
                    <!-- BEGIN PAGE BREADCRUMB -->
			<ul class="page-breadcrumb breadcrumb">
				<li>
					<a href="{{url('admin/dashbard')}}">Dashboard</a>
					<i class="fa fa-circle"></i>
				</li>
				<li>
					<a href="{{url('admin/admin-users')}}">Manage Admin Users</a>
                                        <i class="fa fa-circle"></i>
					
				</li>
				<li>
					<a href="javascript:void(0);">Create New User</a>
					
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
                                <i class="fa fa-gift"></i> Create User
                        </div>

             </div>
             <div class="portlet-body form">
              <form role="form" id="frm_admin_add" name="frm_admin_add" class="form-horizontal"  method="post" >
                  <input type='hidden' name='user_type' id='user_type' value='1'>
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
                   @if(!(Auth::user()->userInformation->user_type=='1' && (!Auth::user()->hasRole('superadmin')))) 
                     <div class="form-group {{ $errors->has('country') ? ' has-error' : '' }}">
                        <label class="col-md-6 control-label">Country<sup style='color:red;'>*</sup> </label>
                         <div class="col-md-6">    
                            <select class="form-control" onchange="getAllStates(this.value)" name="country" id="country">
                             <option value="" selected="">--Select--</option>
                                @foreach($countries as $country)
                                   @if($country->id=='17')
                                     <option  @if (old("country") == $country->id) selected @endif value="{{$country->id}}">--{{$country->name}}--</option>
                                   @else
                                     <option @if (old("country") == $country->id) selected @endif value="{{$country->id}}">{{$country->name}}</option>
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
                  @endif 
                   <div class="form-group {{ $errors->has('email') ? ' has-error' : '' }}">
                        <label class="col-md-6 control-label">Email:<sup>*</sup></label>
                         <div class="col-md-6">      
                       <input type="email" class="form-control" id="email" name="email" value="{{old('email')}}">
                        @if ($errors->has('email'))
                                 <span class="help-block">
                                    <strong>{{ $errors->first('email') }}</strong>
                                 </span>
                          @endif
                  </div>
                  </div>
                    <div class="form-group {{ $errors->has('password') ? ' has-error' : '' }}">
                        <label class="col-md-6 control-label">Password:<sup>*</sup></label>
                        <div class="col-md-6">    
                         <input type="Password" class="form-control" id="password" name="password" value="{{old('password')}}">
                        @if ($errors->has('password'))
                                 <span class="help-block">
                                    <strong>{{ $errors->first('password') }}</strong>
                                 </span>
                          @endif
                  </div>
                  </div>
                    <div class="form-group {{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
                        <label class="col-md-6 control-label">Confirm Password:<sup>*</sup></label>
                        <div class="col-md-6">    
                         <input type="Password" class="form-control" id="password_confirmation" name="password_confirmation" value="{{old('password_confirmation')}}">
                            @if ($errors->has('password_confirmation'))
                                 <span class="help-block">
                                    <strong>{{ $errors->first('password_confirmation') }}</strong>
                                 </span>
                          @endif
                        </div>
                    </div>
                     <div class="form-group {{ $errors->has('gender') ? ' has-error' : '' }}">
                        <label class="col-md-6 control-label">Gender <sup style='color:red;'>*</sup> </label>
                         <div class="col-md-6">    
                            <select class="form-control" name="gender" id="gender">
                             <option value="" >--Select--</option>
                             <option value="1"  @if (old("gender") == "1") selected @endif>Male</option>
                                <option value="2"  @if (old("gender") == "2") selected @endif>Female</option>

                             </select>
                        
                        @if ($errors->has('gender'))
                        <span class="help-block">
                            <strong>{{ $errors->first('gender') }}</strong>
                        </span>
                        @endif
                         </div>
                     </div>
                  <div class="form-group {{ $errors->has('user_mobile') ? ' has-error' : '' }}">
                        <label class="col-md-6 control-label">Mobile:</label>
                        <div class="col-md-6">  
                       <input type="text" class="form-control" id="user_mobile" name="user_mobile" value="{{old('user_mobile')}}">
                        @if ($errors->has('user_mobile'))
                                 <span class="help-block">
                                    <strong>{{ $errors->first('user_mobile') }}</strong>
                                 </span>
                          @endif
                  </div>
                  </div>
                  <div class="form-group {{ $errors->has('role') ? ' has-error' : '' }}">
                        <label class="col-md-6 control-label">Role:</label>
                        <div class="col-md-6">  
                            <select name="role" class="form-control" id="role">
                                <option value="">--Select--</option>
                                @if(count($roles)>0)
                                    @foreach($roles as $role)
                                    @if($role->slug!='role.company' && $role->slug!='role.agent' && $role->slug!='role.agentmanager' && $role->slug!='role.free_toner')
                                        <option value="{{$role->slug}}">{{$role->name}}</option>
                                    @endif
                                    @endforeach
                                @endif
                            </select>   
                        <!--<input type="text" class="form-control" id="role" name="role" value="{{old('role')}}">-->
                        @if ($errors->has('role'))
                                 <span class="help-block">
                                    <strong>{{ $errors->first('role') }}</strong>
                                 </span>
                          @endif
                        </div>
                   </div>
                   
                          
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