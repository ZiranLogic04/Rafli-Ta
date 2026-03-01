<?php

// Manual Autoloader for cloned libraries (PHPWord, DomPDF)
spl_autoload_register(function ($class) {
    $prefixMap = [
        'PhpOffice\\PhpWord\\' => 'vendor/phpoffice/phpword/src/PhpWord/',
        'Dompdf\\' => 'vendor/dompdf/dompdf/src/',
        'FontLib\\' => 'vendor/phenx/php-font-lib/src/FontLib/',
        'Svg\\' => 'vendor/phenx/php-svg-lib/src/Svg/',
        'Sabberworm\\CSS\\' => 'vendor/sabberworm/php-css-parser/src/',
        'Masterminds\\' => 'vendor/masterminds/html5/src/',
    ];

    foreach ($prefixMap as $prefix => $path) {
        if (strpos($class, $prefix) === 0) {
            $relativeClass = substr($class, strlen($prefix));
            $file = __DIR__ . '/../' . $path . str_replace('\\', '/', $relativeClass) . '.php';
            
            // Special case for Dompdf\Cpdf which is in lib/
            if ($class === 'Dompdf\Cpdf') {
                $file = __DIR__ . '/../vendor/dompdf/dompdf/lib/Cpdf.php';
            }

            if (file_exists($file)) {
                require $file;
                return true;
            } else {
                // If not found in primary path, try secondary for Dompdf (lib/)
                if (strpos($class, 'Dompdf\\') === 0) {
                    $libFile = __DIR__ . '/../vendor/dompdf/dompdf/lib/' . str_replace('\\', '/', $relativeClass) . '.php';
                    if (file_exists($libFile)) {
                        require $libFile;
                        return true;
                    }
                }
            }
        }
    }
    return false;
});

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
        $middleware->validateCsrfTokens(except: [
            'api/login',
            'api/logout'
        ]);
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->is('api/*') || $request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Unauthenticated.'
                ], 401);
            }
        });
    })->create();
