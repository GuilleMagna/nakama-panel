<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Inicio' }} · Nakama Panel</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body>
<div class="shell">
    <aside class="sidebar">
        <a class="brand" href="{{ route('dashboard') }}"><span class="brand-mark">n.</span><span>Nakama<span class="brand-sub">PANEL DE GESTIÓN</span></span></a>
        <p class="nav-label">ESPACIO DE TRABAJO</p>
        <nav aria-label="Principal">
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}" href="{{ route('dashboard') }}"><span aria-hidden="true">◫</span> Resumen</a>
            <a class="nav-link {{ request()->routeIs('sites.*') ? 'is-active' : '' }}" href="{{ route('sites.index') }}"><span aria-hidden="true">◎</span> Sitios web</a>
            <a class="nav-link {{ request()->routeIs('clients.*') ? 'is-active' : '' }}" href="{{ route('clients.index') }}"><span aria-hidden="true">▤</span> Clientes</a>
            <a class="nav-link {{ request()->routeIs('settings*') ? 'is-active' : '' }}" href="{{ route('settings') }}"><span aria-hidden="true">⚙</span> Configuración</a>
        </nav>
        <div class="sidebar-note"><span class="dot"></span> Primera entrega<span>Clientes, sitios e historia</span></div>
        <div class="profile"><span class="avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</span><div><strong>{{ auth()->user()->name }}</strong><small>Administrador</small></div></div>
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="logout">Cerrar sesión ↗</button></form>
    </aside>
    <div class="workspace">
        <header class="topbar"><span>Whileam <span class="muted">/</span> Gestión interna</span><span class="muted">{{ now()->translatedFormat('d M Y') }}</span></header>
        <main id="main">
            @if(session('success'))<div class="notice success" role="status">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="notice error" role="alert"><strong>Revisá los datos:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            {{ $slot }}
        </main>
        <footer>Nakama Panel <span>Hecho para cuidar cada sitio.</span></footer>
    </div>
</div>
@livewireScripts
</body>
</html>
