@props(['action', 'label', 'confirmation'])
<form method="POST" action="{{ $action }}" data-confirm="{{ $confirmation }}" onsubmit="return confirm(this.dataset.confirm)" style="display:inline-flex;margin:0">
    @csrf @method('DELETE')
    <button type="submit" class="button secondary" aria-label="{{ $label }}" title="{{ $label }}" style="width:44px;height:44px;padding:0;display:inline-flex;align-items:center;justify-content:center;color:#a73535">
        <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18M9 6V4h6v2M5 6l1 14h12l1-14M10 10v6M14 10v6"/></svg>
    </button>
</form>
