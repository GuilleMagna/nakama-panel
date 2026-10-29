<!DOCTYPE html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Ingresar · Nakama Panel</title>@vite(['resources/css/app.css', 'resources/js/app.js'])</head>
<body class="login-body"><main class="login-card">
    <span class="brand-mark">n.</span><p class="eyebrow">WHILEAM · GESTIÓN INTERNA</p><h1>Todo tu ecosistema,<br>en un solo lugar.</h1><p class="muted">Ingresá a Nakama Panel para gestionar tus clientes y sitios.</p>
    @if($errors->any())<div class="notice error" role="alert">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('login') }}" class="form-stack">@csrf
        <label>Correo electrónico<input name="email" type="email" required autocomplete="username" value="{{ old('email') }}" autofocus></label>
        <label>Contraseña<input name="password" type="password" required autocomplete="current-password"></label>
        <button class="button primary full">Ingresar al panel <span>↗</span></button>
    </form><p class="login-foot">Acceso privado para administración.</p>
</main></body></html>
