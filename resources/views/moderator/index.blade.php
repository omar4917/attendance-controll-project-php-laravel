@extends('layouts.app')

@section('content')
    <div class="card">
        <h2 style="margin:0 0 12px 0;">Moderator Labels</h2>
        @if(!empty($error))
            <div class="alert">API error: {{ $error }}</div>
        @endif
        @if(session('success')) <div class="alert" style="background:#e8f5e9;color:#1b5e20;">{{ session('success') }}</div> @endif

        <form method="GET" style="margin-bottom:14px; display:flex; gap:10px;">
            <input name="q" value="{{ $query }}" placeholder="Search labels..." style="flex:1; padding:8px; border:1px solid #ccc; border-radius:8px;">
            <button type="submit" style="padding:8px 16px; border:none; border-radius:8px; background:#0b7d5c; color:#fff;">Search</button>
        </form>

        <div style="overflow-x:auto;">
            <table>
                <thead><tr><th>Key</th><th>Default</th><th>Current Value</th><th>Action</th></tr></thead>
                <tbody>
                @forelse($labels as $l)
                    <tr>
                        <td>{{ $l['key'] }}</td>
                        <td>{{ $l['default'] }}</td>
                        <td>
                            <form method="POST" action="{{ route('moderator.store') }}" style="display:flex; gap:6px;">
                                @csrf
                                <input type="hidden" name="key" value="{{ $l['key'] }}">
                                <input name="value" value="{{ $l['value'] }}" style="padding:6px; border:1px solid #ccc; border-radius:4px; width:200px;">
                                <button type="submit" style="padding:6px 10px; border:none; border-radius:4px; background:#0b7d5c; color:#fff;">Save</button>
                                <button type="submit" name="reset" value="1" style="padding:6px 10px; border:1px solid #ccc; border-radius:4px; background:#fff; color:#333;">Reset</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4">No labels found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
