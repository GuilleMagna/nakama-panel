<?php

namespace App\Services;

use App\Models\Site;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SiteAvailability
{
    public function check(Site $site): ?string
    {
        $result = Cache::lock('site-availability:'.$site->id, 90)->get(function () use ($site) {
            try {
                $this->probe($site->url);
                $error = null;
            } catch (RuntimeException $failure) {
                $error = $failure->getMessage();
            }

            return DB::transaction(function () use ($site, $error) {
                $current = Site::lockForUpdate()->find($site->id);
                if (! $current || $current->url !== $site->url) {
                    return 'La URL cambió durante el chequeo. Volvé a consultar.';
                }

                $previousError = $current->availability_error;
                $current->forceFill(['availability_checked_at' => now(), 'availability_error' => $error])->save();
                app(HealthAlerts::class)->record($current, 'availability', $previousError, $error);

                return $error;
            });
        });

        return $result === false ? 'Ya hay un chequeo de disponibilidad en curso.' : $result;
    }

    protected function probe(string $url): void
    {
        if (! function_exists('curl_init')) {
            throw new RuntimeException('El servidor del Panel necesita la extensión cURL para verificar disponibilidad.');
        }

        $deadline = microtime(true) + 10;
        for ($hop = 0; $hop <= 4; $hop++) {
            $parts = parse_url($url);
            $host = strtolower($parts['host'] ?? '');
            $scheme = strtolower($parts['scheme'] ?? '');
            $port = $scheme === 'https' ? 443 : 80;
            if (! filter_var($url, FILTER_VALIDATE_URL) || ! in_array($scheme, ['http', 'https'], true)
                || isset($parts['user']) || isset($parts['pass']) || ($parts['port'] ?? $port) !== $port
                || (! filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && ! preg_match('/^[a-z0-9.-]+\.[a-z][a-z0-9-]*$/D', $host))) {
                throw new RuntimeException('La URL pública o una redirección tiene un destino no admitido.');
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
                throw new RuntimeException('El dominio no resuelve a una dirección pública.');
            }

            $remaining = (int) (($deadline - microtime(true)) * 1000);
            if ($remaining <= 0) {
                throw new RuntimeException('El sitio tardó demasiado en responder.');
            }
            $stoppedAtBody = false;
            $handle = curl_init($url);
            curl_setopt_array($handle, [
                CURLOPT_PROXY => '', CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
                CURLOPT_RESOLVE => [$host.':'.$port.':'.implode(',', array_unique($addresses))],
                CURLOPT_SSL_VERIFYHOST => 2, CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_CONNECTTIMEOUT_MS => min(4000, $remaining), CURLOPT_TIMEOUT_MS => $remaining,
                CURLOPT_WRITEFUNCTION => static function ($handle, string $chunk) use (&$stoppedAtBody): int {
                    $stoppedAtBody = true;

                    return 0;
                },
            ]);
            $ok = curl_exec($handle);
            $status = curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
            $redirect = curl_getinfo($handle, CURLINFO_REDIRECT_URL);
            $errno = curl_errno($handle);
            unset($handle);

            if ($ok === false && ! ($stoppedAtBody && $errno === CURLE_WRITE_ERROR)) {
                $reason = match ($errno) {
                    CURLE_OPERATION_TIMEDOUT => 'Se agotó el tiempo de conexión desde el Panel.',
                    CURLE_COULDNT_CONNECT => 'El hosting del Panel no pudo conectarse al servidor del sitio.',
                    CURLE_PEER_FAILED_VERIFICATION, CURLE_SSL_CACERT_BADFILE => 'El hosting del Panel no pudo verificar el certificado HTTPS.',
                    CURLE_SSL_CONNECT_ERROR => 'Falló la negociación HTTPS desde el Panel.',
                    default => 'Falló la conexión HTTP o HTTPS desde el Panel.',
                };

                throw new RuntimeException($reason.' (cURL '.$errno.')');
            }
            if ($status >= 200 && $status < 300) {
                return;
            }
            if ($status >= 300 && $status < 400 && $redirect) {
                try {
                    $url = (string) UriResolver::resolve(new Uri($url), new Uri($redirect));
                } catch (\InvalidArgumentException) {
                    throw new RuntimeException('El sitio devolvió una redirección inválida.');
                }
                continue;
            }

            throw new RuntimeException('El sitio respondió HTTP '.$status.'.');
        }

        throw new RuntimeException('El sitio supera el límite de redirecciones.');
    }
}
