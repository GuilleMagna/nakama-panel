@props(['state' => 'waiting'])
@php
    $tone = match ($state) {
        'error' => 'background:#fff0ef;color:#922e25',
        'updates' => 'background:#fff0b3;color:#664d03',
        default => '',
    };
@endphp
<span class="badge {{ $state === 'ok' ? 'green' : '' }}" style="display:inline-flex;align-items:center;gap:6px;{{ $tone }}">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        @if($state === 'ok')
            <circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>
        @elseif($state === 'error')
            <path d="M12 3 2 20h20L12 3zM12 9v4m0 3h.01"/>
        @elseif($state === 'updates')
            <path d="M12 20V4m-6 6 6-6 6 6"/>
        @else
            <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>
        @endif
    </svg>
    {{ $slot }}
</span>
