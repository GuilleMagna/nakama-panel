<x-layout title="Resumen">
    <div class="page-heading"><div><p class="eyebrow">TU ESPACIO DE CONTROL</p><h1>Una mirada a tu operación.</h1><p class="muted">Clientes, sitios y su historia, siempre a mano.</p></div><a href="{{ route('sites.create') }}" class="button primary">+ Agregar sitio</a></div>
    <div class="stats-grid">
        <article class="stat"><span>Sitios registrados</span><strong>{{ $siteCount }}</strong><small>Tu cartera de sitios web</small></article>
        <article class="stat"><span>Clientes</span><strong>{{ $clientCount }}</strong><small>Relaciones en un solo lugar</small></article>
        <article class="stat"><span>Sitios activos</span><strong>{{ $activeCount }}</strong><small>Estado administrativo</small></article>
        <article class="stat accent"><span>Monitoreo previsto</span><strong>{{ \App\Models\PanelSetting::currentInterval() }}<em> h</em></strong><small>Consultas automáticas en desarrollo</small></article>
    </div>
    <div class="dashboard-grid"><section class="panel"><div class="section-heading"><div><p class="eyebrow">CARTERA DIGITAL</p><h2>Tus sitios web</h2></div><a href="{{ route('sites.index') }}" class="text-link">Ver todos ↗</a></div>
        <p class="muted">Registrá cada instalación, asociá su cliente y empezá a construir su historia.</p><div class="quick-actions"><a class="action-card" href="{{ route('sites.create') }}"><span>◎</span><strong>Registrar un sitio</strong><small>Dominio, cliente y conexión</small></a><a class="action-card" href="{{ route('clients.create') }}"><span>▤</span><strong>Agregar un cliente</strong><small>Contacto y sitios asociados</small></a></div>
    </section><section class="panel"><p class="eyebrow">LÍNEA DE TIEMPO</p><h2>Últimos hechos registrados</h2>
        @forelse($recentEvents as $event)<a class="timeline-mini" href="{{ route('sites.show', $event->site_id) }}"><small>{{ $event->occurred_on->format('d/m/Y') }}{{ $event->approximate ? ' · aprox.' : '' }}</small><strong>{{ $event->title }}</strong><span>{{ \App\Models\SiteEvent::CATEGORIES[$event->category] ?? $event->category }}</span></a>@empty<div class="empty compact"><span>↗</span><h3>Cada sitio tiene una historia.</h3><p>Los cambios de hosting, rediseños e incidentes que registres aparecerán acá.</p></div>@endforelse
    </section></div>
    <div class="notice info"><strong>Estamos construyendo la base.</strong> Esta entrega incluye clientes, sitios, historia y configuración de frecuencia. Monitoreo automático, accesos y gestión económica se incorporarán en próximas entregas.</div>
</x-layout>
