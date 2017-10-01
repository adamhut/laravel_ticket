@extends('layouts.master')

@section('body')

<div class="container-fluid bg-soft">
    <div class="full-height flex-center">
        <div class="constrain constrain-sm flex-fit">
            <form class="card p-xs-6" action="/login" method="POST">
                {{ csrf_field() }}
                <h1 class="text-xl wt-light text-center m-xs-b-6">Reset Your Password</h1>
                <div class="form-group">
                    <label class="form-label pseudo-hidden">Current Password</label>
                    <div class="input-group">
                        <span class="input-group-addon">
                            @icon('user', 'text-dark-muted text-xs')
                        </span>
                        <input type="password" name="current_password" class="form-control" placeholder="Email address" value="{{ old('current_password') }}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label pseudo-hidden">Password</label>
                    <div class="input-group">
                        <span class="input-group-addon">
                            @icon('lock-closed', 'text-dark-muted text-xs')
                        </span>
                        <input type="password" name="password" class="form-control" placeholder="Password">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label pseudo-hidden">Confirm Password</label>
                    <div class="input-group">
                        <span class="input-group-addon">
                            @icon('lock-closed', 'text-dark-muted text-xs')
                        </span>
                        <input type="password" name="password_confirmation" class="form-control" placeholder="Password">
                    </div>
                </div>
                <button type="submit" class="btn btn-block btn-primary">Reset Password</button>
                @if($errors->any())
                    <p class="text-center text-danger m-xs-t-2">
                        These credentials or the password does not matched
                    </p>
                @endif

            </form>
        </div>
    </div>
</div>
{{ svg_spritesheet() }}
@endsection
