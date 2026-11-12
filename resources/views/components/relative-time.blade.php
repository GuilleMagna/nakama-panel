@props(['at'])

@if($at)
    <time datetime="{{ $at->toIso8601String() }}" title="{{ $at->format('d/m/Y H:i') }}">{{ ucfirst($at->locale('es')->diffForHumans()) }}</time>
@endif
