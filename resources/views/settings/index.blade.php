@extends('layouts.app')

@section('nav_item','Settings')


@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow mb-8">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Settings</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{url('/settings')}}">
                        @csrf
                        <div class="form-group">
                            <label for="email">Email address</label>
                            <input type="email" class="input form-control @error('email') is-danger @enderror" id="email" name="email" aria-describedby="emailHelp" placeholder="Enter email" value="{{ old('email', Auth::user()->email) }}">
                            <small id="emailHelp" class="form-text text-muted">We'll never share your email with anyone else.</small>
                            @error('email')
                            <p class="help is-danger">{{ $errors->first('email') }}</p>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="assembla_key">Assembla Key</label>
                            <input type="text" class="input form-control @error('assembla_key') is-danger @enderror" id="assembla_key" name="assembla_key" placeholder="Assembla Key" value="{{ old('assembla_key',Auth::user()->assembla_key) }}">
                            <small id="emailHelp" class="form-text text-muted">Key and Secret can be found in <a href="https://app.assembla.com/user/edit/manage_clients" target="_blank">Assembla</a></small>
                            @error('assembla_key')
                            <p class="help is-danger">{{ $errors->first('assembla_key') }}</p>

                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="assembla_secret">Assembla Secret</label>
                            <input type="password" class="input form-control @error('assembla_secret') is-danger @enderror" id="assembla_secret" name="assembla_secret" placeholder="Assembla Secret" value="{{ old('assembla_secret',Auth::user()->assembla_secret) }}">
                            @error('assembla_secret')
                            <p class="help is-danger">{{ $errors->first('assembla_secret') }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">Save</button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection





