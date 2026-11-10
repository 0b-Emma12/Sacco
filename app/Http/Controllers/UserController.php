<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index() {
        $users = \App\Models\User::orderBy('created_at', 'desc')->paginate(15);
        return view('users.index', compact('users'));
    }
    public function create() { return view('users.create'); }
    public function store(Request $request) { return redirect()->route('users.index')->with('success', 'Created successfully'); }
    public function show($id) { return view('users.show', compact('id')); }
    public function edit($id) { return view('users.edit', compact('id')); }
    public function update(Request $request, $id) { return redirect()->route('users.index')->with('success', 'Updated successfully'); }
    public function destroy($id) { return redirect()->route('users.index')->with('success', 'Deleted successfully'); }
}
