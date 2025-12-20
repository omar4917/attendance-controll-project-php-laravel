<?php

namespace App\Http\Controllers;

use App\Services\DjangoApi;
use App\Traits\HasOrganizationContext;
use Illuminate\Http\Request;

class LiveFeedController extends Controller
{
    use HasOrganizationContext;

    public function index(Request $request, DjangoApi $api)
    {
        $query = $request->query();
        $orgId = $this->getOrganizationId();
        if ($orgId) {
            $query['organization_id'] = $orgId;
        }
        $data = $api->livefeedList($query);
        $images = $data['images'] ?? [];
        $summary = $data['summary'] ?? [];
        $meta = $data['meta'] ?? [];
        $error = $data['error'] ?? null;
        
        return view('livefeed.index', compact('images', 'summary', 'meta', 'error'));
    }

    public function action(Request $request, DjangoApi $api)
    {
        $payload = $request->all();
        $orgId = $this->getOrganizationId();
        if ($orgId) {
            $payload['organization_id'] = $orgId;
        }
        $resp = $api->livefeedAction($payload);
        
        if (!empty($resp['error'])) {
            return redirect()->back()->with('error', $resp['error']);
        }
        
        return redirect()->back()->with('success', $resp['message'] ?? 'Action completed');
    }

    public function destroy($id, DjangoApi $api)
    {
        $resp = $api->livefeedAction(['action' => 'delete', 'image_id' => $id]);
        if (!empty($resp['error'])) {
            return redirect()->back()->with('error', $resp['error']);
        }
        return redirect()->back()->with('success', 'Image deleted');
    }
}
