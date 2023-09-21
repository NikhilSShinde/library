@extends(config("piplmodules.back-view-layout-login-location"))

@section("meta")
<title>Login to Admin panel</title>
@endsection

@section('content')
       <div class="page-lock">
	<div class="page-body">
		<div class="lock-head">
			  Support Chat Login Page
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
                    <form class="lock-form pull-left" id='admin_login' name='admin_login' role="form" method="POST" action="{{ url('/login') }}">
                        {!! csrf_field() !!}

                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                          
                                <input type="email" autocomplete="off" placeholder="Email" class="form-control placeholder-no-fix" name="email" value="{{ old('email') }}">

                                @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                          
                        </div>

                        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                           
                               <input type="password" autocomplete="off" placeholder="Password" class="form-control placeholder-no-fix" name="password">

                                @if ($errors->has('password'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
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

                        <div class="form-group text-center">
                             	
                                <button type="submit" class="btn btn-primary">
                                    Login
                                </button>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
