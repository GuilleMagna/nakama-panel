@props(['site'])
@php($pending = $site->pendingUpdates())
@if($site->monitor_error)
    <x-health-badge state="error">Falló la conexión</x-health-badge>
@elseif($pending > 0)
    <a href="{{ route('sites.show', ['site' => $site, 'updates' => 'available']) }}#monitor" aria-label="Ver actualizaciones disponibles de {{ $site->name }}">
        <x-health-badge state="updates">{{ $pending === 1 ? '1 pendiente' : $pending.' pendientes' }}</x-health-badge>
    </a>
@elseif($pending === 0)
    <x-health-badge state="ok">Sin pendientes</x-health-badge>
@else
    <x-health-badge>{{ $site->getRawOriginal('monitor_token') ? 'Sin consultar' : 'Sin token' }}</x-health-badge>
@endif
@if($site->monitor_checked_at)
    <small>Última lectura · <x-relative-time :at="$site->monitor_checked_at" /></small>
@endif
