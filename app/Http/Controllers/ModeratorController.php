<?php

namespace App\Http\Controllers;

use App\Services\DjangoApi;
use Illuminate\Http\Request;

class ModeratorController extends Controller
{
    public function index(Request $request, DjangoApi $api)
    {
        $data = $api->moderatorLabels($request->query());
        $labels = $data['labels'] ?? [];
        $error = $data['error'] ?? null;
        $query = $request->query('q');
        return view('moderator.index', compact('labels', 'error', 'query'));
    }

    public function store(Request $request, DjangoApi $api)
    {
        $payload = $request->only(['key', 'value', 'reset']);
        $resp = $api->saveModeratorLabel($payload);
        if (!empty($resp['error'])) {
            return redirect()->back()->withInput()->with('error', $resp['error']);
        }
        return redirect()->route('moderator.index')->with('success', $resp['message'] ?? 'Label saved');
    }
}
