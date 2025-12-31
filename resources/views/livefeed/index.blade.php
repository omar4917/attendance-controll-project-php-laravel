@extends('layouts.app')

@section('content')
<style>
    /* Green Theme Card Styles */
    .livefeed-card {
        background: var(--bg-primary);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        overflow: hidden;
        color: var(--text-primary);
        font-family: sans-serif;
        display: flex;
        flex-direction: column;
    }
    .livefeed-img-container {
        height: 200px;
        background: var(--bg-secondary);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        position: relative;
    }
    .livefeed-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .livefeed-body {
        padding: 15px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .livefeed-title {
        font-size: 16px;
        font-weight: bold;
        color: var(--text-primary);
        margin-bottom: 2px;
    }
    .livefeed-meta {
        font-size: 12px;
        color: var(--text-secondary);
        margin-bottom: 8px;
    }
    .livefeed-badges {
        display: flex;
        gap: 8px;
        margin-bottom: 10px;
    }
    .badge-light {
        background: var(--bg-quaternary);
        border: 1px solid var(--border-color);
        color: var(--text-primary);
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
    }
    .badge-green {
        background: var(--btn-primary);
        border: 1px solid var(--btn-primary);
        color: #fff;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
    }
    
    /* Form Elements */
    .btn-block {
        display: block;
        width: 100%;
        padding: 8px;
        border-radius: 4px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        text-align: center;
        text-decoration: none;
        box-sizing: border-box;
    }
    .btn-delete {
        background: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
    }
    .btn-delete:hover { background: #f1aeb5; }
    
    .btn-link {
        background: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
    }
    .btn-link:hover { background: #c3e6cb; }
    
    .btn-create {
        background: #e2e3e5;
        border: 1px solid #d3d6d8;
        color: #383d41;
    }
    .btn-create:hover { background: #d3d6d8; }

    .input-light {
        background: var(--input-bg);
        border: 1px solid var(--input-border);
        color: var(--input-text);
        padding: 8px;
        border-radius: 4px;
        width: 100%;
        box-sizing: border-box;
        font-size: 13px;
    }
    .input-light::placeholder { color: #999; }
    
    .checkbox-wrapper {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        color: var(--text-secondary);
    }
</style>

<div style="padding: 20px;">
    <div class="page-header" style="margin-bottom: 20px;">
        <h2 style="margin:0; font-size: 20px; font-weight: 600; color: var(--text-primary);">{{ __('messages.live_feed') }}</h2>
    </div>

    @if(!empty($error))
        <div class="alert alert-danger" style="background:#f8d7da; color:#842029; padding:12px; border-radius:6px; margin-bottom:20px; border: 1px solid #f5c2c7;">API error: {{ $error }}</div>
    @endif
    @if(session('success'))
        <div class="alert alert-success" style="background:#d1e7dd; color:#0f5132; padding:12px; border-radius:6px; margin-bottom:20px; border: 1px solid #badbcc;">{{ session('success') }}</div>
    @endif

    <!-- Filters -->
    <form method="GET" style="margin-bottom:20px; display:flex; gap:10px; align-items:end; flex-wrap:wrap; background:var(--bg-quaternary); padding:15px; border-radius:8px; border:1px solid var(--border-color);">
        <div>
            <label style="font-size:12px; font-weight:600; color:var(--text-secondary);">{{ __('messages.date') }}</label>
            <input type="date" name="date" value="{{ request('date') }}" style="padding:6px; border:1px solid var(--border-color); border-radius:4px; background:var(--input-bg); color:var(--input-text);">
        </div>
        <div>
            <label style="font-size:12px; font-weight:600; color:var(--text-secondary);">{{ __('messages.search') }}</label>
            <input type="text" name="employee" value="{{ request('employee') }}" placeholder="{{ __('messages.name_or_id') ?? 'Name or ID' }}" style="padding:6px; border:1px solid var(--border-color); border-radius:4px; background:var(--input-bg); color:var(--input-text);">
        </div>
        <div>
            <button type="submit" style="padding:6px 12px; font-size:13px; background:var(--btn-primary); color:white; border:none; border-radius:4px; cursor:pointer;">{{ __('messages.filter') }}</button>
            <a href="{{ route('livefeed.index') }}" style="padding:6px 12px; font-size:13px; text-decoration:none; color:var(--text-primary); border:1px solid var(--border-color); border-radius:4px; margin-left:5px; background:var(--bg-primary);">{{ __('messages.reset') }}</a>
        </div>
    </form>

    <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap:20px;">
        @forelse($images as $img)
            <div class="livefeed-card">
                <div class="livefeed-img-container">
@inject('djangoApi', 'App\Services\DjangoApi')
                    @if($img['url'])
                        <img src="{{ $djangoApi->getBaseUrl() . $img['url'] }}" class="livefeed-img" alt="Snapshot">
                    @else
                        <span style="color:var(--text-secondary);">{{ __('messages.no_image') }}</span>
                    @endif
                </div>
                <div class="livefeed-body">
                    <div class="livefeed-title">
                        {{ $img['employee_name'] ?? $img['subject_identifier'] }}
                    </div>
                    <div class="livefeed-meta">
                        {{ __('messages.captured') }}: {{ $img['captured_at'] }}<br>
                        {{ __('messages.device') }}: Not provided
                    </div>
                    <div class="livefeed-badges">
                        <span class="badge-light">ID: {{ $img['subject_identifier'] }}</span>
                        <span class="badge-light">26 / 6 today</span> 
                        <span class="badge-green">0 left</span>
                    </div>

                    <!-- Delete Form -->
                    <form method="POST" action="{{ route('livefeed.destroy', $img['id']) }}" onsubmit="return confirm('Delete this image?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-block btn-delete">{{ __('messages.delete') }}</button>
                    </form>

                    <!-- Link Form -->
                    <form method="POST" action="{{ route('livefeed.action') }}" style="display:flex; flex-direction:column; gap:8px;">
                        @csrf
                        <input type="hidden" name="action" value="assign">
                        <input type="hidden" name="image_id" value="{{ $img['id'] }}">
                        
                        <input name="employee_id" class="input-light" placeholder="Link to employee ID">
                        
                        <div class="checkbox-wrapper">
                            <input type="checkbox" name="update_photo" id="update_photo_{{ $img['id'] }}">
                            <label for="update_photo_{{ $img['id'] }}">{{ __('messages.update_photo_snapshot') }}</label>
                        </div>
                        
                        <button type="submit" class="btn-block btn-link">{{ __('messages.link_to_employee') }}</button>
                    </form>

                    <!-- Create Form -->
                    <form method="POST" action="{{ route('livefeed.action') }}" style="display:flex; flex-direction:column; gap:8px;">
                        @csrf
                        <input type="hidden" name="action" value="create_employee">
                        <input type="hidden" name="image_id" value="{{ $img['id'] }}">
                        
                        <input name="new_employee_id" class="input-light" placeholder="New employee ID">
                        <input name="new_employee_name" class="input-light" placeholder="New employee name">
                        
                        <button type="submit" class="btn-block btn-create">{{ __('messages.create_employee_snapshot') }}</button>
                    </form>
                </div>
            </div>
        @empty
            <div style="grid-column:1/-1; text-align:center; padding:40px; color:var(--text-secondary);">
                {{ __('messages.no_live_feed') }}
            </div>
        @endforelse
    </div>
</div>
@endsection
