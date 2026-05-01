@extends('layouts.customer-guest')

@section('title', $siteContext->displayLabel() . ' Login')

@section('footer')
@endsection

@section('content')
    <div class="container guest-shell">
        <section class="panel form-panel auth-panel" style="max-width: 640px; margin: 0 auto;">
            <h2>Sign In</h2>
            <p class="muted">Use your email or username to access your orders, quotes, billing, and downloads.</p>

            @if (session('success'))
                <div class="alert success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert">{{ $errors->first() }}</div>
            @endif

            <form method="post" action="/login" data-validate-form novalidate>
                @csrf
                <label class="form-field" data-form-field>
                    <span class="field-label">Email or User Name <span class="field-meta required" aria-hidden="true">*</span></span>
                    <input type="text" name="user_id" value="{{ old('user_id') }}" autocomplete="username" required>
                    <span class="field-help">You can use either the account email or the customer username tied to your account.</span>
                    <span class="field-error" data-field-error aria-live="polite"></span>
                </label>

                <label class="form-field" data-form-field>
                    <span class="field-label">Password <span class="field-meta required" aria-hidden="true">*</span></span>
                    <input type="password" name="user_psw" autocomplete="current-password" required>
                    <span class="field-error" data-field-error aria-live="polite"></span>
                </label>

                <label class="form-field" data-form-field style="display:flex; align-items:center; gap:10px; font-weight:400;">
                    <input type="checkbox" name="remember_me" value="1" @checked(old('remember_me')) style="width:auto; min-height:auto;">
                    <span class="field-label" style="font-weight:400;">Remember me on this device</span>
                </label>

                @include('shared.turnstile')

                <div class="actions">
                    <button type="submit">Sign In</button>
                    <a class="button secondary" href="/sign-up.php">Create Account</a>
                </div>
            </form>

            <p class="muted" style="margin-top:16px;">
                <a href="/forget-password.php">Forgot your password?</a><br>
                <a href="/resend-verification.php">Need a new verification email?</a><br>
                Need help? <a href="/contact-us.php">Contact Us</a>.
            </p>
        </section>
    </div>
@endsection
