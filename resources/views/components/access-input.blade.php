@props(['id', 'name', 'label', 'value' => '', 'maxlength' => 255, 'readonly' => false])
<div data-access-field>
    <label for="{{ $id }}">{{ $label }}</label>
    <div style="position:relative;margin-top:7px">
        <input id="{{ $id }}" type="text" name="{{ $name }}" value="{{ $value }}" maxlength="{{ $maxlength }}" autocomplete="off" spellcheck="false" style="padding-right:48px" @readonly($readonly)>
        <button type="button" data-copy-access aria-label="Copiar {{ $label }}" title="Copiar {{ $label }}" style="position:absolute;right:1px;top:1px;bottom:1px;width:42px;display:grid;place-items:center;background:transparent;border:0;border-left:1px solid #d8e2dc;color:#49665a;border-radius:0 6px 6px 0">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="8" y="8" width="12" height="12" rx="2"/><path d="M16 8V4a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h4"/></svg>
        </button>
    </div>
    <small data-copy-status role="status" aria-live="polite"></small>
</div>
@once
<script>
document.addEventListener('click', async function (event) {
    const button = event.target.closest('[data-copy-access]');
    if (!button) return;
    const field = button.closest('[data-access-field]');
    const input = field.querySelector('input');
    const status = field.querySelector('[data-copy-status]');
    if (!input.value) {
        status.textContent = 'El campo está vacío.';
        return;
    }
    try {
        await navigator.clipboard.writeText(input.value);
        status.textContent = 'Copiado.';
    } catch (error) {
        input.focus();
        input.select();
        status.textContent = 'No se pudo copiar automáticamente. Usá Ctrl+C o el menú Copiar.';
    }
});
</script>
@endonce
