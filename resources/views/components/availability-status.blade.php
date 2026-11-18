@props(['site'])
@if($site->availability_error)
    @php($connectionFailed = str_contains($site->availability_error, '(cURL ') || $site->availability_error === 'El sitio no respondió por HTTP o HTTPS desde el Panel.')
    <x-health-badge state="error">{{ $connectionFailed ? 'Sin conexión desde el Panel' : 'No disponible' }}</x-health-badge>
@elseif($site->availability_checked_at)
    <x-health-badge state="ok">Disponible</x-health-badge>
@else
    <x-health-badge>Sin chequear</x-health-badge>
@endif
@if($site->availability_checked_at)
    <small><x-relative-time :at="$site->availability_checked_at" /></small>
@endif
