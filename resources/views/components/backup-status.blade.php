@props(['site'])

@php($backup = $site->monitor_snapshot['backup'] ?? null)
@if($site->monitor_error)
    <x-health-badge state="error">Falló la conexión</x-health-badge>
@elseif($backup === null)
    <x-health-badge>Sin datos</x-health-badge>
    @if($site->getRawOriginal('monitor_token'))
        <small>Se actualiza con la consulta a Nakama Monitor.</small>
    @else
        <small>Sin token de Nakama Monitor.</small>
    @endif
@elseif(! $backup['installed'])
    <x-health-badge>Sin UpdraftPlus</x-health-badge>
@elseif(! $backup['history_found'])
    <x-health-badge>Sin backups registrados</x-health-badge>
@else
    <x-health-badge :state="$backup['complete'] ? 'ok' : 'updates'">
        {{ $backup['complete'] ? 'Backup completo' : 'Backup parcial' }}
    </x-health-badge>
    <small><x-relative-time :at="\Carbon\CarbonImmutable::parse($backup['last_backup'])" /></small>
@endif
