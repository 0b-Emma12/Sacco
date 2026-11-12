@props(['eyebrow' => null, 'title', 'subtitle' => null])
<div {{ $attributes->merge(['class' => 'flex flex-col sm:flex-row sm:items-end justify-between gap-4']) }}>
    <div>
        @if($eyebrow)
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">{{ $eyebrow }}</div>
        @endif
        <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">{{ $title }}</h1>
        @if($subtitle)
            <p class="text-sm text-slate-500 mt-1">{{ $subtitle }}</p>
        @endif
    </div>
    @if(trim($slot) !== '')
        <div class="flex flex-wrap items-center gap-2">
            {{ $slot }}
        </div>
    @endif
</div>
