<section class="panel table-panel" @if(collect($checkBatches)->contains('running', true)) wire:poll.5s @endif>
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;padding:16px">
        <button type="button" class="button secondary" wire:click="startChecks('availability')" wire:loading.attr="disabled" @disabled($checkBatches['availability']['running'] ?? false)>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2 12h5l3-7 4 14 3-7h5"/></svg>
            Verificar todos
        </button>
        <button type="button" class="button secondary" wire:click="startChecks('monitor')" wire:loading.attr="disabled" @disabled($checkBatches['monitor']['running'] ?? false)>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20 7v5h-5M4 17v-5h5M5 9a7.5 7.5 0 0 1 13-3l2 3M4 15l2 3a7.5 7.5 0 0 0 13-3"/></svg>
            Consultar actualizaciones de todos
        </button>
        <small class="muted">Sitios activos</small>
    </div>
    @if($checkMessage || $checkBatches)
        <div class="loading" role="status">
            @if($checkMessage)<div>{{ $checkMessage }}</div>@endif
            @foreach($checkBatches as $batch)
                <div>{{ $batch['label'] }} · {{ $batch['completed'] }}/{{ $batch['total'] }} procesados
                    @if($batch['running']) · En curso @endif
                    @if($batch['failed']) · {{ $batch['failed'] }} con error de ejecución @endif
                </div>
            @endforeach
        </div>
    @endif
    <div class="filters"><label class="search-field">Buscar sitio<input type="search" wire:model.live.debounce.300ms="search" placeholder="Nombre o URL…"></label><label>Cliente<select wire:model.live="client"><option value="">Todos los clientes</option>@foreach($clients as $customer)<option value="{{ $customer->id }}">{{ $customer->name }}</option>@endforeach</select></label><label>Propiedad<select wire:model.live="ownership"><option value="">Todas las propiedades</option><option value="own">Propios</option><option value="client">De clientes</option></select></label><label>Estado<select wire:model.live="status"><option value="">Todos los estados</option><option value="active">Activo</option><option value="paused">Pausado</option><option value="archived">Archivado</option></select></label></div>
    <div wire:loading class="loading" role="status">Actualizando listado…</div>
    <div class="table-scroll"><table><thead><tr><th>Sitio / URL</th><th>Cliente</th><th>Estado</th><th>Disponibilidad</th><th>Actualizaciones</th><th><span class="sr-only">Acciones</span></th></tr></thead><tbody>
    @forelse($sites as $site)<tr wire:key="site-{{ $site->id }}" data-edit-url="{{ route('sites.edit', $site) }}" style="cursor:pointer" onclick="if (!event.target.closest('a, button, input, select, textarea, form') &amp;&amp; !window.getSelection()?.toString()) window.location.assign(this.dataset.editUrl)"><td><a class="table-title" href="{{ route('sites.edit', $site) }}">{{ $site->name }}</a><small>{{ $site->url }}</small></td><td>{{ $site->client?->name ?? '—' }}</td><td><x-site-status :status="$site->status" /></td><td><x-availability-status :site="$site" /></td><td><x-monitor-status :site="$site" /></td><td>@if($site->admin_url)<a href="{{ $site->admin_url }}" class="button secondary" target="_blank" rel="noopener noreferrer" aria-label="Ver backend de {{ $site->name }}" title="Ver backend de {{ $site->name }}" style="width:44px;height:44px;padding:0;display:inline-flex;align-items:center;justify-content:center"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg></a>@else<span class="muted" title="Configurá la URL de acceso al backend al editar el sitio">Sin backend</span>@endif</td></tr>
    @empty<tr><td colspan="6"><div class="empty"><span>◎</span><h3>{{ $search || $status || $client || $ownership ? 'No encontramos coincidencias.' : 'Tu próximo sitio empieza acá.' }}</h3><p>{{ $search || $status || $client || $ownership ? 'Probá con otros filtros.' : 'Agregá un sitio y vinculalo con su cliente.' }}</p>@unless($search || $status || $client || $ownership)<x-add-button :href="route('sites.create')" label="Agregar sitio" />@endunless</div></td></tr>@endforelse
    </tbody></table></div><div class="pagination">{{ $sites->links() }}</div>
</section>
