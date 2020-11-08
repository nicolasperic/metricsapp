@extends('layouts.login')

@section('content')
<div class="text-center">
    <h1 class="h4 text-gray-900 mb-4">Welcome Back!</h1>
</div>
<form class="user" method="POST" action="{{ route('login') }}">
    @csrf
    <div class="form-group">
        <input id="email" type="email" class="form-control form-control-user @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" aria-describedby="emailHelp" placeholder="Enter Email Address..." autofocus >

        @error('email')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>
    <div class="form-group">
        <input id="password" type="password" class="form-control form-control-user @error('password') is-invalid @enderror" name="password" placeholder="Password" required autocomplete="current-password">

        @error('password')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>
    <div class="form-group">
        <div class="custom-control custom-checkbox small">
            <input class="form-check-input custom-control-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

            <label class="form-check-label" for="remember">

            </label>
            <label class="custom-control-label" for="customCheck">Remember Me</label>
        </div>
    </div>

    <button type="submit" class="btn btn-primary  btn-user btn-block">
        {{ __('Login') }}
    </button>
</form>
<hr>
@if (Route::has('password.request'))
    <div class="text-center">
        <a class="small" href="{{ route('password.request') }}">
            {{ __('Forgot Your Password?') }}
        </a>
    </div>
@endif
@if (Route::has('register'))
    <div class="text-center">
        <a class="small" href="{{ route('register') }}">Create an Account!</a>
    </div>
@endif

@endsection
