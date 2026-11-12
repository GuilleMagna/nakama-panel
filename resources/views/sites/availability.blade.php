<section class="panel form-stack" id="availability">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
        <h2>Disponibilidad del sitio</h2>
        <form method="POST" action="{{ route('sites.availability.check', $site) }}">
            @csrf
            <button type="submit" class="button secondary">Verificar ahora</button>
        </form>
    </div>
    <div><x-availability-status :site="$site" /></div>
    @if($site->availability_error)
        <p class="muted">{{ $site->availability_error }}</p>
    @endif
</section>
