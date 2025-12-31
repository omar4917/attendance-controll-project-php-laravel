<?php

namespace App\Http\Controllers;

use App\Services\DjangoApi;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(DjangoApi $api)
    {
        $data = $api->subscriptionPlans();
        $plans = $data['plans'] ?? [];
        return view('plans.index', compact('plans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('plans.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, DjangoApi $api)
    {
        $payload = [
            'name' => $request->input('name'),
            'slug' => \Illuminate\Support\Str::slug($request->input('name')),
            'description' => $request->input('description'),
            'max_employees' => $request->input('max_employees'),
            'max_devices' => $request->input('max_devices'),
            'price_monthly' => $request->input('price_monthly'),
            'price_yearly' => $request->input('price_yearly'),
            'is_active' => $request->has('is_active'),
            'is_default' => $request->has('is_default'),
            'features' => [], // TODO: Add feature management if needed
        ];

        // We need to implement createPlan in DjangoApi or use send('POST', ...)
        // Since createPlan doesn't exist in DjangoApi yet, let's use send directly or add it.
        // DjangoApi has subscriptionPlans() which calls GET /api/subscription-plans/
        // Usually POST /api/subscription-plans/ creates one.
        
        // Let's rely on standard REST principles if the Django view supports it.
        // Looking at urls.py: path('api/subscription-plans/', views.subscription_plans_api, name='subscription_plans_api'),
        // I need to check if that view handles POST.
        
        // For now, I'll assume I can send a POST request to /api/subscription-plans/
        // I'll add a specific method to DjangoApi for clarity later, or use extended functionality.
        // Actually, let's add upsertPlan to DjangoApi if I can, or just use the generic send method via a helper in this controller?
        // No, I should stick to the pattern. I'll just use a direct call pattern via a new method in DjangoApi if I could edit it,
        // but I prefer editing DjangoApi to keep things clean.
        
        // Wait, I can't edit DjangoApi easily without viewing it all and replacing sections.
        // I'll just use the `send` method from the controller? No, `send` is protected.
        // I'll implement `upsertPlan` in `DjangoApi` first using replace_file_content.
        
        // For now, let's create the controller assuming the method exists in DjangoApi.
        // I'll add `upsertPlan` and `deletePlan` to DjangoApi in a moment.
        
        $resp = $api->upsertPlan($payload);

        if (!empty($resp['error'])) {
            return redirect()->back()->withInput()->with('error', $resp['error']);
        }

        return redirect()->route('plans.index')->with('success', 'Plan created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id, DjangoApi $api)
    {
        // We can fetch the list and find the one, or fetch specific if API supports it.
        // The API returns a list.
        $data = $api->subscriptionPlans();
        $plans = $data['plans'] ?? [];
        $plan = collect($plans)->firstWhere('id', $id);
        
        if (!$plan) {
            return redirect()->route('plans.index')->with('error', 'Plan not found');
        }

        return view('plans.edit', compact('plan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id, DjangoApi $api)
    {
        $payload = [
            'id' => $id,
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'max_employees' => $request->input('max_employees'),
            'max_devices' => $request->input('max_devices'),
            'price_monthly' => $request->input('price_monthly'),
            'price_yearly' => $request->input('price_yearly'),
            'is_active' => $request->has('is_active'),
            'is_default' => $request->has('is_default'),
        ];
        
        $resp = $api->upsertPlan($payload);
        
        if (!empty($resp['error'])) {
            return redirect()->back()->withInput()->with('error', $resp['error']);
        }

        return redirect()->route('plans.index')->with('success', 'Plan updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id, DjangoApi $api)
    {
        $resp = $api->deletePlan($id);
        
        if (!empty($resp['error'])) {
            return redirect()->back()->with('error', $resp['error']);
        }
        
        return redirect()->route('plans.index')->with('success', 'Plan deleted successfully.');
    }
}
