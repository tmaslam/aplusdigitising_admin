@extends('layouts.customer')

@section('title', 'Summary - '.$siteContext->displayLabel())
@section('hero_class', 'hero-compact dashboard-hero')
@section('hero_title', 'Dashboard')
@section('hero_text', 'Track your orders, quotes, billing, downloads, and account details in one streamlined workspace.')

@section('hero_meta')
    <div style="display:grid;gap:4px;justify-items:end;text-align:right;">
        <span style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.08em;color:#666;font-weight:600;">Subscription</span>
        @if ($subscription)
            <strong style="font-size:0.92rem;color:#333;">{{ $subscription['plan_name'] }}</strong>
            <span style="font-size:0.78rem;color:#666;">Next payment: {{ $subscription['next_payment_date'] }}</span>
        @else
            <strong style="font-size:0.92rem;color:#333;">No Subscription</strong>
        @endif
    </div>
@endsection

@section('top_banner')
    <section class="content-card" style="background: linear-gradient(135deg, #FFF8F0 0%, #FFF0E4 100%); border: 1.5px solid rgba(242, 101, 34, 0.12); margin-bottom: 0;">
        <div style="display: flex; align-items: center; gap: 18px; flex-wrap: wrap;">
            <img src="{{ url('/images/dashboard-welcome.jpg') }}" alt="Welcome" style="width: 72px; height: 72px; border-radius: 50%; object-fit: cover; border: 3px solid #fff; box-shadow: 0 4px 12px rgba(242, 101, 34, 0.15);">
            <div>
                <h3 style="margin: 0; font-size: 1.15rem; color: #333;">Welcome back, {{ $customer->first_name ?: 'valued customer' }}! 👋</h3>
                <p style="margin: 4px 0 0; color: #666; font-size: 0.9rem;">We're here to help you with all your embroidery digitizing &amp; vector art needs.</p>
            </div>
        </div>
    </section>
@endsection

