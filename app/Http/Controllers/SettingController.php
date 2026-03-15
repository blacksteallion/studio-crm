<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Integration;
use App\Models\WhatsappNumber; // <--- Import WhatsApp Model
use App\Models\User;           // <--- Import User/Staff Model
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
        $metaIntegration = Integration::where('platform', 'meta')->first();
        
        // Fetch WhatsApp Numbers & Active Staff for the multi-routing dropdown
        $whatsappNumbers = WhatsappNumber::with('staff')->get();
        $staffMembers = User::where('status', 1)->get(); 

        return view('settings.index', compact('settings', 'metaIntegration', 'whatsappNumbers', 'staffMembers'));
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

        $redirectTab = $request->input('redirect_tab', 'general');
        $data = $request->except(['_token', 'company_logo', 'redirect_tab']);

        foreach ($data as $key => $value) {
            if ($value !== null) {
                Setting::set($key, $value);
            }
        }

        return redirect()->route('settings.index', ['tab' => $redirectTab])
                         ->with('success', 'System settings updated successfully.');
    }

    // --- NEW: Handle Adding a Multi-Routing WhatsApp Number ---
    public function storeWhatsappNumber(Request $request)
    {
        $request->validate([
            'phone_number_id' => 'required|string|unique:whatsapp_numbers',
            'phone_number_name' => 'required|string',
            'access_token' => 'required|string',
            'welcome_template_name' => 'required|string',
            'assigned_staff_id' => 'nullable|exists:users,id',
        ]);

        WhatsappNumber::create($request->all());

        return redirect()->route('settings.index', ['tab' => 'whatsapp'])
                         ->with('success', 'WhatsApp Number added and routed successfully.');
    }

    // --- NEW: Handle Deleting a WhatsApp Number ---
    public function destroyWhatsappNumber($id)
    {
        WhatsappNumber::findOrFail($id)->delete();
        
        return redirect()->route('settings.index', ['tab' => 'whatsapp'])
                         ->with('success', 'WhatsApp Number removed.');
    }
}