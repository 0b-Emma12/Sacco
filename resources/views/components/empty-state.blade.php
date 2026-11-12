@props(['icon' => 'inbox', 'title' => 'Nothing to show yet', 'message' => null, 'colspan' => null])
@if($colspan)
    <tr>
        <td colspan="{{ $colspan }}" class="px-5 py-16">
@endif
<div class="flex flex-col items-center justify-center text-center px-6 py-10">
    <div class="h-12 w-12 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center mb-3">
        <x-icon :name="$icon" class="w-5 h-5"/>
    </div>
    <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $title }}</p>
    @if($message)
        <p class="text-xs text-slate-400 mt-1 max-w-sm">{{ $message }}</p>
    @endif
    @if(trim($slot) !== '')
        <div class="mt-4">{{ $slot }}</div>
    @endif
</div>
@if($colspan)
        </td>
    </tr>
@endif
