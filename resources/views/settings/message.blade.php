@extends('layouts.app')

@section('content')
<style>
    /* Green Theme Styles for Settings */
    .settings-card {
        background-color: var(--bg-primary);
        color: var(--text-primary);
        padding: 20px;
        border-radius: 6px;
        font-family: sans-serif;
    }

    .settings-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .settings-title {
        font-size: 20px;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
    }

    .btn-history {
        background: var(--bg-quaternary);
        color: var(--text-primary);
        border: 1px solid var(--border-color);
        padding: 5px 12px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 12px;
    }
    .btn-history:hover { background: var(--bg-secondary); }

    .section-title {
        font-size: 16px;
        font-weight: 600;
        color: var(--text-primary);
        margin-top: 20px;
        margin-bottom: 15px;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 5px;
    }

    .form-group {
        margin-bottom: 15px;
        display: grid;
        grid-template-columns: 200px 1fr;
        align-items: center;
        gap: 20px;
    }

    .form-label {
        font-weight: 600;
        font-size: 13px;
        color: var(--text-primary);
    }

    .form-input {
        background: var(--input-bg);
        border: 1px solid var(--input-border);
        color: var(--input-text);
        padding: 8px 12px;
        border-radius: 6px;
        width: 100%;
        max-width: 400px;
        font-size: 13px;
    }
    .form-input:focus {
        border-color: var(--btn-primary);
        outline: none;
    }
    
    .form-textarea {
        background: var(--input-bg);
        border: 1px solid var(--input-border);
        color: var(--input-text);
        padding: 8px 12px;
        border-radius: 6px;
        width: 100%;
        max-width: 400px;
        font-size: 13px;
        min-height: 80px;
        font-family: monospace;
    }

    .checkbox-wrapper {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .checkbox-input {
        accent-color: var(--btn-primary);
        width: 16px;
        height: 16px;
    }

    .meta-info {
        font-size: 12px;
        color: var(--text-secondary);
        margin-top: 10px;
        margin-bottom: 20px;
    }

    .action-bar {
        display: flex;
        gap: 10px;
        margin-top: 20px;
        padding-top: 15px;
        border-top: 1px solid var(--border-color);
    }

    .btn-save {
        background: var(--btn-primary);
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 13px;
    }
    .btn-save:hover { background: #157347; }

    .btn-delete {
        background: #dc3545;
        color: #fff;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 13px;
        margin-left: auto;
    }
    .btn-delete:hover { background: #bb2d3b; }
    
    /* Table Styles for Overrides */
    .light-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
        font-size: 13px;
        border: 1px solid var(--border-color);
    }
    .light-table th {
        background: var(--btn-primary);
        color: #fff;
        text-align: left;
        padding: 8px;
        font-weight: 600;
        font-size: 11px;
        text-transform: uppercase;
    }
    .light-table td {
        background: var(--bg-primary);
        border-bottom: 1px solid var(--border-color);
        padding: 8px;
        color: var(--text-primary);
    }
    .light-table input {
        background: var(--input-bg);
        border: 1px solid var(--input-border);
        color: var(--input-text);
        padding: 4px 8px;
        border-radius: 4px;
        width: 100%;
    }
    
    .btn-add-override {
        background: var(--btn-primary);
        color: #fff;
        border: none;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 11px;
        cursor: pointer;
        margin-bottom: 10px;
    }
    
    .section-header-bar {
        background: var(--bg-header);
        color: var(--text-primary);
        padding: 10px;
        font-weight: 600;
        margin-bottom: 15px;
        border-radius: 4px;
        margin-top: 30px;
        border: 1px solid var(--border-color);
    }

    .alert-error {
        background: #f8d7da;
        color: #721c24;
        padding: 10px;
        border-radius: 4px;
        margin-bottom: 15px;
        border: 1px solid #f5c6cb;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        padding: 10px;
        border-radius: 4px;
        margin-bottom: 15px;
        border: 1px solid #c3e6cb;
    }
</style>

<div class="settings-card">
    <div class="settings-header">
        <div>
            <div style="font-size: 18px; color: var(--text-secondary); margin-bottom: 5px;">Change voice and text message settings</div>
            <h1 class="settings-title">Voice & Message Settings</h1>
        </div>
        <a href="#" class="btn-history">HISTORY</a>
    </div>

    @if(!empty($error))
        <div class="alert-error">API Error: {{ $error }}</div>
    @endif
    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    @php
        $text = $data['text'] ?? [];
        $voice = $data['voice'] ?? [];
        $nameOverrides = $data['voice_name_overrides'] ?? ($voice['overrides']['names'] ?? []);
        $phraseOverrides = $data['voice_phrase_overrides'] ?? ($voice['overrides']['phrases'] ?? []);
        $prefs = $data['voice_preferences'] ?? ($voice['overrides']['preferences'] ?? []);
    @endphp

    <form method="POST" action="{{ route('settings.voice_message.save') }}">
        @csrf
        
        <!-- TEXT MESSAGE SETTINGS -->
        <div class="section-header-bar" style="margin-top: 0;">Text Message Settings</div>
        
        <div class="form-group">
            <label class="form-label">Checkin text:</label>
            <input name="checkin_text" value="{{ $text['checkin_text'] ?? '' }}" class="form-input">
        </div>

        <div class="form-group">
            <label class="form-label">Checkout text:</label>
            <input name="checkout_text" value="{{ $text['checkout_text'] ?? '' }}" class="form-input">
        </div>

        <div class="form-group">
            <label class="form-label">Checkin interval seconds:</label>
            <input name="checkin_interval_seconds" type="number" value="{{ $text['checkin_interval_seconds'] ?? '' }}" class="form-input" style="width: 80px;">
        </div>

        <div class="form-group">
            <label class="form-label">Checkout interval seconds:</label>
            <input name="checkout_interval_seconds" type="number" value="{{ $text['checkout_interval_seconds'] ?? '' }}" class="form-input" style="width: 80px;">
        </div>

        <div class="form-group">
            <div class="checkbox-wrapper">
                <input type="checkbox" name="checkin_active" value="1" {{ ($text['checkin_active'] ?? false) ? 'checked' : '' }} class="checkbox-input">
                <label class="form-label" style="margin:0;">Checkin active</label>
            </div>
        </div>

        <div class="form-group">
            <div class="checkbox-wrapper">
                <input type="checkbox" name="checkout_active" value="1" {{ ($text['checkout_active'] ?? false) ? 'checked' : '' }} class="checkbox-input">
                <label class="form-label" style="margin:0;">Checkout active</label>
            </div>
        </div>

        <!-- VOICE SETTINGS -->
        <div class="section-header-bar">Voice Settings</div>
        
        <div class="form-group">
            <label class="form-label">Default language:</label>
            <div>
                <input name="default_language" value="{{ $voice['default_language'] ?? '' }}" class="form-input">
                <div style="font-size: 11px; color: var(--text-secondary); margin-top: 4px;">Primary TTS locale (e.g., en-US, bn-BD).</div>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Additional languages:</label>
            <div>
                <textarea name="additional_languages" class="form-textarea">{{ isset($voice['additional_languages']) ? json_encode($voice['additional_languages']) : '' }}</textarea>
                <div style="font-size: 11px; color: var(--text-secondary); margin-top: 4px;">List of fallback locales by priority order (JSON array or comma-separated).</div>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Speech rate:</label>
            <div>
                <input name="speech_rate" type="number" step="0.1" value="{{ $voice['speech_rate'] ?? '' }}" class="form-input" style="width: 80px;">
                <div style="font-size: 11px; color: var(--text-secondary); margin-top: 4px;">TTS speech rate (1.0 = normal).</div>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Pitch:</label>
            <div>
                <input name="pitch" type="number" step="0.1" value="{{ $voice['pitch'] ?? '' }}" class="form-input" style="width: 80px;">
                <div style="font-size: 11px; color: var(--text-secondary); margin-top: 4px;">TTS pitch (1.0 = normal).</div>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Voice mode:</label>
            <div>
                <select name="voice_mode" class="form-input" style="width: 150px;">
                    <option value="Female Clear" {{ ($voice['voice_mode'] ?? '') == 'Female Clear' ? 'selected' : '' }}>Female Clear</option>
                    <option value="Male" {{ ($voice['voice_mode'] ?? '') == 'Male' ? 'selected' : '' }}>Male</option>
                </select>
                <div style="font-size: 11px; color: var(--text-secondary); margin-top: 4px;">Optional mode hint for the client.</div>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Voice repeat delay seconds:</label>
            <div>
                <input name="voice_repeat_delay_seconds" type="number" step="0.1" value="{{ $voice['voice_repeat_delay_seconds'] ?? '' }}" class="form-input" style="width: 80px;">
                <div style="font-size: 11px; color: var(--text-secondary); margin-top: 4px;">Delay in seconds between repeated voice prompts.</div>
            </div>
        </div>
        
        <div class="form-group">
            <div class="checkbox-wrapper">
                <input type="checkbox" name="voice_message_active" value="1" {{ ($voice['voice_message_active'] ?? false) ? 'checked' : '' }} class="checkbox-input">
                <label class="form-label" style="margin:0;">Voice active</label>
            </div>
        </div>

        <div class="section-header-bar">Name Format</div>

        <div class="form-group">
            <label class="form-label">Name format:</label>
            <div>
                <select name="name_format" class="form-input" style="width: 150px;">
                    <option value="Full Name" {{ ($voice['name_format'] ?? '') == 'Full Name' ? 'selected' : '' }}>Full Name</option>
                    <option value="First Name" {{ ($voice['name_format'] ?? '') == 'First Name' ? 'selected' : '' }}>First Name</option>
                </select>
                <div style="font-size: 11px; color: var(--text-secondary); margin-top: 4px;">How names should be spoken in voice prompts.</div>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Custom name template:</label>
            <div>
                <input name="custom_name_template" value="{{ $voice['custom_name_template'] ?? '' }}" class="form-input">
                <div style="font-size: 11px; color: var(--text-secondary); margin-top: 4px;">Use {first}, {last}, {full} placeholders when name_format is custom.</div>
            </div>
        </div>

        <div class="meta-info">
            Updated at: Dec. 8, 2025, 4:16 p.m.
        </div>

        <!-- Overrides Sections -->
        <h3 class="section-title">Voice name overrides</h3>
        <table class="light-table">
            <thead><tr><th>EMPLOYEE</th><th>LANGUAGE CODE</th><th>SPOKEN NAME</th><th>DELETE</th></tr></thead>
            <tbody>
            @foreach($nameOverrides as $idx => $o)
                <tr>
                    <td><input name="voice_name_overrides[{{ $idx }}][employee_id]" value="{{ $o['employee_id'] ?? '' }}"></td>
                    <td><input name="voice_name_overrides[{{ $idx }}][language_code]" value="{{ $o['language_code'] ?? '' }}"></td>
                    <td><input name="voice_name_overrides[{{ $idx }}][spoken_name]" value="{{ $o['spoken_name'] ?? '' }}"></td>
                    <td style="text-align:center;"><input type="checkbox"></td>
                </tr>
            @endforeach
            <!-- Empty row for adding -->
            @php $newIdx = count($nameOverrides); @endphp
            <tr>
                <td><input name="voice_name_overrides[{{ $newIdx }}][employee_id]" placeholder="Add new..."></td>
                <td><input name="voice_name_overrides[{{ $newIdx }}][language_code]"></td>
                <td><input name="voice_name_overrides[{{ $newIdx }}][spoken_name]"></td>
                <td></td>
            </tr>
            </tbody>
        </table>
        <button type="button" class="btn-add-override">Add voice name override</button>

        <h3 class="section-title">Voice phrase overrides</h3>
        <table class="light-table">
            <thead><tr><th>LANGUAGE CODE</th><th>CHECKIN PHRASE</th><th>CHECKOUT PHRASE</th><th>ACTIVE</th><th>DELETE</th></tr></thead>
            <tbody>
            @foreach($phraseOverrides as $idx => $p)
                <tr>
                    <td><input name="voice_phrase_overrides[{{ $idx }}][language_code]" value="{{ $p['language_code'] ?? '' }}"></td>
                    <td><input name="voice_phrase_overrides[{{ $idx }}][checkin_phrase]" value="{{ $p['checkin_phrase'] ?? '' }}"></td>
                    <td><input name="voice_phrase_overrides[{{ $idx }}][checkout_phrase]" value="{{ $p['checkout_phrase'] ?? '' }}"></td>
                    <td style="text-align:center;"><input type="checkbox" name="voice_phrase_overrides[{{ $idx }}][is_active]" value="1" {{ ($p['is_active'] ?? false) ? 'checked' : '' }}></td>
                    <td style="text-align:center;"><input type="checkbox"></td>
                </tr>
            @endforeach
             <!-- Empty row for adding -->
             @php $newIdx = count($phraseOverrides); @endphp
             <tr>
                <td><input name="voice_phrase_overrides[{{ $newIdx }}][language_code]" placeholder="Add new..."></td>
                <td><input name="voice_phrase_overrides[{{ $newIdx }}][checkin_phrase]"></td>
                <td><input name="voice_phrase_overrides[{{ $newIdx }}][checkout_phrase]"></td>
                <td style="text-align:center;"><input type="checkbox" name="voice_phrase_overrides[{{ $newIdx }}][is_active]" value="1" checked></td>
                <td></td>
            </tr>
            </tbody>
        </table>
        <button type="button" class="btn-add-override">Add voice phrase override</button>
        
        <h3 class="section-title">Voice preferences</h3>
        <table class="light-table">
            <thead><tr><th>EMPLOYEE</th><th>LANGUAGE CODE</th><th>DELETE</th></tr></thead>
            <tbody>
            @foreach($prefs as $idx => $pref)
                <tr>
                    <td><input name="voice_preferences[{{ $idx }}][employee_id]" value="{{ $pref['employee_id'] ?? '' }}"></td>
                    <td><input name="voice_preferences[{{ $idx }}][language_code]" value="{{ $pref['language_code'] ?? '' }}"></td>
                    <td style="text-align:center;"><input type="checkbox"></td>
                </tr>
            @endforeach
            <!-- Empty row for adding -->
            @php $newIdx = count($prefs); @endphp
            <tr>
                <td><input name="voice_preferences[{{ $newIdx }}][employee_id]" placeholder="Add new..."></td>
                <td><input name="voice_preferences[{{ $newIdx }}][language_code]"></td>
                <td></td>
            </tr>
            </tbody>
        </table>
        <button type="button" class="btn-add-override">Add voice preference</button>

        <div class="action-bar">
            <button type="submit" class="btn-save">SAVE</button>
            <button type="submit" class="btn-save">Save and continue editing</button>
            <button type="button" class="btn-delete">Delete</button>
        </div>
    </form>
</div>
@endsection
