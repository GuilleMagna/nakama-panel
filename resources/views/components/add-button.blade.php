@props(['label', 'href' => null])
@if($href)
<a href="{{ $href }}" class="button primary" aria-label="{{ $label }}" title="{{ $label }}" style="width:44px;height:44px;padding:0;display:inline-flex;align-items:center;justify-content:center">
@else
<button type="submit" class="button primary" aria-label="{{ $label }}" title="{{ $label }}" style="width:44px;height:44px;padding:0;display:inline-flex;align-items:center;justify-content:center">
@endif
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
@if($href)</a>@else</button>@endif