@section('content')
    <section class="content-card">
        <div class="section-head">
            <div>
                <h3>Summary</h3>
                <p>View your orders, quotes, billing, and account details in one place.</p>
            </div>
        </div>

        <div class="portal-stat-grid">
            <a class="metric-link" href="{{ url('/view-orders.php') }}">
                <article class="portal-stat">
                    <span>My Orders</span>
                    <strong>{{ $metrics['orders'] }}</strong>
                </article>
            </a>
            <a class="metric-link" href="{{ url('/view-quotes.php') }}">
                <article class="portal-stat">
                    <span>My Quotes</span>
                    <strong>{{ $metrics['quotes'] }}</strong>
                </article>
            </a>
            <a class="metric-link" href="{{ url('/view-billing.php') }}">
                <article class="portal-stat">
                    <span>Payment Due</span>
                    <strong>${{ number_format($metrics['billing_total'], 2) }}</strong>
                </article>
            </a>
            <div class="portal-stat" style="position:relative; cursor:pointer;" onclick="toggleInfo(this)">
                <span class="stat-label">Available Balance</span>
                <strong>${{ number_format($metrics['available_balance'], 2) }}</strong>
                <div class="info-popup">Payments and usable credit currently available on the account.</div>
            </div>
            <div class="portal-stat" style="position:relative; cursor:pointer;" onclick="toggleInfo(this)">
                <span class="stat-label">Bonus Credit</span>
                <strong>${{ number_format($metrics['deposit_balance'], 2) }}</strong>
                <div class="info-popup">Bonus funds are used after Available Balance runs out.</div>
            </div>
        </div>
    </section>

    <section class="content-card">
        <div class="section-head">
            <div>
                <h3>Quick Actions</h3>
                <p>Jump into the task you need most without hunting through the portal.</p>
            </div>
        </div>

        <div class="action-grid">
            <a class="action-card action-card-primary" href="{{ url('/new-order.php') }}">
                <span>Digitizing</span>
                <strong>Place New Order</strong>
                <p>Upload artwork and start a standard digitizing request.</p>
            </a>
            <a class="action-card" href="{{ url('/quote.php') }}">
                <span>Quote</span>
                <strong>Digitizing Quote</strong>
                <p>Ask for digitizing pricing first before placing a new order.</p>
            </a>
            <a class="action-card" href="{{ url('/vector-order.php') }}">
                <span>Vector</span>
                <strong>Place Vector Order</strong>
                <p>Start a vector-only job with the existing vector order flow.</p>
            </a>
            <a class="action-card" href="{{ url('/vector-quote.php') }}">
                <span>Vector Quote</span>
                <strong>Request Vector Quote</strong>
                <p>Ask for vector pricing first before placing a vector order.</p>
            </a>
        </div>
    </section>

    <section class="content-card">
        <div class="section-head">
            <div>
                <h3>Recent Activity</h3>
                <p>Pick up where you left off without scanning every list page.</p>
            </div>
        </div>

        <div class="workspace-grid">
            <div class="activity-card">
                <span class="activity-kicker">Latest Orders</span>
                <div class="activity-list" style="margin-top:12px;">
                    @if ($recentOrders->isEmpty())
                        <div class="empty-state">No active orders are open right now.</div>
                    @else
                        @foreach ($recentOrders as $order)
                            <div class="activity-item">
                                <div class="activity-meta">
                                    <strong><a class="inline-link" href="/view-order-detail.php?order_id={{ $order->order_id }}&origin=orders">{{ $order->design_name ?: 'Order #'.$order->order_id }}</a></strong>
                                    <span class="status {{ \App\Support\CustomerWorkflowStatus::tone($order) }}">{{ \App\Support\CustomerWorkflowStatus::label($order) }}</span>
                                </div>
                                <div class="file-actions">
                                    <a class="button secondary" href="/view-order-detail.php?order_id={{ $order->order_id }}&origin=orders">Open Order</a>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            <div class="activity-card">
                <span class="activity-kicker">Quotes & Billing</span>
                <div class="activity-list" style="margin-top:12px;">
                    @if ($recentQuotes->isEmpty())
                        <div class="activity-item">
                            <strong>No open quotes</strong>
                            <p>You can request pricing first whenever you need a review before ordering.</p>
                        </div>
                    @else
                        @foreach ($recentQuotes as $quote)
                            <div class="activity-item">
                                <div class="activity-meta">
                                    <strong><a class="inline-link" href="/view-quote-detail.php?order_id={{ $quote->order_id }}&origin=quotes">{{ $quote->design_name ?: 'Quote #'.$quote->order_id }}</a></strong>
                                    <span class="status {{ \App\Support\CustomerWorkflowStatus::tone($quote, true) }}">{{ \App\Support\CustomerWorkflowStatus::label($quote, true) }}</span>
                                </div>
                                <div class="file-actions">
                                    <a class="button secondary" href="/view-quote-detail.php?order_id={{ $quote->order_id }}&origin=quotes">Open Quote</a>
                                </div>
                            </div>
                        @endforeach
                    @endif

                    @if ($recentBilling->isNotEmpty())
                        <div class="activity-item">
                            <div class="activity-meta">
                                <strong>${{ number_format($metrics['billing_total'], 2) }} outstanding</strong>
                                <span class="status warning">{{ $metrics['billing_count'] }} due</span>
                            </div>
                            <div class="file-actions">
                                <a class="button secondary" href="{{ url('/view-billing.php') }}">Open Billing</a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <style>
        .info-popup {
            display: none;
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            right: 0;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 0.85rem;
            color: #475569;
            box-shadow: 0 10px 24px rgba(17, 31, 45, 0.12);
            z-index: 10;
        }
        .info-popup.active {
            display: block;
        }
        .stat-label {
            display: flex !important;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
            letter-spacing: 0.04em;
        }
        .portal-stat-grid {
            align-items: stretch;
        }
        .portal-stat-grid > .metric-link,
        .portal-stat-grid > .portal-stat {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .metric-link .portal-stat {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
    </style>

    <script>
        function toggleInfo(card) {
            const popup = card.querySelector('.info-popup');
            const isActive = popup.classList.contains('active');
            document.querySelectorAll('.info-popup').forEach(p => p.classList.remove('active'));
            if (!isActive) {
                popup.classList.add('active');
            }
        }
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.portal-stat[style*="cursor:pointer"]')) {
                document.querySelectorAll('.info-popup').forEach(p => p.classList.remove('active'));
            }
        });
    </script>
@endsection
