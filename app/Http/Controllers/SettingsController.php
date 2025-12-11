<?php

namespace App\Http\Controllers;

use App\Services\DjangoApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

class SettingsController extends Controller
{




    public function saveVoice(Request $request, DjangoApi $api)
    {
        $payload = $request->only([
            'default_language','additional_languages','name_format','custom_name_template',
            'speech_rate','pitch','voice_mode','voice_repeat_delay_seconds','voice_message_active'
        ]);

        // additional_languages may come as JSON string
        if (isset($payload['additional_languages']) && is_string($payload['additional_languages'])) {
            $decoded = json_decode($payload['additional_languages'], true);
            if (is_array($decoded)) {
                $payload['additional_languages'] = $decoded;
            } else {
                $payload['additional_languages'] = array_filter(array_map('trim', explode(',', $payload['additional_languages'])));
            }
        }

        $payload['voice_name_overrides'] = $this->collectRows(
            $request->input('voice_name_overrides', []),
            ['employee_id','language_code','spoken_name']
        );
        $payload['voice_phrase_overrides'] = $this->collectRows(
            $request->input('voice_phrase_overrides', []),
            ['language_code','checkin_phrase','checkout_phrase','is_active']
        );
        $payload['voice_preferences'] = $this->collectRows(
            $request->input('voice_preferences', []),
            ['employee_id','language_code']
        );

        $resp = $api->saveVoiceSettings($payload);
        if (!empty($resp['error'])) {
            return redirect()->back()->withInput()->with('error', $resp['error']);
        }
        return redirect()->route('settings.voice')->with('success', 'Voice settings saved');
    }

    public function voiceMessage(DjangoApi $api)
    {
        $voiceData = $api->voiceSettings();
        $messageData = $api->messageSettings();
        
        // Merge data, prioritizing message data structure but including voice fields
        $data = [
            'text' => $messageData['text'] ?? [],
            'voice' => $voiceData ?? [], // Voice settings are flat in voiceSettings() response usually, or nested
        ];
        
        // If voiceSettings returns flat data, we might need to structure it or just pass it as is.
        // Let's assume voiceSettings returns the 'voice' part of the config.
        // Actually, looking at previous code, voiceSettings returned $data directly.
        // Let's pass both as 'data' to the view.
        
        $data = [
            'text' => $messageData['text'] ?? [],
            'voice' => $voiceData ?? [],
            'voice_name_overrides' => $voiceData['overrides']['names'] ?? [],
            'voice_phrase_overrides' => $voiceData['overrides']['phrases'] ?? [],
            'voice_preferences' => $voiceData['overrides']['preferences'] ?? [],
        ];

        $error = $voiceData['error'] ?? ($messageData['error'] ?? null);
        $apiServer = Session::get('django_base_url', config('django.base_url'));
        
        return view('settings.message', compact('data', 'error', 'apiServer'));
    }

    public function saveVoiceMessage(Request $request, DjangoApi $api)
    {
        // 1. Save Voice Settings
        $voicePayload = $request->only([
            'default_language','additional_languages','name_format','custom_name_template',
            'speech_rate','pitch','voice_mode','voice_repeat_delay_seconds','voice_message_active'
        ]);

        if (isset($voicePayload['additional_languages']) && is_string($voicePayload['additional_languages'])) {
            $decoded = json_decode($voicePayload['additional_languages'], true);
            if (is_array($decoded)) {
                $voicePayload['additional_languages'] = $decoded;
            } else {
                $voicePayload['additional_languages'] = array_filter(array_map('trim', explode(',', $voicePayload['additional_languages'])));
            }
        }

        $voicePayload['voice_name_overrides'] = $this->collectRows(
            $request->input('voice_name_overrides', []),
            ['employee_id','language_code','spoken_name']
        );
        $voicePayload['voice_phrase_overrides'] = $this->collectRows(
            $request->input('voice_phrase_overrides', []),
            ['language_code','checkin_phrase','checkout_phrase','is_active']
        );
        $voicePayload['voice_preferences'] = $this->collectRows(
            $request->input('voice_preferences', []),
            ['employee_id','language_code']
        );

        $voiceResp = $api->saveVoiceSettings($voicePayload);

        // 2. Save Message Settings
        $messagePayload = [
            'text' => $request->only([
                'checkin_text','checkout_text','checkin_interval_seconds','checkout_interval_seconds',
                'checkin_active','checkout_active','text_message_display'
            ]),
            'voice' => $request->only(['voice_message_active']), // This is shared/duplicated in message settings usually
        ];
        
        // Message settings also takes overrides in some implementations, but we just saved them via voice settings.
        // However, the previous saveMessage method also saved overrides. 
        // To be safe and consistent with the backend API which might expect them in both or either,
        // let's include them here too if the API requires it.
        // Based on previous code, saveMessage DID send overrides.
        
        $messagePayload['voice_name_overrides'] = $voicePayload['voice_name_overrides'];
        $messagePayload['voice_phrase_overrides'] = $voicePayload['voice_phrase_overrides'];
        $messagePayload['voice_preferences'] = $voicePayload['voice_preferences'];

        $messageResp = $api->saveMessageSettings($messagePayload);

        if (!empty($voiceResp['error'])) {
            return redirect()->back()->withInput()->with('error', 'Voice Error: ' . $voiceResp['error']);
        }
        if (!empty($messageResp['error'])) {
            return redirect()->back()->withInput()->with('error', 'Message Error: ' . $messageResp['error']);
        }

        return redirect()->route('settings.voice_message')->with('success', 'Settings saved successfully');
    }

