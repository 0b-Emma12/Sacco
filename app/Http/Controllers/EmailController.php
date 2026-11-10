<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EmailController extends Controller
{
    public function index() { return view('emails.index'); }
    public function create() { return view('emails.create'); }
    public function store(Request $request) { return redirect()->route('emails.index')->with('success', 'Created successfully'); }
    public function show($id) { return view('emails.show', compact('id')); }
    public function edit($id) { return view('emails.edit', compact('id')); }
    public function update(Request $request, $id) { return redirect()->route('emails.index')->with('success', 'Updated successfully'); }
    public function destroy($id) { return redirect()->route('emails.index')->with('success', 'Deleted successfully'); }
}
