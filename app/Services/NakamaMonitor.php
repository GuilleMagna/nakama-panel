<?php

namespace App\Services;

use App\Models\Site;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class NakamaMonitor
{
    public function check(Site $site): ?string
    {
        if (! $site->getRawOriginal('monitor_token')) {
            return 'Guardá el token de Nakama Monitor en la edición del sitio.';
        }

        $lock = Cache::lock('nakama-monitor:'.$site->id, 90);
        if (! $lock->get()) {
            return 'Ya hay una consulta en curso para este sitio.';
        }

        try {
            $site->refresh();
            $configuration = $site->getRawOriginal();
            $result = ['monitor_attempted_at' => now(), 'monitor_error' => null];
            try {
                $token = $site->monitor_token;
                if (! is_string($token) || ! preg_match('/^[\x21-\x7e]+$/D', $token)) {
                    throw new RuntimeException('Revisá el token configurado para este sitio.');
                }
                $result['monitor_snapshot'] = $this->snapshot($this->request($site->monitorStatusUrl(), $token, $site->url));
                $result['monitor_checked_at'] = now();
            } catch (DecryptException) {
                $result['monitor_error'] = 'No se pudo leer el token guardado. Volvé a configurarlo.';
            } catch (RuntimeException $error) {
                $result['monitor_error'] = $error->getMessage();
            }

            return DB::transaction(function () use ($site, $configuration, $result) {
                $current = Site::lockForUpdate()->find($site->id);
                foreach (['url', 'monitor_endpoint', 'monitor_token'] as $field) {
                    if (! $current || $current->getRawOriginal($field) !== ($configuration[$field] ?? null)) {
                        return 'La configuración del sitio cambió durante la consulta. Volvé a consultar.';
                    }
                }
                $previousError = $current->monitor_error;
                $current->forceFill($result)->save();
                app(HealthAlerts::class)->record($current, 'monitor', $previousError, $result['monitor_error']);

                return $result['monitor_error'];
            });
        } finally {
            $lock->release();
        }
    }

    protected function request(string $url, string $token, string $siteUrl): array
    {
        $parts = parse_url($url);
        $host = strtolower($parts['host'] ?? '');
        $siteHost = strtolower((string) parse_url($siteUrl, PHP_URL_HOST));
        if (! filter_var($url, FILTER_VALIDATE_URL) || ($parts['scheme'] ?? null) !== 'https'
            || $host !== $siteHost || isset($parts['user']) || isset($parts['pass']) || isset($parts['fragment'])
            || ($parts['port'] ?? 443) !== 443
            || (! filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && ! preg_match('/^[a-z0-9.-]+\.[a-z][a-z0-9-]*$/D', $host))) {
            throw new RuntimeException('El endpoint debe usar HTTPS y el mismo dominio que la URL del sitio.');
        }
        if (! function_exists('curl_init')) {
            throw new RuntimeException('El servidor del Panel necesita la extensión cURL.');
        }

        $records = filter_var($host, FILTER_VALIDATE_IP) ? [['ip' => $host]] : @dns_get_record($host, DNS_A | DNS_AAAA);
        $addresses = [];
        foreach ($records ?: [] as $record) {
            $address = $record['ip'] ?? $record['ipv6'] ?? null;
            if (filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_GLOBAL_RANGE)) {
                $addresses[] = str_contains($address, ':') ? '['.$address.']' : $address;
            }
        }
        if ($addresses === []) {
            throw new RuntimeException('El dominio del sitio no resuelve a una dirección pública.');
        }

        $body = '';
        $handle = curl_init($url);
        curl_setopt_array($handle, [
            CURLOPT_HTTPHEADER => ['Accept: application/json', 'X-Nakama-Monitor-Token: '.$token],
            CURLOPT_PROXY => '',
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_RESOLVE => [$host.':443:'.implode(',', array_unique($addresses))],
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 18,
            CURLOPT_WRITEFUNCTION => static function ($handle, string $chunk) use (&$body): int {
                if (strlen($body) + strlen($chunk) > 2 * 1024 * 1024) {
                    return 0;
                }
                $body .= $chunk;

                return strlen($chunk);
            },
        ]);
        $ok = curl_exec($handle);
        $status = curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        unset($handle);

        if ($ok === false) {
            throw new RuntimeException('No se pudo completar la conexión HTTPS con Nakama Monitor.');
        }
        if (in_array($status, [401, 403], true)) {
            throw new RuntimeException('El sitio rechazó el acceso. Revisá el token y las reglas de seguridad de WordPress.');
        }
        if ($status === 404 || ($status >= 300 && $status < 400)) {
            throw new RuntimeException('Revisá que el plugin esté activo y copiá su endpoint HTTPS exacto.');
        }
        if ($status !== 200) {
            throw new RuntimeException('El sitio devolvió un error HTTP '.$status.'.');
        }

        $payload = json_decode($body, true);
        if (! is_array($payload)) {
            throw new RuntimeException('El sitio no devolvió un informe válido de Nakama Monitor.');
        }

        return $payload;
    }

    private function snapshot(array $payload): array
    {
        if (($payload['wordpress']['new_version'] ?? null) === true) {
            $payload['wordpress']['new_version'] = null;
        }
        $rules = [
            'monitor_version' => ['required', 'string', 'max:50'],
            'wordpress' => ['required', 'array'],
            'theme' => ['required', 'array'],
            'theme.name' => ['present', 'string', 'max:255'],
            'plugins.items' => ['present', 'array', 'max:1000'],
            'plugins.items.*' => ['required', 'array'],
            'plugins.items.*.name' => ['present', 'string', 'max:255'],
        ];
        foreach (['wordpress', 'theme', 'plugins.items.*'] as $key) {
            $rules[$key.'.version'] = ['present', 'string', 'max:100'];
            $rules[$key.'.update_available'] = ['required', 'boolean'];
            $rules[$key.'.new_version'] = ['nullable', 'string', 'max:100'];
        }
        $validator = Validator::make($payload, $rules);
        if ($validator->fails() || ! array_is_list($payload['plugins']['items'] ?? [])) {
            throw new RuntimeException('El informe de Nakama Monitor está incompleto o tiene un formato incompatible.');
        }

        $version = static fn (array $item) => [
            'version' => $item['version'],
            'update_available' => (bool) $item['update_available'],
            'new_version' => $item['new_version'] ?? null,
        ];

        return [
            'monitor_version' => $payload['monitor_version'],
            'wordpress' => $version($payload['wordpress']),
            'theme' => ['name' => $payload['theme']['name'], ...$version($payload['theme'])],
            'plugins' => array_map(static fn (array $item) => ['name' => $item['name'], ...$version($item)], $payload['plugins']['items']),
        ];
    }
}
