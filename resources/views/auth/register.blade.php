@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Register') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('register') }}" id="registerForm">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('Name') }}</label>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" 
                                name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
                            @error('name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">{{ __('Email Address') }}</label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                                name="email" value="{{ old('email') }}" required autocomplete="email">
                            @error('email')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">{{ __('Password') }}</label>
                            <div class="input-group">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" 
                                    name="password" required autocomplete="new-password">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('password', 'toggleIcon')">
                                    <i id="toggleIcon" class="bi bi-eye-slash"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password-confirm" class="form-label">{{ __('Confirm Password') }}</label>
                            <div class="input-group">
                                <input id="password-confirm" type="password" class="form-control" 
                                    name="password_confirmation" required autocomplete="new-password">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('password-confirm', 'toggleIconConfirm')">
                                    <i id="toggleIconConfirm" class="bi bi-eye-slash"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_admin" id="is_admin">
                                <label class="form-check-label" for="is_admin">
                                    {{ __('Register as Admin') }}
                                </label>
                            </div>
                        </div>

                        <div class="mb-3 admin-key-group" style="display: none;">
                            <label for="admin_key" class="form-label">{{ __('Admin Key') }}</label>
                            <input id="admin_key" type="password" class="form-control" name="admin_key">
                        </div>

                        <div class="mb-0">
                            <button type="submit" class="btn btn-primary">
                                {{ __('Register') }}
                            </button>

                            <a class="btn btn-link" href="{{ route('login') }}">
                                {{ __('Already have an account? Login') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
<style>
    .card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .btn-success {
        background-color: #28a745;
    }
    .btn-success:hover {
        background-color: #218838;
    }
    .input-group .btn-outline-secondary {
        border-color: #ced4da;
    }
    .input-group .btn-outline-secondary:hover {
        background-color: #f8f9fa;
    }
    .invalid-feedback {
        display: block;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add CSRF token to all AJAX requests
    let token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    document.querySelectorAll('form').forEach(form => {
        if (!form.querySelector('input[name="_token"]')) {
            let csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = token;
            form.appendChild(csrfInput);
        }
    });

    // Password visibility toggle function
    window.togglePasswordVisibility = function(fieldId, iconId) {
        const passwordInput = document.getElementById(fieldId);
        const toggleIcon = document.getElementById(iconId);
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('bi-eye-slash');
            toggleIcon.classList.add('bi-eye');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('bi-eye');
            toggleIcon.classList.add('bi-eye-slash');
        }
    }

    // Admin checkbox and key field handling
    const adminCheckbox = document.getElementById('is_admin');
    const adminKeyGroup = document.querySelector('.admin-key-group');
    const adminKeyInput = document.getElementById('admin_key');

    if (adminCheckbox) {
        adminCheckbox.addEventListener('change', function() {
            adminKeyGroup.style.display = this.checked ? 'block' : 'none';
            if (!this.checked) {
                adminKeyInput.value = '';
            }
        });
    }

    // Form validation
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        if (adminCheckbox.checked && !adminKeyInput.value) {
            e.preventDefault();
            alert('Please enter the Admin Key');
            adminKeyInput.focus();
        }
    });
});
</script>
@endpush 