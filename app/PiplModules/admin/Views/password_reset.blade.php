@extends(config("piplmodules.back-view-layout-login-location"))

@section("meta")
<title>Reset your password/title>
@endsection

@section('content')
       <div class="page-lock">
	<div class="page-body">
		<div class="lock-head">
			  Admin Login Page
		</div>
		
               @if (session('login-error'))
               <div class="alert alert-danger">
                {{ session('login-error') }}
            	</div>
                @endif
                 @if (session('register-success'))
               <div class="alert alert-success">
                {{ session('register-success') }}
            	</div>
                @endif
               <div class="lock-body">
                    <form class="form-horizontal" role="form" method="POST" action="{{ url('/password/email') }}">
                        {!! csrf_field() !!}

                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                          
                               <input type="email" class="form-control" name="email" value="{{ old('email') }}">

                                @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                          
                        </div>

                        <div class="form-group">
                           
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="remember"> Remember Me
                                    </label>
                                </div>
                          
                        </div>

                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-btn fa-envelope"></i>Send Password Reset Link
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
