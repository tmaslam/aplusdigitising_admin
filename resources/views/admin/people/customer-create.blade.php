@extends('layouts.admin')

@section('title', 'Create Customer | Digitizing Jobs Admin')
@section('page_heading', 'Create Customer')
@section('page_subheading', 'Manually create a new customer account from the admin panel.')

@section('content')
    @if ($errors->any())
        <div class="alert">{{ $errors->first() }}</div>
    @endif

    <section class="card">
        <div class="card-body">
            <form method="post" action="{{ url('/v/create-customer.php') }}">
                @csrf

                <div class="toolbar">
                    <div class="field">
                        <label>First Name <span style="color:var(--accent)">*</span></label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}" required>
                    </div>
                    <div class="field">
                        <label>Last Name <span style="color:var(--accent)">*</span></label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}" required>
                    </div>
                    <div class="field">
                        <label>Email <span style="color:var(--accent)">*</span></label>
                        <input type="email" name="useremail" value="{{ old('useremail') }}" required>
                    </div>
                    <div class="field">
                        <label>Password <span style="color:var(--accent)">*</span></label>
                        <input type="password" name="user_psw" minlength="6" autocomplete="new-password" required>
                    </div>
                    <div class="field">
                        <label>Telephone <span style="color:var(--accent)">*</span></label>
                        <input type="text" name="telephone_num" value="{{ old('telephone_num') }}" required>
                    </div>
                    <div class="field">
                        <label>Country <span style="color:var(--accent)">*</span></label>
                        <select name="selCountry" required>
                            <option value="">Please Select</option>
                            @foreach ($countries as $country)
                                <option value="{{ $country }}" @selected(old('selCountry', 'United States') === $country)>{{ $country }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label>Package Type</label>
                        <select name="package_type">
                            <option value="">No subscription</option>
                            <option value="Starter" @selected(old('package_type') === 'Starter')>Starter</option>
                            <option value="Plus" @selected(old('package_type') === 'Plus')>Plus</option>
                            <option value="Pro" @selected(old('package_type') === 'Pro')>Pro</option>
                            <option value="Enterprise" @selected(old('package_type') === 'Enterprise')>Enterprise</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>Company Name</label>
                        <input type="text" name="company_name" value="{{ old('company_name') }}">
                    </div>
                    <div class="field">
                        <label>Company Type</label>
                        <select name="selCompanyTypes">
                            <option value="">Please Select</option>
                            @foreach ($companyTypes as $type)
                                <option value="{{ $type }}" @selected(old('selCompanyTypes') === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field" style="grid-column: 1 / -1">
                        <label>Company Address</label>
                        <input type="text" name="company_address" value="{{ old('company_address') }}">
                    </div>
                </div>

                <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:18px;">
                    <button type="submit">Create Customer</button>
                    <a class="badge" href="{{ url('/v/customer_list.php') }}">Cancel</a>
                </div>
            </form>
        </div>
    </section>
@endsection
