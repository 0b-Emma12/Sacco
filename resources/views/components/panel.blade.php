@props(['title' => null, 'subtitle' => null, 'icon' => null, 'tone' => 'green'])
@php
    $tones = [
        'green' => 'bg-[#38CE3C]/10 text-[#38CE3C]',
        'purple' => 'bg-[#8E32E9]/10 text-[#8E32E9]',
        'coral' => 'bg-[#FF4D6B]/10 text-[#FF4D6B]',
        'amber' => 'bg-[#FFDE73]/30 text-amber-600 dark:text-amber-300',
        'blue' => 'bg-blue-500/10 text-blue-500',
        'slate' => 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-300',
    ];
@endphp
<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden dark:bg-[#181824] dark:border-[#272738]']) }}>
    @if($title)
        <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-3">
                @if($icon)
                    <div class="h-9 w-9 rounded-xl {{ $tones[$tone] ?? $tones['green'] }} flex items-center justify-center shrink-0">
                        <x-icon :name="$icon" class="w-4 h-4"/>
                    </div>
                @endif
                <div>
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">{{ $title }}</h3>
                    @if($subtitle)
                        <p class="text-xs text-slate-400 mt-0.5">{{ $subtitle }}</p>
                    @endif
                </div>
            </div>
            @isset($actions)
                <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
            @endisset
        </div>
    @endif
    {{ $slot }}
</div>
