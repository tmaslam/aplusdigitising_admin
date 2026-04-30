@extends('layouts.admin')

@section('title', 'Change Password | Digitizing Jobs Admin')
@section('page_heading', 'Change Password')
@section('page_subheading', 'Update your own admin login password.')

@section('content')
    @if ($errors->any())
        <div class="alert">{{ $errors->first() }}</div>
    @endif

    @if (session('success'))
        <div class="alert" style="background: rgba(21, 115, 71, 0.08); color: #1d6f46; border-color: rgba(21, 115, 71, 0.16);">{{ session('success') }}</div>
    @endif

    <section class="card">
        <div class="card-body">
            @if (! ($twoFAVerified ?? false))
                <form method="post" action="{{ url('/v/change-password-2fa') }}" class="toolbar">
                    @csrf
                    <div class="field">
                        <label>Admin User</label>
                        <input type="text" value="{{ $adminUser->user_name }}" readonly>
                    </div>
                    <div class="field">
                        <label for="code">2FA Verification Code <span style="color:var(--accent)">*</span></label>
                        <input id="code" type="text" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" placeholder="Enter the 6-digit code sent to your email" required>
                    </div>
                    <div class="field" style="min-width:auto;">
                        <label>&nbsp;</label>
                        <button type="submit">Verify Code</button>
                    </div>
                </form>

                <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:18px;">
                    <form method="post" action="{{ url('/v/change-password-2fa-resend') }}">
                        @csrf
                        <button type="submit" class="badge" style="border:0;background:linear-gradient(135deg,var(--accent),var(--accent-dark));color:#fff;font-weight:800;">Resend Code</button>
                    </form>
                </div>
            @else
                <form method="post" action="{{ url('/v/change-password.php') }}" class="toolbar">
                    @csrf
                    <div class="field">
                        <label>Admin User</label>
                        <input type="text" value="{{ $adminUser->user_name }}" readonly>
                    </div>
                    <div class="field">
                        <label for="txtPassword">New Password <span style="color:var(--accent)">*</span></label>
                        <input id="txtPassword" type="password" name="txtPassword" autocomplete="new-password" required>
                    </div>
                    <div class="field">
                        <label for="txtCPassword">Confirm Password <span style="color:var(--accent)">*</span></label>
                        <input id="txtCPassword" type="password" name="txtCPassword" autocomplete="new-password" required>
                    </div>
                    <div class="field" style="min-width:auto;">
                        <label>&nbsp;</label>
                        <button type="submit">Save Password</button>
                    </div>
                </form>
            @endif
        </div>
    </section>
@endsection
