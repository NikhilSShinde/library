@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Create Role</title>

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
					<a href="{{url('admin/manage-roles')}}">Manage Roles</a>
                                        <i class="fa fa-circle"></i>
					
				</li>
				<li>
					<a href="#">Create A Role</a>
					
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
                                <i class="fa fa-gift"></i> Create Role
                        </div>

             </div>
             <div class="portlet-body form">
              <form role="form" class="form-horizontal" action="{{url('/admin/roles/create')}}" method="post" >
               
                {!! csrf_field() !!}
                <div class="form-body">
                <div class="row">
                    <div class="col-md-12">    
                      <div class="col-md-8">     
                      <div class="form-group">
                          <label class="col-md-6 control-label">Name<sup>*</sup></label>
                       
                          <div class="col-md-6">     
                         <input name="name" type="text" class="form-control" id="name" value="{{old('name')}}">
                          @if ($errors->has('name'))
                            <span class="help-block">
                                <strong class="text-danger">{{ $errors->first('name') }}</strong>
                            </span>
                            @endif
                        </div>
                       
                  </div>
                   
                  <div class="form-group">
                        <label class="col-md-6 control-label">Slug<sup>*</sup></label>
                         <div class="col-md-6">         
                           <input type="text" class="form-control" id="slug" name="slug" value="{{old('slug')}}">
                           @if ($errors->has('slug'))
                            <span class="help-block">
                                <strong class="text-danger">{{ $errors->first('slug') }}</strong>
                            </span>
                            @endif
                        </div>
                  </div>
                   <div class="form-group">
                        <label class="col-md-6 control-label">Description</label>
                       
                         <div class="col-md-6">     
                            <textarea class="form-control" id="description" name="description">{{old('description')}}</textarea>
             
                        </div>
                  </div>
                
                  <div class="form-group">
                        <label class="col-md-6 control-label">Level</label>
                        <div class="col-md-6">         
                                  <select class="form-control" id="level" name="level">
                                    @for($i=1;$i<=10;$i++)
                                    <option value="{{$i}}" @if(old('level')==$i) selected @endif>Level {{$i}}</option>
                                    @endfor

                                 </select>
                         
                        </div>
                  </div>
                   <div class="form-group">
                         <div class="col-md-12">   
                            <button type="submit" id="submit" class="btn btn-primary  pull-right">Create Role</button>
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
        <style>
            .submit-btn{
                padding: 10px 0px 0px 18px;
            }
        </style>
 @endsection