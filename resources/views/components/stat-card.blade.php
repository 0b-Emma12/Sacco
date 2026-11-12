@props(['label', 'value', 'meta' => null, 'icon' => null, 'tone' => 'green', 'href' => null])
@php
    $tones = [
        'green' => ['bg-[#38CE3C]/10 text-[#38CE3C]', 'bg-[#38CE3C]', 'text-[#38CE3C]'],
        'purple' => ['bg-[#8E32E9]/10 text-[#8E32E9]', 'bg-[#8E32E9]', 'text-[#8E32E9]'],
        'coral' => ['bg-[#FF4D6B]/10 text-[#FF4D6B]', 'bg-[#FF4D6B]', 'text-[#FF4D6B]'],
        'amber' => ['bg-[#FFDE73]/30 text-amber-600 dark:text-amber-300', 'bg-amber-400', 'text-amber-600 dark:text-amber-300'],
        'blue' => ['bg-blue-500/10 text-blue-500', 'bg-blue-500', 'text-blue-500'],
        'slate' => ['bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-300', 'bg-slate-400', 'text-slate-500 dark:text-slate-300'],
    ];
    [$chip, $dot, $accent] = $tones[$tone] ?? $tones['green'];
    $tag = $href ? 'a' : 'div';
@endphp
<{{ $tag }} @if($href) href="{{ $href }}" @endif class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-4 sm:p-5 dark:bg-[#181824] dark:border-[#272738] flex flex-col justify-between hover:shadow-md transition">
    <div class="flex items-center justify-between gap-3">
        <span class="text-[11px] font-bold tracking-wider text-slate-400 uppercase">{{ $label }}</span>
        @if($icon)
            <span class="p-2 rounded-xl {{ $chip }}">
                <x-icon :name="$icon" class="w-4 h-4"/>
            </span>
        @endif
    </div>
    <div class="mt-3">
        <div class="text-lg sm:text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $value }}</div>
        @if($meta)
            <div class="flex items-center gap-1.5 mt-1 text-[11px] font-medium {{ $accent }}">
                <span class="h-1.5 w-1.5 rounded-full {{ $dot }}"></span>
                <span>{{ $meta }}</span>
            </div>
        @endif
    </div>
</{{ $tag }}>
