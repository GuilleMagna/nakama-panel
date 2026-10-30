@props(['status'])
<span class="badge {{ $status === 'active' ? 'green' : '' }}" style="display:inline-flex;align-items:center;gap:6px">
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        @if($status === 'active')
            <circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>
        @elseif($status === 'paused')
            <circle cx="12" cy="12" r="9"/><path d="M9 8v8M15 8v8"/>
        @else
            <path d="M4 9v11h16V9M3 4h18v5H3zM9 13h6"/>
        @endif
    </svg>
    {{ ['active' => 'Activo', 'paused' => 'Pausado', 'archived' => 'Archivado'][$status] ?? $status }}
</span>
