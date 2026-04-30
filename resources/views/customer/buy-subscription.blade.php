@extends('layouts.customer')

@section('title', $pageTitle)

@section('content')
    <div class="container">
        <section class="content-card">
            <div class="section-head">
                <div>
                    <h2>Buy Subscription</h2>
                    <p>Choose a monthly plan that fits your business.</p>
                </div>
            </div>

            @if ($subscription)
                <div class="alert alert-info" style="margin-top:16px;">
                    You are currently on the <strong>{{ $subscription['plan_name'] }}</strong> plan.
                    Next payment: {{ $subscription['next_payment_date'] }}.
                </div>
            @endif

            @if ($errors->any())
                <div class="alert" style="margin-top:16px;">{{ $errors->first() }}</div>
            @endif

            <div style="display:grid; gap:16px; margin-top:20px;">
                @foreach ($plans as $planIndex => $plan)
                    @php
                        $isCurrent = $subscription && $subscription['plan_name'] === $plan['name'];
                        $canSelect = ! $isCurrent;
                        $buttonLabel = $isCurrent ? 'Current Plan' : 'Change Plan';
                    @endphp
                    <form method="post" action="/aplus/buy-subscription.php">
                        @csrf
                        <input type="hidden" name="amount" value="{{ $plan['amount'] }}">
                        <div style="display:flex; align-items:center; justify-content:space-between; padding:18px 22px; border:1px solid {{ $isCurrent ? 'rgba(242,101,34,0.25)' : 'var(--line)' }}; border-radius:16px; background:{{ $isCurrent ? 'linear-gradient(135deg, #FFF3EB 0%, #FFE4D6 100%)' : '#fff' }};">
                            <div>
                                <strong style="font-size:1.1rem; color:{{ $isCurrent ? 'var(--brand-dark)' : 'inherit' }}">{{ $plan['name'] }}</strong>
                                <div style="color:{{ $isCurrent ? 'var(--brand-dark)' : 'var(--muted)' }}; font-size:0.9rem; margin-top:4px; font-weight:{{ $isCurrent ? '600' : '400' }}">${{ $plan['amount'] }} / month</div>
                                <a href="/aplus/price-plan.php" style="color:var(--brand); font-size:0.85rem; text-decoration:underline;">see benefits</a>
                            </div>
                            <button type="submit" class="button primary" @disabled(! $canSelect)>
                                {{ $buttonLabel }}
                            </button>
                        </div>
                    </form>
                @endforeach
            </div>
        </section>
    </div>
@endsection
