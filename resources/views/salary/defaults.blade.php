@extends('layouts.app')

@section('content')
    <div class="card">
        <h2 style="margin:0 0 12px 0;">{{ __('messages.salary_defaults') }}</h2>
        @if(!empty($error))
            <div class="alert">API error: {{ $error }}</div>
        @endif
        @if(session('success')) <div class="alert" style="background:#e8f5e9;color:#1b5e20;">{{ session('success') }}</div> @endif

        <form method="POST" action="{{ route('salary.defaults.save') }}" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:14px; align-items:end;">
            @csrf
            @foreach($defaults as $key => $val)
                <label style="text-transform:capitalize;">{{ __('messages.'.$key) }}<br>
                    <input name="{{ $key }}" value="{{ $val }}" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:8px;">
                </label>
            @endforeach
            <div style="grid-column: 1/-1;">
                <button type="submit" style="padding:12px 20px; border:none; border-radius:8px; background:#0b7d5c; color:#fff; font-size:16px;">{{ __('messages.save_defaults') }}</button>
            </div>
        </form>
    </div>
@endsection
