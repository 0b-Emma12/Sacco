@if(! empty($print) && $print->watermark)
    <div class="watermark {{ ! empty($inline) ? 'inline' : '' }}" aria-hidden="true">{{ $print->watermark }}</div>
@endif
