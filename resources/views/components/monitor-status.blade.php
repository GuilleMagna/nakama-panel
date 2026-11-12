@props(['site'])
@php($pending = $site->pendingUpdates())
@if($site->monitor_error)
    <x-health-badge state="error">Falló la conexión</x-health-badge>
@elseif($pending !== null)
    <x-health-badge :state="$pending === 0 ? 'ok' : 'updates'">
        {{ $pending === 0 ? 'Sin pendientes' : ($pending === 1 ? '1 pendiente' : $pending.' pendientes') }}
    </x-health-badge>
@else
    <x-health-badge>{{ $site->getRawOriginal('monitor_token') ? 'Sin consultar' : 'Sin token' }}</x-health-badge>
@endif
@if($site->monitor_checked_at)
    <small>Última lectura {{ $site->monitor_checked_at->format('d/m H:i') }}</small>
@endif
