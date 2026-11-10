<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

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
        $data = $request->except(['_token', '_method']);
        
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
            Setting::set($key, $value);
        }

        return redirect()->route('settings.index')->with('success', 'Settings updated successfully'); 
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
