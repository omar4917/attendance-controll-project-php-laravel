<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class MediaController extends Controller
{
    /**
     * Proxy media files from Django backend
     * This allows Laravel to serve Django media files (logos, employee images, etc.)
     * 
     * Usage: /media/{path}
     * Example: /media/org_logos/tech-solutions.png
     */
    public function proxy(Request $request, $path)
    {
        $djangoBase = rtrim(Session::get('django_base_url', config('django.base_url', 'http://localhost:8001')), '/');
        $mediaUrl = $djangoBase . '/media/' . $path;
        
        try {
            $client = new \GuzzleHttp\Client([
                'verify' => false,
                'timeout' => 30,
            ]);
            
            $response = $client->get($mediaUrl);
            $content = $response->getBody()->getContents();
            $contentType = $response->getHeaderLine('Content-Type') ?: 'application/octet-stream';
            
            return response($content)
                ->header('Content-Type', $contentType)
                ->header('Cache-Control', 'public, max-age=86400'); // Cache for 24 hours
                
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // 404 from Django
            abort(404, 'Media file not found');
        } catch (\Exception $e) {
            \Log::error("Media proxy error: " . $e->getMessage());
            abort(500, 'Failed to fetch media');
        }
    }
}
