<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;

// Simple test route to check database connection
Route::get('/test-db', function () {
    // Get database configuration
    $config = [
        'DB_CONNECTION' => env('DB_CONNECTION', 'mysql'),
        'DB_HOST' => env('DB_HOST', '127.0.0.1'),
        'DB_PORT' => env('DB_PORT', '3306'),
        'DB_DATABASE' => env('DB_DATABASE', 'laravel'),
        'DB_USERNAME' => env('DB_USERNAME', 'root'),
        'DB_PASSWORD' => env('DB_PASSWORD') ? '*****' : 'Not set',
    ];

    // Check if .env file exists and is readable
    $envPath = base_path('.env');
    $envExists = file_exists($envPath);
    $envReadable = $envExists ? is_readable($envPath) : false;

    // Check database connection
    try {
        DB::connection()->getPdo();
        $connectionStatus = 'Connected successfully';
        $error = null;
    } catch (\Exception $e) {
        $connectionStatus = 'Connection failed';
        $error = [
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ];
    }

    // Check if database file exists for SQLite
    $dbFile = database_path('database.sqlite');
    $sqliteFileExists = file_exists($dbFile);

    return response()->json([
        'status' => $error ? 'error' : 'success',
        'server' => [
            'php_version' => phpversion(),
            'laravel_version' => app()->version(),
        ],
        'environment' => [
            'app_env' => env('APP_ENV'),
            'app_debug' => env('APP_DEBUG', false),
        ],
        'database' => [
            'connection' => $connectionStatus,
            'config' => $config,
            'sqlite' => [
                'file_exists' => $sqliteFileExists,
                'path' => $dbFile,
                'writable' => is_writable(dirname($dbFile))
            ],
            'env_file' => [
                'exists' => $envExists,
                'readable' => $envReadable,
                'path' => $envPath
            ]
        ],
        'error' => $error,
        'suggestion' => $error ? [
            'check_database_server' => 'Make sure your database server is running',
            'check_credentials' => 'Verify database credentials in .env file',
            'check_database' => 'Check if the database exists and is accessible',
            'check_permissions' => 'Verify database user has proper permissions'
        ] : null
    ], $error ? 500 : 200);
});

// Authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});