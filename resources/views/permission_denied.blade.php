@extends('layouts.permission_app')

 @section('meta')
    <title>Permission Denied Page</title>
 @endsection
 
@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">Permission Denied</div>

                <div class="panel-body">
                    Sorry for this, You seeing this page because the action you want to perform require a permission for same. Please contact with site administrator.<br><br>
					@if(Auth::User()->userInformation->user_type=='1')	
					   <a href="{{ url('/admin/dashboard') }}" class="btn btn-primary">Back</a><br>
				   </a>
				   @endif
				   @if(Auth::User()->userInformation->user_type!='1')	
					   <a href="{{ url('/') }}" class="btn btn-primary">Back</a><br>
				   </a>
				   @endif
					<img src="{{url('public/media/front/images/denied.gif')}}" title="Permission Denied">
                </div>
				
            </div>
        </div>
    </div>
</div>
@endsection
