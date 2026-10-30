<hr><h2>Credenciales de acceso</h2>
<p class="muted">Opcionales. Se guardan cifradas. {{ $site->exists ? 'Dejá cada contraseña vacía para conservar la guardada.' : 'Podés completarlas ahora o más adelante.' }}</p>
@php($access = $site->access_credentials ?? [])
@foreach(['wordpress' => 'WordPress / backend', 'cpanel' => 'cPanel', 'ftp' => 'FTP / SFTP'] as $kind => $label)
    <h3>{{ $label }}</h3>
    @if($kind === 'wordpress')<small>Se utiliza la URL de acceso al backend indicada arriba.</small>@endif
    @if($kind === 'ftp')
        <div class="form-grid" style="grid-template-columns:repeat(auto-fit,minmax(min(100%,180px),1fr))">
            <label>Servidor<input name="access[ftp][host]" maxlength="255" value="{{ $access['ftp']['host'] ?? '' }}" placeholder="ftp.ejemplo.com"></label>
            <label>Puerto<input type="number" name="access[ftp][port]" min="1" max="65535" value="{{ $access['ftp']['port'] ?? '' }}" placeholder="21 o 22"></label>
            <label>Protocolo<select name="access[ftp][protocol]">@foreach(['ftp' => 'FTP', 'ftps' => 'FTPS', 'sftp' => 'SFTP'] as $value => $text)<option value="{{ $value }}" @selected(($access['ftp']['protocol'] ?? 'sftp') === $value)>{{ $text }}</option>@endforeach</select></label>
        </div>
    @endif
    <div class="form-grid">
        <x-access-input :id="$kind.'-username'" :name="'access['.$kind.'][username]'" :label="'Usuario de '.$label" :value="$access[$kind]['username'] ?? ''" />
        <x-access-input :id="$kind.'-password'" :name="'access['.$kind.'][password]'" :label="'Contraseña de '.$label" :value="$access[$kind]['password'] ?? ''" :maxlength="1000" />
    </div>
@endforeach
