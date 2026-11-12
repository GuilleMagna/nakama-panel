@props(['site'])
@if($site->availability_error)
    <x-health-badge state="error">No disponible</x-health-badge>
@elseif($site->availability_checked_at)
    <x-health-badge state="ok">Disponible</x-health-badge>
@else
    <x-health-badge>Sin chequear</x-health-badge>
@endif
@if($site->availability_checked_at)
    <small>{{ $site->availability_checked_at->format('d/m H:i') }}</small>
@endif
