@extends('layouts.app')

@section('content')
<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card shadow-lg p-4" style="max-width: 420px; width: 100%; border-radius: 1rem;">
        <div class="text-center mb-4">
            <img src="{{ asset('images/asset.png') }}" alt="App Logo" width="80" class="mb-3">
            <h3 class="fw-bold text-primary">Welcome Back</h3>
            <p class="text-muted mb-0">Login to continue to <strong>{{ config('app.name') }}</strong></p>
        </div>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}" novalidate>
            @csrf

            <!-- Email -->
            <div class="mb-3">
                <label for="email" class="form-label fw-semibold">Email Address</label>
                <input id="email" type="email"
                       class="form-control @error('email') is-invalid @enderror"
                       name="email" value="{{ old('email') }}" required autofocus>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Password -->
            <div class="mb-3">
                <label for="password" class="form-label fw-semibold">Password</label>
                <input id="password" type="password"
                       class="form-control @error('password') is-invalid @enderror"
                       name="password" required>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label" for="remember">Remember Me</label>
            </div>

            <!-- Submit -->
            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-primary fw-semibold">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Login
                </button>
            </div>

            <div class="text-center small">
                <!-- <a href="{{ route('password.request') }}" class="text-decoration-none text-secondary">
                    Forgot your password?
                </a> -->
                <br>
                <span class="text-muted">Don't have an account?</span>
                <a href="{{ route('register') }}" class="fw-semibold text-primary text-decoration-none">Register</a>
            </div>
        </form>
    </div>
</div>
@endsection
