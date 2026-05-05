@extends('layouts.admin')

@section('title', 'Download Backup | Digitizing Jobs Admin')
@section('page_heading', 'Download Backup')
@section('page_subheading', 'Download all order files for a selected period as a ZIP archive.')

@section('content')
    <section class="card">
        <div class="card-body">
            <form method="post" action="{{ url('/v/download-backup.php') }}" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end;">
                @csrf
                <div style="flex: 1; min-width: 200px;">
                    <label for="date_from" style="display: block; font-size: 0.85rem; margin-bottom: 4px;">From</label>
                    <input type="date" id="date_from" name="date_from" value="{{ $dateFrom }}" style="width: 100%;">
                </div>
                <div style="flex: 1; min-width: 200px;">
                    <label for="date_to" style="display: block; font-size: 0.85rem; margin-bottom: 4px;">To</label>
                    <input type="date" id="date_to" name="date_to" value="{{ $dateTo }}" style="width: 100%;">
                </div>
                <button type="submit" class="button primary">Download ZIP</button>
            </form>

            <p style="margin-top: 16px; color: #64748B; font-size: 0.9rem;">
                Leave both dates empty to download files from all orders. The ZIP will be organized by Order ID.
            </p>
        </div>
    </section>
@endsection
