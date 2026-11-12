@php($snapshot = $site->monitor_snapshot)
<section class="panel form-stack" id="monitor">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
        <h2>Actualizaciones de WordPress</h2>
        @if($site->getRawOriginal('monitor_token'))
            <form method="POST" action="{{ route('sites.monitor.check', $site) }}">
                @csrf
                <button type="submit" class="button primary">Consultar ahora</button>
            </form>
        @endif
    </div>
    @if($site->monitor_error)
        <div class="notice error" role="status">{{ $site->monitor_error }} @if($snapshot) Se muestran los datos de la última consulta exitosa. @endif</div>
    @endif
    @if($snapshot)
        <p class="muted">Última consulta exitosa {{ $site->monitor_checked_at?->format('d/m/Y H:i') }} · Nakama Monitor {{ $snapshot['monitor_version'] }}</p>
        <nav aria-label="Filtrar actualizaciones" style="display:flex;gap:8px;flex-wrap:wrap">
            @foreach(['all' => 'Todas', 'available' => 'Disponible', 'current' => 'Sin pendientes'] as $filter => $label)
                <a class="button {{ $updateFilter === $filter ? 'primary' : 'secondary' }}"
                    href="{{ route('sites.show', ['site' => $site, 'updates' => $filter]) }}#monitor"
                    @if($updateFilter === $filter) aria-current="true" @endif>{{ $label }}</a>
            @endforeach
        </nav>
        <div class="table-scroll">
            <table>
                <thead><tr><th>Componente</th><th>Versión instalada</th><th>Actualización</th></tr></thead>
                <tbody>
                    @forelse($updateItems as $item)
                        <tr>
                            <td>{{ $item['name'] }}</td>
                            <td>{{ $item['version'] ?: '—' }}</td>
                            <td>
                                <x-health-badge :state="$item['update_available'] ? 'updates' : 'ok'">
                                    {{ $item['update_available'] ? 'Disponible' : 'Sin pendientes' }}
                                    @if($item['update_available'] && $item['new_version']) · {{ $item['new_version'] }} @endif
                                </x-health-badge>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3">{{ $updateFilter === 'available' ? 'No hay actualizaciones disponibles.' : 'No hay componentes sin pendientes.' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @elseif(! $site->getRawOriginal('monitor_token'))
        <p class="muted">Guardá el token de Nakama Monitor en <a class="text-link" href="{{ route('sites.edit', $site) }}">Editar sitio</a>.</p>
    @elseif(! $site->monitor_error)
        <p class="muted">Todavía no se consultó este sitio.</p>
    @endif
</section>
