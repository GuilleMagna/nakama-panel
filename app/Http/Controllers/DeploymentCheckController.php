<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Process;
use Throwable;

class DeploymentCheckController extends Controller
{
    public function __invoke()
    {
        $report = [
            'archivo_despliegue_presente' => is_file(base_path('.cpanel.yml')),
            'archivo_despliegue_legible' => is_readable(base_path('.cpanel.yml')),
            'git_disponible' => false,
            'archivos_pendientes' => [],
        ];

        if (! function_exists('proc_open')) {
            $report['mensaje'] = 'El hosting no permite consultar Git desde PHP. No se modificó ningún archivo.';
            return response()->json($report)->header('Cache-Control', 'no-store');
        }

        $git = 'git';
        foreach (['/usr/local/cpanel/3rdparty/bin/git', '/usr/bin/git', '/usr/local/bin/git'] as $candidate) {
            if (is_executable($candidate)) {
                $git = $candidate;
                break;
            }
        }

        try {
            // Solo estado: sin shell, sin entrada del usuario y sin escribir el índice.
            $result = Process::path(base_path())->timeout(10)->run([
                $git, '--no-optional-locks', '-c', 'core.fsmonitor=false',
                'status', '--porcelain=v1', '--untracked-files=normal',
            ]);
            if ($result->successful()) {
                $report['git_disponible'] = true;
                $report['archivos_pendientes'] = array_values(array_filter(explode("\n", rtrim($result->output(), "\r\n"))));
                $report['mensaje'] = empty($report['archivos_pendientes'])
                    ? 'Git no detecta archivos pendientes.'
                    : 'Estos archivos requieren revisión. No se modificó ningún archivo.';
            } else {
                $report['mensaje'] = 'Git no pudo consultar el repositorio. No se modificó ningún archivo.';
            }
        } catch (Throwable $exception) {
            // No devolver errores de proceso ni configuración privada al navegador.
            $report['mensaje'] = 'El hosting no permitió completar la consulta de Git. No se modificó ningún archivo.';
        }

        return response()->json($report, options: JSON_INVALID_UTF8_SUBSTITUTE)->header('Cache-Control', 'no-store');
    }
}
