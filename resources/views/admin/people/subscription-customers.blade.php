@extends('layouts.admin')

@php
    $currentColumn = request('column_name', 'user_id');
    $currentDirection = strtolower(request('sort', 'desc'));
    $nextDirection = fn ($column) => $currentColumn === $column && $currentDirection === 'asc' ? 'desc' : 'asc';
@endphp

@section('title', 'Subscription Customers | Digitizing Jobs Admin')
@section('page_heading', 'Subscription Customers')
@section('page_subheading', 'Customers with active monthly subscriptions.')

@section('content')
    <style>
        .customer-actions-cell {
            min-width: 280px;
        }

        .customer-actions {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            flex-wrap: nowrap;
            min-width: max-content;
        }

        .customer-actions .badge {
            white-space: nowrap;
            padding: 8px 10px;
            font-size: 0.78rem;
        }

        .plan-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 999px;
            background: rgba(100, 116, 139, 0.15);
            color: var(--accent-dark);
            font-size: 0.82rem;
            font-weight: 800;
        }
    </style>

    <section class="card">
        <div class="card-body">
            <form method="get" action="{{ url('/v/subscription-customers.php') }}" class="toolbar">
                <div class="field">
                    <label for="txtUserID">User ID</label>
                    <input id="txtUserID" type="text" name="txtUserID" value="{{ request('txtUserID') }}">
                </div>
                <div class="field">
                    <label for="txtUserName">Username</label>
                    <input id="txtUserName" type="text" name="txtUserName" value="{{ request('txtUserName') }}">
                </div>
                <div class="field">
                    <label for="txtEmail">Email</label>
                    <input id="txtEmail" type="text" name="txtEmail" value="{{ request('txtEmail') }}">
                </div>
                <div class="field" style="min-width:auto;">
                    <label>&nbsp;</label>
                    <button type="submit">Filter</button>
                </div>
            </form>
        </div>
    </section>

    <section class="card">
        <div class="card-body">
            <div style="display:flex;justify-content:space-between;gap:16px;align-items:center;flex-wrap:wrap;">
                <div>
                    <h3 style="margin:0 0 6px;font-size:1.15rem;">Subscription Directory</h3>
                    <p class="muted" style="margin:0;">Showing {{ $customers->total() }} customers with active subscriptions.</p>
                </div>
                <span class="badge">subscription management</span>
            </div>

            <div class="table-wrap" style="margin-top:18px;">
                <table>
                    <thead>
                    <tr>
                        <th class="action-col">Action</th>
                        <th><a href="{{ request()->fullUrlWithQuery(['column_name' => 'user_id', 'sort' => $nextDirection('user_id')]) }}">User ID</a></th>
                        <th><a href="{{ request()->fullUrlWithQuery(['column_name' => 'user_name', 'sort' => $nextDirection('user_name')]) }}">Username</a></th>
                        <th>Plan</th>
                        <th>Amount</th>
                        <th><a href="{{ request()->fullUrlWithQuery(['column_name' => 'user_country', 'sort' => $nextDirection('user_country')]) }}">Country</a></th>
                        <th>Subscription Date</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if (collect($customers)->isEmpty())
                        <tr>
                            <td colspan="7" class="muted">No customers with subscriptions match the current filters.</td>
                        </tr>
                    @else
                    @foreach ($customers as $customer)
                        <tr>
                            <td class="action-col customer-actions-cell">
                                <div class="action-row customer-actions">
                                    <a class="badge" href="{{ url('/v/customer-detail.php?uid='.$customer->user_id) }}">View</a>
                                    <a class="badge" href="{{ url('/v/edit-customer-detail.php?uid='.$customer->user_id) }}">Edit</a>
                                </div>
                            </td>
                            <td class="cell-nowrap">{{ $customer->user_id }}</td>
                            <td class="cell-wrap-md"><a href="{{ url('/v/customer-detail.php?uid='.$customer->user_id) }}" style="color: var(--accent);">{{ $customer->user_name ?: '-' }}</a></td>
                            <td class="cell-nowrap">
                                <span class="plan-badge">{{ $customer->subscription_plan_label }}</span>
                            </td>
                            <td class="cell-nowrap">${{ number_format((float) $customer->subscription_amount, 2) }}</td>
                            <td class="cell-wrap-md">{{ $customer->user_country ?: '-' }}</td>
                            <td class="cell-nowrap">{{ $customer->subscription_date ? \Carbon\Carbon::parse($customer->subscription_date)->format('M d, Y') : '-' }}</td>
                        </tr>
                    @endforeach
                    @endif
                    </tbody>
                </table>
            </div>

            @if ($customers->hasPages())
                <div style="margin-top:18px;">
                    {{ $customers->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