    public function saveApiServer(Request $request)
    {
        $url = rtrim($request->input('api_server'), '/');
        if ($url) {
            Session::put('django_base_url', $url);
            Config::set('django.base_url', $url);
        }
        return redirect()->back()->with('success', 'API server updated to '.$url);
    }

    public function company(DjangoApi $api)
    {
        $data = $api->companyInfo();
        $error = $data['error'] ?? null;
        return view('settings.company', compact('data', 'error'));
    }

    public function saveCompany(Request $request, DjangoApi $api)
    {
        $payload = $request->only([
            'name', 'address', 'email', 'phone', 'website', 'tin', 'bin', 'founder'
        ]);
        $file = $request->file('logo');
        
        $resp = $api->saveCompanyInfo($payload, $file);
        if (!empty($resp['error'])) {
            return redirect()->back()->withInput()->with('error', $resp['error']);
        }
        return redirect()->route('settings.company')->with('success', 'Company info saved');
    }

    public function context(DjangoApi $api)
    {
        $data = $api->contextSettings();
        $error = $data['error'] ?? null;
        return view('settings.context', compact('data', 'error'));
    }

    public function saveContext(Request $request, DjangoApi $api)
    {
        $payload = $request->only(['text_message_display', 'voice_message_active']);
        // Checkboxes not sent if unchecked
        $payload['text_message_display'] = $request->has('text_message_display');
        $payload['voice_message_active'] = $request->has('voice_message_active');

        $resp = $api->saveContextSettings($payload);
        if (!empty($resp['error'])) {
            return redirect()->back()->withInput()->with('error', $resp['error']);
        }
        return redirect()->route('settings.context')->with('success', 'Context settings saved');
    }

    public function integration(DjangoApi $api)
    {
        $data = $api->integrationSettings();
        $error = $data['error'] ?? null;
        return view('settings.integration', compact('data', 'error'));
    }

    public function saveIntegration(Request $request, DjangoApi $api)
    {
        // Handle API Server URL save
        if ($request->has('api_server')) {
            Session::put('django_base_url', rtrim($request->input('api_server'), '/'));
            
            // If only saving API server, return early
            if ($request->has('save_api_server')) {
                return redirect()->route('settings.integration')->with('success', 'API Server URL updated successfully');
            }
        }
        
        $payload = $request->only(['fcm_server_key', 'fcm_service_account_json']);
        $resp = $api->saveIntegrationSettings($payload);
        if (!empty($resp['error'])) {
            return redirect()->back()->withInput()->with('error', $resp['error']);
        }
        return redirect()->route('settings.integration')->with('success', 'Integration settings saved');
    }

    private function collectRows(array $rows, array $fields): array
    {
        $clean = [];
        foreach ($rows as $row) {
            $filtered = [];
            foreach ($fields as $field) {
                if (isset($row[$field]) && $row[$field] !== '') {
                    $filtered[$field] = $row[$field];
                }
            }
            if (!empty($filtered)) {
                $clean[] = $filtered;
            }
        }
        return $clean;
    }
}
