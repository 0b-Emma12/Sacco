@extends('layouts.app')
@section('title', 'Add New Member')

@section('content')
<div class="flex flex-col gap-6 mx-auto w-full max-w-4xl">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-2">
        <div>
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Members</div>
            <h1 class="text-2xl font-semibold text-slate-800 dark:text-slate-100">Add New Member</h1>
        </div>
        <a href="{{ route('members.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">← Back to Directory</a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 dark:bg-slate-900 dark:border-slate-800 p-6 md:p-8">
        
        @if ($errors->any())
            <div class="bg-rose-50 text-rose-600 p-4 rounded-md mb-6 text-sm">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('members.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Personal Info -->
                <div class="space-y-4">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 border-b pb-2">Personal Information</h3>
                    
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">First Name</label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}" class="w-full border border-slate-200 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none" required>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Last Name</label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}" class="w-full border border-slate-200 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none" required>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Date of Birth</label>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" class="w-full border border-slate-200 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none" required>
                    </div>
                </div>

                <!-- Identity & Contact -->
                <div class="space-y-4">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 border-b pb-2">Identity & Contact</h3>
                    
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">National ID</label>
                        <input type="text" name="national_id" value="{{ old('national_id') }}" class="w-full border border-slate-200 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none" required>
                    </div>
                    


                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Phone Number</label>
                        <input type="text" name="phone_number" value="{{ old('phone_number') }}" class="w-full border border-slate-200 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none" required>
                    </div>
                </div>
            </div>

            <!-- Location -->
            <div class="space-y-4 pt-4">
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 border-b pb-2">Location Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Country</label>
                        <input type="text" name="country" value="{{ old('country', 'Uganda') }}" class="w-full border border-slate-200 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">County/District</label>
                        <input type="text" name="county" value="{{ old('county') }}" class="w-full border border-slate-200 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">City</label>
                        <input type="text" name="city" value="{{ old('city') }}" class="w-full border border-slate-200 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none" required>
                    </div>
                </div>
            </div>

            <!-- Next of Kin -->
            <div class="space-y-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 border-b pb-2">Next of Kin</h3>
                <div id="nok-container" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-slate-50 p-4 rounded-lg border border-slate-100 dark:bg-slate-800/50 dark:border-slate-800">
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Full Name</label>
                            <input type="text" name="next_of_kin[0][name]" class="w-full border border-slate-200 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Relationship</label>
                            <input type="text" name="next_of_kin[0][relationship]" class="w-full border border-slate-200 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Phone Number</label>
                            <input type="text" name="next_of_kin[0][phone_number]" class="w-full border border-slate-200 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">National ID</label>
                            <input type="text" name="next_of_kin[0][national_id]" class="w-full border border-slate-200 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none">
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-6 flex justify-end gap-3">
                <a href="{{ route('members.index') }}" class="px-4 py-2 text-sm font-medium text-slate-600 bg-slate-100 rounded hover:bg-slate-200 transition">Cancel</a>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-sacco rounded hover:bg-sacco-dark transition">Save Member</button>
            </div>
        </form>
    </div>
</div>
@endsection
