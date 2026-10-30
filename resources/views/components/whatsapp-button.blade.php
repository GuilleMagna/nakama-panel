@props(['href', 'name', 'phone'])
<a href="{{ $href }}" class="button secondary" target="_blank" rel="noopener noreferrer" aria-label="Abrir WhatsApp de {{ $name }}" title="WhatsApp: {{ $phone }}" style="width:44px;height:44px;padding:0;display:inline-flex;align-items:center;justify-content:center;color:#168447">
    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 11.5a9 9 0 0 1-13.4 7.9L3 21l1.6-4.6A9 9 0 1 1 21 11.5Z"/><path d="m8.1 7.3 1.5 2.4-1 1.2a8.4 8.4 0 0 0 4.5 4.1l1.1-1.2 2.5 1.2c-.3 1.5-1.2 2.1-2.4 1.9-4.4-.8-7.4-3.7-8-7.1-.2-1.2.5-2.1 1.8-2.5Z"/></svg>
</a>
