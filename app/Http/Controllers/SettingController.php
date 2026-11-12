<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index() 
    { 
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        return view('settings.index', compact('settings')); 
    }

    public function create() { return view('settings.create'); }

    public function store(Request $request) 
    { 
        $data = $request->except(['_token', '_method', 'org_logo_file', 'remove_logo']);

        if ($request->hasFile('org_logo_file')) {
            $request->validate([
                'org_logo_file' => 'image|mimes:png,jpg,jpeg|max:2048',
            ]);

            $previous = Setting::get('org_logo');
            if ($previous) {
                Storage::disk('local')->delete($previous);
            }

            Setting::set('org_logo', $request->file('org_logo_file')->store('branding', 'local'), 'organization');
        } elseif ($request->boolean('remove_logo')) {
            $previous = Setting::get('org_logo');
            if ($previous) {
                Storage::disk('local')->delete($previous);
            }
            Setting::set('org_logo', '', 'organization');
        }

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
            Setting::set($key, $value);
        }

        return redirect()->route('settings.index')->with('success', 'Settings updated successfully'); 
    }

    public function logo()
    {
        $path = Setting::get('org_logo');
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }

    public function show($id) { return view('settings.show', compact('id')); }
    
    public function edit($id) { return view('settings.edit', compact('id')); }
    
    public function update(Request $request, $id) 
    { 
        // For individual setting updates if needed, though usually store handles all
        return redirect()->route('settings.index')->with('success', 'Updated successfully'); 
    }
    
    public function destroy($id) 
    { 
        return redirect()->route('settings.index')->with('success', 'Deleted successfully'); 
    }
}
