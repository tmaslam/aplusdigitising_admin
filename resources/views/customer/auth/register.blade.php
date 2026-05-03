@extends('layouts.customer-guest')

@section('title', $siteContext->displayLabel() . ' Sign Up')

@section('footer')
@endsection

@section('content')
    <div class="container guest-shell">
        <section class="panel form-panel auth-panel">
            <h2>Member Sign Up</h2>
            <p class="muted">Complete the signup form below to create your customer account.</p>

            @if ($errors->any())
                <div class="alert">{{ $errors->first() }}</div>
            @endif

            <form method="post" action="/sign-up.php" data-validate-form novalidate>
                @csrf
                <section class="form-section">
                    <div class="section-heading">
                        <h3>Your Details</h3>
                        <p>Start with the essentials so we can create and verify your account.</p>
                    </div>

                    <div class="grid">
                        <label class="form-field" data-form-field>
                            <span class="field-label">First Name <span class="field-meta required" aria-hidden="true">*</span></span>
                            <input type="text" name="first_name" value="{{ old('first_name') }}" autocomplete="given-name" required>
                            <span class="field-error" data-field-error aria-live="polite"></span>
                        </label>
                        <label class="form-field" data-form-field>
                            <span class="field-label">Last Name <span class="field-meta required" aria-hidden="true">*</span></span>
                            <input type="text" name="last_name" value="{{ old('last_name') }}" autocomplete="family-name" required>
                            <span class="field-error" data-field-error aria-live="polite"></span>
                        </label>
                        <label class="form-field" data-form-field style="grid-column: 1 / -1">
                            <span class="field-label">Country <span class="field-meta required" aria-hidden="true">*</span></span>
                            <input
                                type="search"
                                name="selCountry"
                                value="{{ old('selCountry', 'United States') }}"
                                placeholder="Start typing or choose from the full list"
                                autocomplete="country-name"
                                required
                                data-country-input
                                data-country-strict
                                data-country-options='@json($countries)'
                            >
                            <div class="country-results" data-country-results aria-label="Matching countries"></div>
                            <span class="field-help">Click the field to view the full country list, or start typing to narrow it down.</span>
                            <span class="field-error" data-field-error aria-live="polite"></span>
                        </label>
                        <label class="form-field" data-form-field>
                            <span class="field-label">Email Address <span class="field-meta required" aria-hidden="true">*</span></span>
                            <input type="email" name="useremail" value="{{ old('useremail') }}" autocomplete="email" required>
                            <span class="field-error" data-field-error aria-live="polite"></span>
                        </label>
                        <label class="form-field" data-form-field>
                            <span class="field-label">Confirm Email Address <span class="field-meta required" aria-hidden="true">*</span></span>
                            <input type="email" name="confirmuseremail" value="{{ old('confirmuseremail') }}" autocomplete="off" required data-match="useremail" data-match-message="The confirm email address must match the email address.">
                            <span class="field-error" data-field-error aria-live="polite"></span>
                        </label>
                        <label class="form-field" data-form-field>
                            <span class="field-label">Password <span class="field-meta required" aria-hidden="true">*</span></span>
                            <input type="password" name="user_psw" minlength="6" autocomplete="new-password" required>
                            <span class="field-help">Use at least 6 characters.</span>
                            <span class="field-error" data-field-error aria-live="polite"></span>
                        </label>
                        <label class="form-field" data-form-field>
                            <span class="field-label">Confirm Password <span class="field-meta required" aria-hidden="true">*</span></span>
                            <input type="password" name="confirm_psw" minlength="6" autocomplete="new-password" required data-match="user_psw" data-match-message="The confirm password must match the password.">
                            <span class="field-help" aria-hidden="true">&nbsp;</span>
                            <span class="field-error" data-field-error aria-live="polite"></span>
                        </label>
                        <label class="form-field" data-form-field>
                            <span class="field-label">Telephone <span class="field-meta required" aria-hidden="true">*</span></span>
                            <input type="text" name="telephone_num" value="{{ old('telephone_num') }}" autocomplete="tel" inputmode="tel" required>
                            <span class="field-error" data-field-error aria-live="polite"></span>
                        </label>
                        <input type="hidden" name="package_type" value="BASIC">
                    </div>
                </section>

                <section class="form-section">
                    <div class="section-heading">
                        <h3>Business Details</h3>
                        <p>Add your company information if you want it on your customer profile.</p>
                    </div>

                    <div class="grid">
                        <label class="form-field" data-form-field>
                            <span class="field-label">Company Name</span>
                            <input type="text" name="company_name" value="{{ old('company_name') }}" autocomplete="organization">
                            <span class="field-error" data-field-error aria-live="polite"></span>
                        </label>
                        <label class="form-field" data-form-field>
                            <span class="field-label">Company Type</span>
                            <select name="selCompanyTypes">
                                <option value="">Company Type</option>
                                @foreach ($companyTypes as $type)
                                    <option value="{{ $type }}" @selected(old('selCompanyTypes') === $type)>{{ $type }}</option>
                                @endforeach
                            </select>
                            <span class="field-error" data-field-error aria-live="polite"></span>
                        </label>
                    </div>

                    <label class="form-field" data-form-field>
                        <span class="field-label">Company Address</span>
                        <textarea name="company_address" autocomplete="street-address">{{ old('company_address') }}</textarea>
                        <span class="field-error" data-field-error aria-live="polite"></span>
                    </label>
                </section>

                <section class="form-section">
                    <div class="section-heading">
                        <h3>How you want to start</h3>
                        <p>Choose the option that works best for your business.</p>
                    </div>

                    <div class="form-field" data-form-field>
                        <div class="promo-banner" style="padding:12px 16px; margin-bottom:14px; background:linear-gradient(135deg, #E8F5E9, #C8E6C9); border:1.5px solid rgba(45,123,83,0.25); border-radius:12px; color:#1d5639; font-weight:600; font-size:0.95rem; display:flex; align-items:center; justify-content:space-between; gap:12px;">
                            <span>🎁 Sign-Up Offer: Get <strong>33% OFF</strong> your first subscription or <strong>20% OFF</strong> your first credit purchase.</span>
                            <span style="display:inline-block; padding:6px 14px; background:rgba(45,123,83,0.12); border:2px dashed #2d7b53; border-radius:8px; font-family:monospace; font-weight:700; font-size:1rem; letter-spacing:0.08em;">WELAPLUS1</span>
                        </div>
                        <div class="field-label">Start Option <span class="field-meta required" aria-hidden="true">*</span></div>
                        <div class="radio-group">
                            <div class="radio-option">
                                <input type="radio" name="start_type" value="subscription" @checked(old('start_type', 'subscription') === 'subscription') required data-group-error="Please select a start option.">
                                <div><strong>Buy subscription</strong></div>
                            </div>
                            <div class="radio-option">
                                <input type="radio" name="start_type" value="credit" @checked(old('start_type') === 'credit') required data-group-error="Please select a start option.">
                                <div><strong>Buy credit</strong></div>
                            </div>
                        </div>
                        <span class="field-error" data-field-error aria-live="polite"></span>
                    </div>

                    <div class="form-field" data-form-field id="subscription-options">
                        <span class="field-label">Select Plan <span class="field-meta required" aria-hidden="true">*</span></span>
                        <select name="plan_option" id="plan_option_select" data-start-required="subscription">
                            <option value="">Select One</option>
                            <option value="starter" data-link="https://buy.stripe.com/7sYeVee2k9g05q86Tl6Ri03?prefilled_promo_code=WELAPLUS1" @selected(old('plan_option') === 'starter')>Starter (10 designs/month), $120 ➔ $79.99 (Save 33%)</option>
                            <option value="plus" data-link="https://buy.stripe.com/aFafZicYgfEo5q8gtV6Ri04?prefilled_promo_code=WELAPLUS1" @selected(old('plan_option') === 'plus')>Plus (25 designs/month), $300 ➔ $199.99 (Save 33%)</option>
                            <option value="pro" data-link="https://buy.stripe.com/00w4gA5vO8bW19Sb9B6Ri05?prefilled_promo_code=WELAPLUS1" @selected(old('plan_option') === 'pro')>Pro (50 designs/month), $600 ➔ $399.99 (Save 33%)</option>
                            <option value="enterprise" data-link="https://buy.stripe.com/7sYaEY3nGfEo6uc3H96Ri06?prefilled_promo_code=WELAPLUS1" @selected(old('plan_option') === 'enterprise')>Enterprise (100 designs/month), $1200 ➔ $799.99 (Save 33%)</option>
                        </select>
                        <span class="field-error" data-field-error aria-live="polite"></span>
                    </div>

                    <div class="form-field" data-form-field id="credit-options" style="display:none">
                        <span class="field-label">Select Credit <span class="field-meta required" aria-hidden="true">*</span></span>
                        <select name="plan_option" id="credit_option_select" data-start-required="credit">
                            <option value="">Select One</option>
                            <option value="10" data-link="https://buy.stripe.com/cNi7sMbUc77S4m4b9B6Ri0c?prefilled_promo_code=WELAPLUS1" @selected(old('plan_option') === '10')>$12 - Promo Price: $10</option>
                            <option value="25" data-link="https://buy.stripe.com/14A5kE3nGdwg9Gob9B6Ri0b?prefilled_promo_code=WELAPLUS1" @selected(old('plan_option') === '25')>$30 - Promo Price: $24</option>
                            <option value="50" data-link="https://buy.stripe.com/bJe7sM5vOeAkaKsb9B6Ri0a?prefilled_promo_code=WELAPLUS1" @selected(old('plan_option') === '50')>$50 - Promo Price: $40</option>
                            <option value="100" data-link="https://buy.stripe.com/3cIaEY1fy77SdWEelN6Ri09?prefilled_promo_code=WELAPLUS1" @selected(old('plan_option') === '100')>$100 - Promo Price: $80</option>
                            <option value="300" data-link="https://buy.stripe.com/00w8wQ3nG1Ny8CkelN6Ri08?prefilled_promo_code=WELAPLUS1" @selected(old('plan_option') === '300')>$300 - Promo Price: $260</option>
                            <option value="500" data-link="https://buy.stripe.com/9B614obUccsc9Go6Tl6Ri07?prefilled_promo_code=WELAPLUS1" @selected(old('plan_option') === '500')>$500 - Promo Price: $400</option>
                            <option value="1000" data-link="https://buy.stripe.com/test_7sYaEY3nGfEo6uc3H96Ri06?prefilled_promo_code=APDOC" @selected(old('plan_option') === '1000')>$1000 - Promo Price: $800</option>
                        </select>
                        <span class="field-error" data-field-error aria-live="polite"></span>
                    </div>
                </section>

                <section class="form-section">
                    <label class="terms-row" data-form-field>
                        <input type="checkbox" name="terms" value="1" @checked(old('terms')) required>
                        <span class="terms-copy">
                            <span class="terms-line"><span class="field-meta required" aria-hidden="true">*</span><span>I have read the <a href="https://aplusdigitizing.com/terms" target="_blank" rel="noopener">Terms &amp; Conditions</a> thoroughly, and I agree.</span></span>
                            <span class="field-error" data-field-error aria-live="polite"></span>
                        </span>
                    </label>
                </section>

                @include('shared.turnstile')

                <div class="actions">
                    <button type="submit" id="register-btn">Register</button>
                    <a class="button secondary" href="/login.php">Already Have An Account?</a>
                </div>
            </form>

            <script>
                (function () {
                    const radios = document.querySelectorAll('input[name="start_type"]');
                    const subscriptionOptions = document.getElementById('subscription-options');
                    const creditOptions = document.getElementById('credit-options');
                    const subscriptionSelect = document.getElementById('plan_option_select');
                    const creditSelect = document.getElementById('credit_option_select');

                    function toggle() {
                        const value = document.querySelector('input[name="start_type"]:checked')?.value;
                        if (value === 'subscription') {
                            subscriptionOptions.style.display = '';
                            creditOptions.style.display = 'none';
                            subscriptionSelect.disabled = false;
                            subscriptionSelect.required = true;
                            creditSelect.disabled = true;
                            creditSelect.required = false;
                        } else {
                            subscriptionOptions.style.display = 'none';
                            creditOptions.style.display = '';
                            subscriptionSelect.disabled = true;
                            subscriptionSelect.required = false;
                            creditSelect.disabled = false;
                            creditSelect.required = true;
                        }
                    }

                    const registerBtn = document.getElementById('register-btn');
                    function updateBtn() {
                        const value = document.querySelector('input[name="start_type"]:checked')?.value;
                        registerBtn.textContent = value === 'none' ? 'Register' : 'Register & Pay';
                    }
                    radios.forEach(r => r.addEventListener('change', function () { toggle(); updateBtn(); }));
                    toggle();
                    updateBtn();
                })();
            </script>
        </section>
    </div>
@endsection
