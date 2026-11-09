<?php

use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Daftarkan alias "role" supaya middleware('role:guru') dikenali
        $middleware->alias(['role' => RoleMiddleware::class]);

        // Mengecualikan route API/Postman dari pemeriksaan CSRF Token
        $middleware->validateCsrfTokens(except: [
            'absen/verifikasi',
            'login',
            'logout',
            'admin/*',
            'guru/*',
            'rekap/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();