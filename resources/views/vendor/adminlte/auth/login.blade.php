@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@section('title', 'Log in - ' . env('APP_NAME') )

@php( $login_url = View::getSection('login_url') ?? config('adminlte.login_url', 'login') )
{{-- @php( $register_url = View::getSection('register_url') ?? config('adminlte.register_url', 'register') ) --}}
{{-- @php( $password_reset_url = View::getSection('password_reset_url') ?? config('adminlte.password_reset_url', 'password/reset') ) --}}
 @php( $register_url = null ) 
 @php( $password_reset_url = null ) 
 @php( $has_admin_error = ($errors->has('email') || $errors->has('password') ))

@if (config('adminlte.use_route_url', false))
    @php( $login_url = $login_url ? route($login_url) : '' )
    @php( $register_url = $register_url ? route($register_url) : '' )
    @php( $password_reset_url = $password_reset_url ? route($password_reset_url) : '' )
@else
    @php( $login_url = $login_url ? url($login_url) : '' )
    @php( $register_url = $register_url ? url($register_url) : '' )
    @php( $password_reset_url = $password_reset_url ? url($password_reset_url) : '' )
@endif

{{-- @section('auth_header', __('adminlte::adminlte.login_message')) --}}

@section('auth_body')

    @if ($message = Session::get('error-psft'))
        <div class="alert alert-danger alert-dismissible">
            <a href="#" class="close" style="text-decoration: none;"  data-dismiss="alert" aria-label="close">&times;</a>
            <strong>Login error!</strong> {{ $message }}
        </div>
    @endif

    <div id="idir-login" style="{{  $errors->has('email')  ? 'display:none;' : '' }}" >
        <div class="text-center py-3">
                <h1 class="font-weight-bold">Log in to start your session<h1>
                    <p class="my-4 ">
                        <form action="{{ '/login/keycloak' }}" method="get">
                            @csrf
                            <button type="submit" class="btn btn-primary">Login with Your BC Govt login ID </button>
                        </form>
                    </p>
        </div>
        <div class="py-2 border-top">
            <h2 class="pt-4 font-weight-bold">Need Help?</h2>
            <div class="">Contact your IDIR security administrator or the 7-7000 Service Desk at:</div>
            <div class="pt-2">Phone: <a href="tel:0612345678">250-387-7000</a></div>
            <div>Email: <a href="mailto:77000@gov.bc.ca" target="_blank" >77000@gov.bc.ca</a></div>

            <div class="py-4"><a class="sysadmin-login" href="">Log in as a System Administrator</a></div>
        </div>
    </div>    


    <div id="admin-login" style="{{  Session::get('error-psft') || $has_admin_error == false ? 'display:none;' : '' }}"">
        <div class="text-center py-3">
            <h4 class="font-weight-bold">Log in to start your session<h4>
        </div>

        <form action="{{ $login_url }}" method="post">
            {{ csrf_field() }}

            {{-- Email field --}}
            <label for="email" class="col-3 col-form-label">Email</label>
            <div class="input-group mb-3">
                <input type="text" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                    value="{{ old('email') }}" placeholder="{{ __('adminlte::adminlte.email') }}" autofocus>
                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-envelope {{ config('adminlte.classes_auth_icon', '') }}"></span>
                    </div>
                </div>
                @if($errors->has('email'))
                    <div class="invalid-feedback">
                        <strong>Error: {{ $errors->first('email') }}</strong>
                    </div>
                @endif
            </div>

            {{-- Password field --}}
            <label for="password" class="col-3 col-form-label">Password</label>
            <div class="input-group mb-3">
                <input type="password" name="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                    placeholder="{{ __('adminlte::adminlte.password') }}">
                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-lock {{ config('adminlte.classes_auth_icon', '') }}"></span>
                    </div>
                </div>
                @if($errors->has('password'))
                    <div class="invalid-feedback">
                        <strong>Error: {{ $errors->first('password') }}</strong>
                    </div>
                @endif
            </div>

            {{-- Login field --}}
            <div class="row pt-3">
                <div class="col-7">
                    <div class="icheck-primary">
                        <input type="checkbox" name="remember" id="remember">
                        <label for="remember">{{ __('adminlte::adminlte.remember_me') }}</label>
                    </div>
                </div>
                <div class="col-5">
                    <button type=submit class="btn btn-block {{ config('adminlte.classes_auth_btn', 'btn-flat btn-primary') }}">
                        <span class="fas fa-sign-in-alt"></span>
                        {{ __('adminlte::adminlte.sign_in') }}
                    </button>
                </div>
            </div>

        </form>

        <hr>
        <button type="button" class="btn btn-outline-secondary idir-login">Back</button>
        {{-- <span class=""><a class="btn btn-outline-info ">Back</a></span> --}}

    </div>    

@stop

@section('auth_footer')

    {{-- Login/logout link for Azure Active Directory --}}
    {{-- @if ($message = Session::get('error-psft'))
        <p class="my-4">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-warning">Logout from BC Govt</button>
            </form>
        </p>
    @else
        <p class="my-4">
            <form action="{{ '/login/microsoft' }}" method="get">
                @csrf
                <button type="submit" class="btn btn-success">Login with Your BC Govt login ID </button>
            </form>
        </p>
    @endif --}}


    {{-- Password reset link --}}
    @if($password_reset_url)
        <p class="my-0">
            <a href="{{ $password_reset_url }}">
                {{ __('adminlte::adminlte.i_forgot_my_password') }}
            </a>
        </p>
    @endif

    {{-- Register link --}}
    @if($register_url)
        <p class="my-0">
            <a href="{{ $register_url }}">
                {{ __('adminlte::adminlte.register_a_new_membership') }}
            </a>
        </p>
    @endif
@stop


@push('css')
<style>
    .login-box, .register-box {
        width: 600px;    
    }

    .login-box .card, .register-box .card {
        padding-left: 20px;
        padding-right: 20px;
    }

</style>

@endpush

@push('js')
<script>
    $(function() {
        console.log( "ready!" );

        $(document).on("click",".sysadmin-login",function(event) {
            event.preventDefault();
            $('#idir-login').hide();
            $('#admin-login').show();
        });

        $(document).on("click",".idir-login",function(event) {
            event.preventDefault();
            $('#idir-login').show();
            $('#admin-login').hide();   
        });

    });
    
    </script>
@endpush