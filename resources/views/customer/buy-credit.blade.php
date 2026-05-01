@extends('layouts.customer')

@section('title', $pageTitle)

@section('content')
    <div class="container">
        <section class="content-card">
            <div class="section-head">
                <div>
                    <h2>Add Funds</h2>
                    <p>Add funds to your available balance instantly.</p>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert" style="margin-top:16px;">{{ $errors->first() }}</div>
            @endif

            <form method="post" action="/buy-credit.php" style="margin-top:20px;">
                @csrf
                <div style="display:flex; flex-wrap:wrap; gap:12px; margin-bottom:20px;">
                    @php
                        $creditLabels = [
                            10 => '$1000 ➔ $850 (Save 15%)',
                            25 => '$500 ➔ $450 (Save 10%)',
                            50 => '$300 ➔ $275 (Save 8%)',
                            100 => '$100 ➔ $95 (Save 5%)',
                        ];
                    @endphp
                    @foreach ($amounts as $amount)
                        <label style="cursor:pointer;">
                            <input type="radio" name="amount" value="{{ $amount }}" style="display:none;" @checked(old('amount') == $amount) required>
                            <span class="amount-pill" style="display:inline-block; padding:12px 24px; border:2px solid #e2e8f0; border-radius:8px; font-weight:600; transition:all .2s;">
                                {{ $creditLabels[$amount] ?? '$'.$amount }}
                            </span>
                        </label>
                    @endforeach
                </div>

                <div style="margin-bottom:20px; padding:16px; border:2px dashed #e2e8f0; border-radius:8px;">
                    <label style="cursor:pointer; display:flex; align-items:center; gap:10px;">
                        <input type="radio" name="amount" value="custom" style="display:none;" @checked(old('amount') === 'custom') id="custom-amount-radio">
                        <span class="amount-pill" style="display:inline-block; padding:12px 24px; border:2px solid #e2e8f0; border-radius:8px; font-weight:600; transition:all .2s;">
                            Custom Amount
                        </span>
                    </label>
                    <div id="custom-amount-field" style="margin-top:12px; display:none;">
                        <input type="number" name="custom_amount" value="{{ old('custom_amount') }}" min="1" step="0.01" placeholder="Enter amount (USD)" style="padding:10px 14px; border:2px solid #e2e8f0; border-radius:8px; font-size:1rem; width:220px;">
                        <input type="hidden" name="plan_option" value="dash-custom">
                    </div>
                </div>

                <button type="submit" class="button primary">Proceed to Payment</button>
                <a href="/dashboard.php" class="button secondary" style="margin-left:8px;">Cancel</a>
            </form>
        </section>
    </div>

    <script>
        document.querySelectorAll('input[name="amount"]').forEach(radio => {
            radio.addEventListener('change', function () {
                document.querySelectorAll('.amount-pill').forEach(pill => {
                    pill.style.borderColor = '#e2e8f0';
                    pill.style.background = '';
                    pill.style.color = '';
                });
                this.nextElementSibling.style.borderColor = '#F26522';
                this.nextElementSibling.style.background = '#FFF3EB';
                this.nextElementSibling.style.color = '#D94E0F';

                const customField = document.getElementById('custom-amount-field');
                if (this.value === 'custom') {
                    customField.style.display = 'block';
                } else {
                    customField.style.display = 'none';
                }
            });
        });
    </script>
@endsection
