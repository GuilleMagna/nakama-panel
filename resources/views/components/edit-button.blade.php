@props(['href', 'label' => 'Editar'])
<a href="{{ $href }}" class="button secondary" aria-label="{{ $label }}" title="{{ $label }}" style="width:44px;height:44px;padding:0;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
        <path d="M9.7 4.3 10.2 2h3.6l.5 2.3 1.7 1 2.2-.7 1.8 3.1-1.7 1.6v2l1.7 1.6-1.8 3.1-2.2-.7-1.7 1-.5 2.3h-3.6l-.5-2.3-1.7-1-2.2.7L4 12.9l1.7-1.6v-2L4 7.7l1.8-3.1 2.2.7 1.7-1Z" transform="translate(0 1.7)"/>
        <circle cx="12" cy="12" r="3"/>
    </svg>
</a>
