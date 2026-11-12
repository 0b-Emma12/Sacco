@php $signatures = $signatures ?? []; @endphp
@if(count($signatures))
    <div class="sig-grid">
        @foreach($signatures as $signature)
            <div>
                <div class="sig-line"></div>
                <div class="sig-name">{{ $signature['name'] ?? '' }}</div>
                <div class="sig-title">{{ $signature['title'] ?? '' }}</div>
            </div>
        @endforeach
    </div>
@endif
