<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HelpController extends Controller
{
    public function index() { return view('help.index'); }
    public function create() { return view('help.create'); }
    public function store(Request $request) { return redirect()->route('help.index')->with('success', 'Created successfully'); }
    public function show($id) { return view('help.show', compact('id')); }
    public function edit($id) { return view('help.edit', compact('id')); }
    public function update(Request $request, $id) { return redirect()->route('help.index')->with('success', 'Updated successfully'); }
    public function destroy($id) { return redirect()->route('help.index')->with('success', 'Deleted successfully'); }
}
