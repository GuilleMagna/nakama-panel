<x-layout :title="$site->exists ? 'Editar sitio' : 'Nuevo sitio'">
<a class="back-link" href="{{ route('sites.index') }}">← Sitios web</a><div class="page-heading"><div><p class="eyebrow">CARTERA DIGITAL</p><h1>{{ $site->exists ? 'Editar sitio' : 'Nuevo sitio' }}</h1><p class="muted">Registrá su identidad, cliente y configuración de conexión.</p></div></div>
<form method="POST" class="panel form-stack form-panel" action="{{ $site->exists ? route('sites.update', $site) : route('sites.store') }}">@csrf @if($site->exists)@method('PUT')@endif
    <h2>Información del sitio</h2><div class="form-grid"><label>Nombre *<input name="name" required maxlength="255" value="{{ old('name', $site->name) }}"></label><label>URL pública *<input type="url" name="url" placeholder="https://ejemplo.com.ar" required maxlength="500" value="{{ old('url', $site->url) }}"></label></div>
    <div class="form-grid"><label>Propiedad *<select name="ownership">@foreach(['client' => 'De un cliente', 'own' => 'Propio'] as $key => $label)<option value="{{ $key }}" @selected(old('ownership', $site->ownership) === $key)>{{ $label }}</option>@endforeach</select></label><label>Cliente<select name="client_id"><option value="">Sin cliente (sitio propio)</option>@foreach($clients as $client)<option value="{{ $client->id }}" @selected((string) old('client_id', $site->client_id) === (string) $client->id)>{{ $client->name }}</option>@endforeach</select><small>Obligatorio si el sitio pertenece a un cliente.</small></label></div>
    <div class="form-grid"><label>Estado administrativo<select name="status">@foreach(['active' => 'Activo', 'paused' => 'Pausado', 'archived' => 'Archivado'] as $key => $label)<option value="{{ $key }}" @selected(old('status', $site->status) === $key)>{{ $label }}</option>@endforeach</select></label><label>URL de acceso al backend<input type="url" name="admin_url" maxlength="500" value="{{ old('admin_url', $site->admin_url) }}" placeholder="https://ejemplo.com.ar/wp-admin"></label></div>
    <hr>
    <h2>Vencimientos</h2>
    <p class="muted">Ingresá mes y año (AAAA-MM) o la fecha completa (AAAA-MM-DD). El vencimiento avanza un año automáticamente al llegar a la fecha. Si ingresás solo mes y año, se toma el primer día de ese mes.</p>
    <div class="form-grid">
        @foreach(\App\Models\Site::RENEWAL_LABELS as $service => $label)
            <label>{{ $label }}
                <input name="{{ $service }}_renewal" maxlength="10" placeholder="2027-02" value="{{ old($service.'_renewal', $site->getAttribute($service.'_renewal')) }}">
            </label>
        @endforeach
    </div>
    <input type="hidden" name="renewal_alerts_enabled" value="0">
    <label class="checkbox"><input type="checkbox" name="renewal_alerts_enabled" value="1" @checked(old('renewal_alerts_enabled', $site->renewal_alerts_enabled ?? true))> Enviar recordatorios de vencimiento de este sitio</label>
    <hr>
    <h2>Contacto para renovaciones y consultas</h2>
    <div class="form-grid">
        <label>Nombre
            <input name="contact_first_name" autocomplete="section-site-contact given-name" maxlength="255" value="{{ old('contact_first_name', $site->contact_first_name) }}">
        </label>
        <label>Apellido
            <input name="contact_last_name" autocomplete="section-site-contact family-name" maxlength="255" value="{{ old('contact_last_name', $site->contact_last_name) }}">
        </label>
        <label>CUIT
            <input name="contact_cuit" maxlength="13" placeholder="XX-XXXXXXXX-X" value="{{ old('contact_cuit', $site->contact_cuit) }}">
        </label>
        <label>Correo electrónico
            <input type="email" name="contact_email" autocomplete="section-site-contact email" maxlength="255" value="{{ old('contact_email', $site->contact_email) }}">
        </label>
        <label>Teléfono
            <input type="tel" name="contact_phone" autocomplete="section-site-contact tel" maxlength="50" value="{{ old('contact_phone', $site->contact_phone) }}">
        </label>
    </div>
    @include('sites.access-form')
    <hr><h2>Nakama Monitor</h2><p class="muted">Si dejás vacío el endpoint, se usa la ruta habitual de Nakama Monitor en la URL del sitio.</p>
    @if($site->exists)<p><a class="text-link" href="{{ route('sites.show', $site) }}#availability">Ver salud y actualizaciones ↗</a></p>@endif
    <input type="hidden" name="health_alerts_enabled" value="0">
    <label class="checkbox"><input type="checkbox" name="health_alerts_enabled" value="1" @checked(old('health_alerts_enabled', $site->health_alerts_enabled ?? true))> Enviar avisos de salud de este sitio</label>
    <label>Endpoint HTTPS<input type="url" name="monitor_endpoint" maxlength="500" value="{{ old('monitor_endpoint', $site->monitor_endpoint) }}" placeholder="https://ejemplo.com.ar/wp-json/nakama-monitor/v1/status"></label>
    <label>{{ $site->exists ? 'Reemplazar token privado' : 'Token privado' }}<input type="password" name="monitor_token" maxlength="255" autocomplete="new-password" value=""><small>{{ $site->exists ? 'Dejalo vacío para conservar el token guardado. Nunca se muestra en este formulario.' : 'Se almacena cifrado. Es independiente de la contraseña del backend.' }}</small></label>
    <label>Intervalo propio de consulta (horas)<input type="number" name="monitor_interval_hours" min="1" max="720" value="{{ old('monitor_interval_hours', $site->monitor_interval_hours) }}" placeholder="Heredar configuración general"><small>Vacío: hereda el intervalo general, actualmente {{ \App\Models\PanelSetting::currentInterval() }} horas.</small></label>
    <label>Notas<textarea name="notes" rows="4" maxlength="10000">{{ old('notes', $site->notes) }}</textarea><small>No incluyas contraseñas en las notas.</small></label>
    <div class="form-actions"><a href="{{ route('sites.index') }}" class="button secondary">Cancelar</a><button class="button primary">Guardar sitio</button></div>
</form>@if($site->exists)<div class="form-panel" style="margin-top:20px"><x-delete-button :action="route('sites.destroy', $site)" :label="'Eliminar sitio: '.$site->name" :confirmation="'¿Eliminar '. $site->name .' y todo su historial del panel? Esta acción no se puede deshacer. No elimina la instalación de WordPress.'" /></div>@endif</x-layout>
