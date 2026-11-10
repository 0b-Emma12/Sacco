@extends('layouts.app')

@section('title', 'Edit Member')

@section('content')
<div class="flex flex-col gap-6 mx-auto w-full max-w-7xl">
    
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-2">
        <div>
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Members / Edit</div>
            <h1 class="text-2xl font-semibold text-slate-800 dark:text-slate-100">Edit Member Details</h1>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('members.show', $member->id) }}" class="flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded hover:bg-slate-50 transition dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Details
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="bg-[#FF4D6B]/10 text-[#FF4D6B] border border-[#FF4D6B]/20 p-4 rounded-xl text-sm font-semibold">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('members.update', $member->id) }}" class="space-y-8">
        @csrf
        @method('PUT')

        <!-- Personal Details -->
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
            <h2 class="text-lg font-medium text-slate-800 dark:text-slate-100 mb-4">Personal Details</h2>
            <hr class="border-slate-200 dark:border-slate-800 mb-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Salutation</label>
                    <select name="salutation" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sacco/50 dark:bg-slate-900 dark:border-slate-700">
                        @foreach(['Mr', 'Mrs', 'Ms', 'Dr', 'Prof', 'Rev'] as $salutation)
                            <option value="{{ $salutation }}" {{ old('salutation', $member->salutation) === $salutation ? 'selected' : '' }}>{{ $salutation }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">First Name</label>
                    <input type="text" name="first_name" value="{{ old('first_name', $member->first_name) }}" required class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sacco/50 dark:bg-slate-900 dark:border-slate-700">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Middle Name</label>
                    <input type="text" name="middle_name" value="{{ old('middle_name', $member->middle_name) }}" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sacco/50 dark:bg-slate-900 dark:border-slate-700">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Last Name</label>
                    <input type="text" name="last_name" value="{{ old('last_name', $member->last_name) }}" required class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sacco/50 dark:bg-slate-900 dark:border-slate-700">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">National ID</label>
                    <input type="text" name="national_id" value="{{ old('national_id', $member->national_id) }}" required class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sacco/50 dark:bg-slate-900 dark:border-slate-700">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Phone Number</label>
                    <input type="text" name="phone_number" value="{{ old('phone_number', $member->phone_number) }}" required class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sacco/50 dark:bg-slate-900 dark:border-slate-700">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $member->email) }}" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sacco/50 dark:bg-slate-900 dark:border-slate-700">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Date of Birth</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $member->date_of_birth) }}" required class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sacco/50 dark:bg-slate-900 dark:border-slate-700">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">KRA PIN</label>
                    <input type="text" name="kra_pin" value="{{ old('kra_pin', $member->kra_pin) }}" required class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sacco/50 dark:bg-slate-900 dark:border-slate-700">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status</label>
                    <select name="status" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sacco/50 dark:bg-slate-900 dark:border-slate-700">
                        @foreach(['Active', 'Closed', 'Dormant', 'Suspended', 'Pending'] as $status)
                            <option value="{{ $status }}" {{ old('status', $member->status) === $status ? 'selected' : '' }}>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Address -->
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
            <h2 class="text-lg font-medium text-slate-800 dark:text-slate-100 mb-4">Address</h2>
            <hr class="border-slate-200 dark:border-slate-800 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Country</label>
                    <input type="text" name="country" value="{{ old('country', $member->country) }}" required class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sacco/50 dark:bg-slate-900 dark:border-slate-700">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">County</label>
                    <input type="text" name="county" value="{{ old('county', $member->county) }}" required class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sacco/50 dark:bg-slate-900 dark:border-slate-700">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">City</label>
                    <input type="text" name="city" value="{{ old('city', $member->city) }}" required class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sacco/50 dark:bg-slate-900 dark:border-slate-700">
                </div>
            </div>
        </div>

        <!-- Employment Details -->
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
            <h2 class="text-lg font-medium text-slate-800 dark:text-slate-100 mb-4">Employment Details</h2>
            <hr class="border-slate-200 dark:border-slate-800 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6" id="employment-section">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Employment Type</label>
                    <select name="employment[employment_type]" id="employment_type" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sacco/50 dark:bg-slate-900 dark:border-slate-700">
                        @php $empType = old('employment.employment_type', $member->employmentDetail->employment_type ?? 'EMPLOYED'); @endphp
                        <option value="EMPLOYED" {{ $empType === 'EMPLOYED' ? 'selected' : '' }}>Employed</option>
                        <option value="SELF_EMPLOYED" {{ $empType === 'SELF_EMPLOYED' ? 'selected' : '' }}>Self Employed</option>
                        <option value="BUSINESS" {{ $empType === 'BUSINESS' ? 'selected' : '' }}>Business Owner</option>
                        <option value="UNEMPLOYED" {{ $empType === 'UNEMPLOYED' ? 'selected' : '' }}>Unemployed</option>
                    </select>
                </div>
                <div class="emp-field employed-only">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Employer Name</label>
                    <input type="text" name="employment[employer_name]" value="{{ old('employment.employer_name', $member->employmentDetail->employer_name ?? '') }}" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sacco/50 dark:bg-slate-900 dark:border-slate-700">
                </div>
                <div class="emp-field employed-only self-employed-only">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Job Title</label>
                    <input type="text" name="employment[job_title]" value="{{ old('employment.job_title', $member->employmentDetail->job_title ?? '') }}" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sacco/50 dark:bg-slate-900 dark:border-slate-700">
                </div>
                <div class="emp-field not-unemployed">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Monthly Income</label>
                    <input type="number" step="0.01" name="employment[monthly_income]" value="{{ old('employment.monthly_income', $member->employmentDetail->monthly_income ?? '') }}" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sacco/50 dark:bg-slate-900 dark:border-slate-700">
                </div>
                <div class="emp-field business-only hidden">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Business Name</label>
                    <input type="text" name="employment[business_name]" value="{{ old('employment.business_name', $member->employmentDetail->business_name ?? '') }}" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sacco/50 dark:bg-slate-900 dark:border-slate-700">
                </div>
                <div class="emp-field business-only hidden">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Business Type</label>
                    <input type="text" name="employment[business_type]" value="{{ old('employment.business_type', $member->employmentDetail->business_type ?? '') }}" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sacco/50 dark:bg-slate-900 dark:border-slate-700">
                </div>
            </div>
        </div>

        <!-- Next of Kin -->
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
            <h2 class="text-lg font-medium text-slate-800 dark:text-slate-100 mb-4">Next Of Kin Details</h2>
            <hr class="border-slate-200 dark:border-slate-800 mb-6">
            <div id="nok-container" class="space-y-4">
                @php $noks = old('next_of_kin', $member->nextOfKins->toArray()); @endphp
                @if(empty($noks))
                    @php $noks = [[]]; @endphp
                @endif
                
                @foreach($noks as $index => $nok)
                <div class="nok-item flex items-center gap-4">
                    <div class="flex-grow grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Name</label>
                            <input type="text" name="next_of_kin[{{ $index }}][name]" value="{{ $nok['name'] ?? '' }}" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm dark:bg-slate-900 dark:border-slate-700">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Relationship</label>
                            <input type="text" name="next_of_kin[{{ $index }}][relationship]" value="{{ $nok['relationship'] ?? '' }}" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm dark:bg-slate-900 dark:border-slate-700">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Phone</label>
                            <input type="text" name="next_of_kin[{{ $index }}][phone_number]" value="{{ $nok['phone_number'] ?? '' }}" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm dark:bg-slate-900 dark:border-slate-700">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">National ID</label>
                            <input type="text" name="next_of_kin[{{ $index }}][national_id]" value="{{ $nok['national_id'] ?? '' }}" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm dark:bg-slate-900 dark:border-slate-700">
                        </div>
                    </div>
                    <button type="button" class="remove-nok mt-6 text-slate-400 hover:text-[#FF4D6B] transition" {{ count($noks) === 1 ? 'style=display:none' : '' }}>
                        <i data-lucide="x-circle" class="w-5 h-5"></i>
                    </button>
                </div>
                @endforeach
            </div>
            <button type="button" id="add-nok" class="mt-4 flex items-center gap-1.5 text-sm font-semibold text-[#38CE3C] hover:text-[#2cb030] transition">
                <i data-lucide="plus-circle" class="w-4 h-4"></i> Add another Next of kin
            </button>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('members.show', $member->id) }}" class="px-5 py-2.5 bg-white text-slate-700 border border-slate-200 rounded-xl hover:bg-slate-50 text-sm font-semibold transition dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300">Cancel</a>
            <button type="submit" class="px-5 py-2.5 bg-[#38CE3C] hover:bg-[#2cb030] text-white rounded-xl shadow-sm text-sm font-semibold transition">Save Changes</button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const empTypeSelect = document.getElementById('employment_type');
        const empFields = document.querySelectorAll('.emp-field');
        
        function updateEmpFields() {
            const val = empTypeSelect.value;
            empFields.forEach(f => f.classList.add('hidden'));
            
            if (val === 'EMPLOYED') {
                document.querySelectorAll('.employed-only').forEach(f => f.classList.remove('hidden'));
                document.querySelectorAll('.not-unemployed').forEach(f => f.classList.remove('hidden'));
            } else if (val === 'SELF_EMPLOYED') {
                document.querySelectorAll('.self-employed-only').forEach(f => f.classList.remove('hidden'));
                document.querySelectorAll('.not-unemployed').forEach(f => f.classList.remove('hidden'));
            } else if (val === 'BUSINESS') {
                document.querySelectorAll('.business-only').forEach(f => f.classList.remove('hidden'));
                document.querySelectorAll('.not-unemployed').forEach(f => f.classList.remove('hidden'));
            }
        }
        
        if(empTypeSelect) {
            empTypeSelect.addEventListener('change', updateEmpFields);
            updateEmpFields();
        }

        const addNokBtn = document.getElementById('add-nok');
        const nokContainer = document.getElementById('nok-container');
        
        addNokBtn.addEventListener('click', () => {
            const items = nokContainer.querySelectorAll('.nok-item');
            const index = items.length;
            const tpl = `
                <div class="nok-item flex items-center gap-4 mt-4">
                    <div class="flex-grow grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Name</label>
                            <input type="text" name="next_of_kin[${index}][name]" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm dark:bg-slate-900 dark:border-slate-700">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Relationship</label>
                            <input type="text" name="next_of_kin[${index}][relationship]" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm dark:bg-slate-900 dark:border-slate-700">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Phone</label>
                            <input type="text" name="next_of_kin[${index}][phone_number]" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm dark:bg-slate-900 dark:border-slate-700">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">National ID</label>
                            <input type="text" name="next_of_kin[${index}][national_id]" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm dark:bg-slate-900 dark:border-slate-700">
                        </div>
                    </div>
                    <button type="button" class="remove-nok mt-6 text-slate-400 hover:text-[#FF4D6B] transition">
                        <i data-lucide="x-circle" class="w-5 h-5"></i>
                    </button>
                </div>
            `;
            nokContainer.insertAdjacentHTML('beforeend', tpl);
            updateRemoveButtons();
            
            // re-initialize lucide icons if window.lucide exists
            if (window.lucide) {
                window.lucide.createIcons();
            }
        });

        nokContainer.addEventListener('click', (e) => {
            const btn = e.target.closest('.remove-nok');
            if (btn) {
                btn.closest('.nok-item').remove();
                updateRemoveButtons();
            }
        });

        function updateRemoveButtons() {
            const items = nokContainer.querySelectorAll('.nok-item');
            const btns = nokContainer.querySelectorAll('.remove-nok');
            btns.forEach(btn => {
                btn.style.display = items.length === 1 ? 'none' : 'block';
            });
        }
    });
</script>
@endsection
