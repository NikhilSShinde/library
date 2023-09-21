@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Update Role Permissions</title>

@endsection

@section('content')
<div class="page-content-wrapper">
		<div class="page-content">
                    <!-- BEGIN PAGE BREADCRUMB -->
			<ul class="page-breadcrumb breadcrumb">
				<li>
					<a href="{{url('admin/dashboard')}}">Dashboard</a>
					<i class="fa fa-circle"></i>
				</li>
				<li>
					<a href="{{url('admin/manage-roles')}}">Manage Roles</a>
                                        <i class="fa fa-circle"></i>
					
				</li>
				<li>
					<a href="javascript:void(0);">Manage Permissions for <span style="text-decoration:underline;">{{$role->name}} Role</span></a>
					
				</li>
                        </ul>
                       
                          <!-- BEGIN SAMPLE FORM PORTLET-->
            <div class="portlet box blue">
             <div class="portlet-title">
                        <div class="caption">
                                <i class="fa fa-gift"></i> Update Role Permissions
                        </div>

             </div>
             <div class="portlet-body form">
                <form method="post" role="form" class="form-inline">
                {!! csrf_field() !!}
                    <div class="panel panel-default">

                @foreach($permissions as $key=>$permission)

                @if(!isset($curr_model) || $curr_model <> $permission->model) 

                @if(isset($curr_model)) </div> @endif

                <div class="panel-heading">
                     {{$curr_model=$permission->model}} 
                </div>
    
                <div class="panel-body">

                @endif
                <div class="form-group">
                 <input type="checkbox" name="permission[]" value="{{$permission->id}}" @if($role_permissions->contains($permission->id)) checked @endif id="{{$permission->slug}}" /> <label for="{{$permission->slug}}">{{$permission->name}}</label>
                    </div>

                @endforeach
                    </div>
	
        <div class="form-group">
                         <div class="col-md-12">   
                            <button type="submit" id="submit" class="btn btn-primary  pull-right">Update Role</button>
                         </div>
          </div>
      
    </form>
                 </div>
</div>
</div>
</div>

@endsection