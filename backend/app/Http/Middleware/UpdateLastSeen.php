<?php
# app/Http/Middleware/UpdateLastSeen.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastSeen
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            auth()->user()->updateLastSeen();
        }
        
        return $next($request);
    }
}