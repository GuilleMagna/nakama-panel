<x-layout title="Configuración">
    <div class="page-heading"><h1>Configuración</h1></div>
    <form class="panel form-stack form-panel" method="POST" action="{{ route('settings.update') }}">
        @csrf @method('PUT')
        <h2>Frecuencia de chequeos</h2>
        <label>Disponibilidad (minutos)
            <input type="number" name="availability_interval_minutes" required min="5" max="1440" step="5" value="{{ old('availability_interval_minutes', $settings?->availability_interval_minutes ?? 30) }}">
        </label>
        <label>Actualizaciones de WordPress (horas)
            <input type="number" name="monitor_interval_hours" required min="1" max="720" value="{{ old('monitor_interval_hours', $settings?->monitor_interval_hours ?? 12) }}">
            <small>Se aplica a los sitios que no tengan un intervalo propio.</small>
        </label>
        <h2>Avisos por correo</h2>
        @unless($mailConfigured)
            <p class="notice info">Configurá un servicio de correo para que los avisos lleguen a tu casilla. Los avisos pendientes se guardan en el Panel.</p>
        @endunless
        <label>Correo de destino
            <input type="email" name="health_alert_email" maxlength="255" value="{{ old('health_alert_email', $settings?->health_alert_email) }}">
        </label>
        <input type="hidden" name="availability_alerts_enabled" value="0">
        <label class="checkbox"><input type="checkbox" name="availability_alerts_enabled" value="1" @checked(old('availability_alerts_enabled', $settings?->availability_alerts_enabled ?? true))> Avisar si un sitio deja de responder</label>
        <input type="hidden" name="monitor_alerts_enabled" value="0">
        <label class="checkbox"><input type="checkbox" name="monitor_alerts_enabled" value="1" @checked(old('monitor_alerts_enabled', $settings?->monitor_alerts_enabled ?? true))> Avisar si falla la conexión con Nakama Monitor</label>
        <div class="form-actions"><button class="button primary">Guardar configuración</button></div>
    </form>
    <section class="panel">
        <h2>Avisos recientes</h2>
        @if($alerts->isEmpty())
            <p class="muted">Todavía no hay avisos registrados.</p>
        @else
            <div class="table-scroll"><table>
                <thead><tr><th>Sitio</th><th>Problema</th><th>Detectado</th><th>Correo</th></tr></thead>
                <tbody>@foreach($alerts as $alert)
                    <tr>
                        <td>{{ $alert->site_name }}</td><td>{{ $alert->detail }}</td><td>{{ $alert->created_at }}</td>
                        <td>{{ $alert->sent_at ? 'Enviado' : ($alert->cancelled_at ? 'Cancelado' : ($alert->delivery_failed ? 'Falló · se reintentará' : 'Pendiente')) }}</td>
                    </tr>
                @endforeach</tbody>
            </table></div>
        @endif
    </section>
</x-layout>
