@extends('layouts.admin')

@section('title', 'Edit Customer #'.$customer->user_id.' | Digitizing Jobs Admin')
@section('page_heading', 'Edit Customer #'.$customer->user_id)
@section('page_subheading', 'Update customer account details, pricing, and approval limits.')

@section('content')
    @if ($errors->any())
        <div class="alert">{{ $errors->first() }}</div>
    @endif

    @php $source = request('source', old('source')); @endphp
    <section class="card">
        <div class="card-body">
            <form method="post" action="{{ url('/v/edit-customer-detail.php') }}">
                @csrf
                <input type="hidden" name="uid" value="{{ $customer->user_id }}">
                <input type="hidden" name="source" value="{{ $source }}">

                <div class="toolbar">
                    <div class="field"><label>User Name</label><input type="text" name="user_name" value="{{ old('user_name', $customer->user_name) }}"></div>
                    <div class="field"><label>Password</label><input type="password" name="txtPassword" value="{{ old('txtPassword') }}" autocomplete="new-password" placeholder="Leave blank to keep current password"></div>
                    <div class="field"><label>First Name</label><input type="text" name="txtFirstName" value="{{ old('txtFirstName', $customer->first_name) }}"></div>
                    <div class="field"><label>Last Name</label><input type="text" name="txtLastName" value="{{ old('txtLastName', $customer->last_name) }}"></div>
                    <div class="field"><label>Company</label><input type="text" name="txtCompany" value="{{ old('txtCompany', $customer->company) }}"></div>
                    <div class="field">
                        <label>Company Type</label>
                        <select name="selCompanyTypes">
                            <option value="">Please Select</option>
                            @foreach ($companyTypes as $type)
                                <option value="{{ $type }}" @selected(old('selCompanyTypes', $customer->company_type) === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field"><label>Email</label><input type="text" name="txtEmail" value="{{ old('txtEmail', $customer->user_email) }}"></div>
                    <div class="field"><label>Address</label><input type="text" name="txtCompanyAddress" value="{{ old('txtCompanyAddress', $customer->company_address) }}"></div>
                    <div class="field"><label>Zip Code</label><input type="text" name="txtZipCode" value="{{ old('txtZipCode', $customer->zip_code) }}"></div>
                    <div class="field"><label>City</label><input type="text" name="txtCity" value="{{ old('txtCity', $customer->user_city) }}"></div>
                    <div class="field">
                        <label>Country</label>
                        <select name="selCountry">
                            <option value="">Please Select</option>
                            @foreach ($countries as $country)
                                <option value="{{ $country }}" @selected(old('selCountry', $customer->user_country) === $country)>{{ $country }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field"><label>Phone</label><input type="text" name="txtTelephone" value="{{ old('txtTelephone', $customer->user_phone) }}"></div>
                    <div class="field"><label>Signup IP Address</label><input type="text" name="txtSignupIp" value="{{ old('txtSignupIp', $customer->userip_addrs) }}" placeholder="Leave blank to clear"></div>
                    <div class="field"><label>Standard Customer Rate</label><input type="text" name="normal_fee" value="{{ old('normal_fee', $customer->normal_fee) }}" placeholder="Blank falls back to site pricing"></div>
                    <div class="field"><label>Express / Normal Customer Rate</label><input type="text" name="middle_fee" value="{{ old('middle_fee', $customer->middle_fee) }}" placeholder="Blank falls back to standard/site pricing"></div>
                    <div class="field"><label>Priority Customer Rate</label><input type="text" name="urgent_fee" value="{{ old('urgent_fee', $customer->urgent_fee) }}" placeholder="Blank falls back to site pricing"></div>
                    <div class="field"><label>Super Rush Customer Rate</label><input type="text" name="super_fee" value="{{ old('super_fee', $customer->super_fee) }}" placeholder="Blank falls back to site pricing"></div>
                    <div class="field"><label>Pending Orders Limit</label><input type="text" name="customer_pending_order_limit" value="{{ old('customer_pending_order_limit', $customer->customer_pending_order_limit) }}"></div>
                    <div class="field"><label>Advance Deposit</label><input type="text" name="topup" value="{{ old('topup', $customer->topup) }}"></div>
                    <div class="field"><label>Available Balance</label><input type="text" name="available_balance" value="{{ old('available_balance', number_format($availableBalance, 2, '.', '')) }}"></div>
                    <div class="field">
                        <label>Payment Terms</label>
                        <select name="payment_terms">
                            @for ($days = 7; $days <= 56; $days += 7)
                                <option value="{{ $days }}" @selected((string) old('payment_terms', $customer->payment_terms) === (string) $days)>{{ $days }} Days</option>
                            @endfor
                        </select>
                    </div>
                    <div class="field">
                        <label>Status</label>
                        <select name="is_active">
                            <option value="1" @selected((string) old('is_active', $customer->is_active) === '1')>Active</option>
                            <option value="0" @selected((string) old('is_active', $customer->is_active) === '0')>Blocked</option>
                        </select>
                    </div>
                    <div class="field"><label>Max Number of Stitches Override</label><input type="text" name="max_num_stiches" value="{{ old('max_num_stiches', $customer->max_num_stiches) }}" placeholder="Blank uses site pricing"></div>
                </div>

                <section class="card" style="margin-top:24px;">
                    <div class="card-body">
                        <h3 style="margin:0 0 12px;font-size:1.1rem;">Quick Fund Package</h3>
                        <p style="margin:0 0 14px;color:var(--muted);font-size:0.88rem;">Select a package to instantly add balance and bonus credit to this customer.</p>

                        <div class="field">
                            <label>Fund Package</label>
                            <select name="fund_package" id="fund_package">
                                <option value="">Select One</option>
                                <option value="fund-1000" data-balance="850.00" data-bonus="150.00">$1000 Package → $850 Balance + $150 Bonus (Save 15%)</option>
                                <option value="fund-500" data-balance="450.00" data-bonus="50.00">$500 Package → $450 Balance + $50 Bonus (Save 10%)</option>
                                <option value="fund-300" data-balance="275.00" data-bonus="25.00">$300 Package → $275 Balance + $25 Bonus (Save 8%)</option>
                                <option value="fund-100" data-balance="95.00" data-bonus="5.00">$100 Package → $95 Balance + $5 Bonus (Save 5%)</option>
                                <option value="custom">Custom Amount</option>
                            </select>
                        </div>

                        <div id="custom-fund-fields" style="display:none;gap:12px;flex-wrap:wrap;margin-top:10px;">
                            <div class="field">
                                <label>Add Balance ($)</label>
                                <input type="number" name="custom_fund_balance" id="custom_fund_balance" step="0.01" min="0" placeholder="0.00">
                            </div>
                            <div class="field">
                                <label>Add Bonus ($)</label>
                                <input type="number" name="custom_fund_bonus" id="custom_fund_bonus" step="0.01" min="0" placeholder="0.00">
                            </div>
                        </div>

                        <div id="fund-preview" style="margin-top:12px;padding:10px 14px;background:#f8fafc;border-radius:8px;font-size:0.88rem;color:#475569;display:none;">
                            <strong>Preview:</strong> <span id="fund-preview-text">—</span>
                        </div>
                    </div>
                </section>

                <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:18px;">
                    <button type="submit">Save Customer</button>
                    <a class="badge" href="{{ url('/v/customer-detail.php?uid='.$customer->user_id.($source ? '&source='.rawurlencode($source) : '')) }}">Cancel</a>
                </div>

                <script>
                    (function () {
                        var fundSelect = document.getElementById('fund_package');
                        var customFields = document.getElementById('custom-fund-fields');
                        var preview = document.getElementById('fund-preview');
                        var previewText = document.getElementById('fund-preview-text');

                        function updatePreview() {
                            var option = fundSelect.options[fundSelect.selectedIndex];
                            var value = fundSelect.value;

                            if (value === 'custom') {
                                customFields.style.display = 'flex';
                                preview.style.display = 'block';
                                previewText.textContent = 'Custom amounts will be added to current balance and bonus.';
                                return;
                            }

                            customFields.style.display = 'none';

                            if (value === '') {
                                preview.style.display = 'none';
                                return;
                            }

                            var balance = option.getAttribute('data-balance') || '0';
                            var bonus = option.getAttribute('data-bonus') || '0';
                            preview.style.display = 'block';
                            previewText.textContent = 'Will add $' + balance + ' to Available Balance and $' + bonus + ' to Advance Deposit.';
                        }

                        fundSelect.addEventListener('change', updatePreview);
                    })();
                </script>
            </form>
        </div>
    </section>
@endsection
