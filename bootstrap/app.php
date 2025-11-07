<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Console\Commands\DeletePendingBookingsCommand;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        DeletePendingBookingsCommand::class,
        // Add other command classes here
    ])
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule) {
        $schedule->command('bookings:delete-pending')->everyTenMinutes();
        // You can adjust the interval as needed (everyMinute, hourly, etc.)
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            // other middlewares
            // 'property.access', // Add the PropertyAccessMiddleware to API routes
            // 'identify.property', // Add the IdentifyPropertyMiddleware to API routes
        ]);
        $middleware->web([
            // other middlewares
        ]);
        
        $middleware->priority([
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            // other middlewares
        ]);

        $middleware->alias([
            'property.access' => \App\Http\Middleware\PropertyAccessMiddleware::class,
            'property.selector' => \App\Http\Middleware\PropertySelector::class, // Replaces identify.property
            'guest.portal' => \App\Http\Middleware\GuestPortalMiddleware::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'auth' => \App\Http\Middleware\Authenticate::class,
            'auth:sanctum' => \Laravel\Sanctum\Http\Middleware\Authenticate::class,
            'must.change.password' => \App\Http\Middleware\CheckMustChangePassword::class,
            // other middleware aliases
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
