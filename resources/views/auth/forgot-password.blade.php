@extends('layouts.app')

@section('content')
<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card shadow-lg p-4" style="max-width: 420px; width: 100%; border-radius: 1rem;">
        <div class="text-center mb-4">
            <img src="{{ asset('images/asset.png') }}" alt="App Logo" width="80" class="mb-3">
            <h3 class="fw-bold text-primary">Forgot Your Password?</h3>
            <p class="text-muted mb-0">
                Enter your email address below and we’ll send you a password reset link.
            </p>
        </div>

        <!-- Status Message -->
        @if (session('status'))
            <div class="alert alert-success mt-3">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="mt-3" novalidate>
            @csrf

            <!-- Email Field -->
            <div class="mb-3">
                <label for="email" class="form-label fw-semibold">Email Address</label>
                <input id="email" type="email"
                       class="form-control @error('email') is-invalid @enderror"
                       name="email" value="{{ old('email') }}" required autofocus>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Submit Button -->
            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-primary fw-semibold">
                    <i class="bi bi-envelope me-2"></i> Send Password Reset Link
                </button>
            </div>

            <div class="text-center small">
                <a href="{{ route('login') }}" class="text-decoration-none text-secondary">
                    <i class="bi bi-arrow-left-circle me-1"></i> Back to Login
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
