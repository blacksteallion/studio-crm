<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Integration; // <--- Import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class SettingController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:manage settings'),
        ];
    }

    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        
        // Fetch Meta Integration status
        $metaIntegration = Integration::where('platform', 'meta')->first();

        return view('settings.index', compact('settings', 'metaIntegration'));
    }

    public function update(Request $request)
    {
        if ($request->hasFile('company_logo')) {
            $request->validate([
                'company_logo' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);
            $path = $request->file('company_logo')->store('logos', 'public');
            Setting::set('company_logo', $path);
        }

        $data = $request->except(['_token', 'company_logo']);

        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        return redirect()->route('settings.index')->with('success', 'System settings updated successfully.');
    }
}