@extends('layouts.customer')

@section('title', 'Download All Files - '.$siteContext->displayLabel())
@section('hero_class', 'hero-compact')
@section('hero_title', 'Download All Files')
@section('hero_text', 'Select a date range to download all your paid order files in a single ZIP archive.')

@section('content')
    <section class="content-card">
        <div class="section-head">
            <div>
                <h3>Download All Files</h3>
                <p>Choose a period and download every file from your completed paid orders in one click.</p>
            </div>
        </div>

        <form method="post" action="{{ url('/download-all-files.php') }}" class="filter-bar" style="flex-wrap: wrap; gap: 12px;">
            @csrf
            <div style="display: flex; align-items: center; gap: 8px; flex: 1; min-width: 200px;">
                <label for="date_from" style="font-size: 0.85rem; color: var(--muted); white-space: nowrap;">From</label>
                <input type="date" id="date_from" name="date_from" value="{{ $dateFrom }}" style="flex: 1;">
            </div>
            <div style="display: flex; align-items: center; gap: 8px; flex: 1; min-width: 200px;">
                <label for="date_to" style="font-size: 0.85rem; color: var(--muted); white-space: nowrap;">To</label>
                <input type="date" id="date_to" name="date_to" value="{{ $dateTo }}" style="flex: 1;">
            </div>
            <button type="submit" class="button primary">Download ZIP</button>
        </form>

        <div class="content-note" style="margin-top: 16px;">
            <strong>Tip:</strong> Leave both dates empty to download every file from all your paid orders. Large archives may take a moment to prepare.
        </div>
    </section>
@endsection
