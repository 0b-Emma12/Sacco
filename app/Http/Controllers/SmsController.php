<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SmsController extends Controller
{
    public function index() { return view('sms.index'); }
    public function create() { return view('sms.create'); }
    public function store(Request $request) { return redirect()->route('sms.index')->with('success', 'Created successfully'); }
    public function show($id) { return view('sms.show', compact('id')); }
    public function edit($id) { return view('sms.edit', compact('id')); }
    public function update(Request $request, $id) { return redirect()->route('sms.index')->with('success', 'Updated successfully'); }
    public function destroy($id) { return redirect()->route('sms.index')->with('success', 'Deleted successfully'); }
}
