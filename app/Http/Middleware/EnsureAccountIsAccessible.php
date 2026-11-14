<?php

namespace App\Http\Middleware;

use App\Enums\AccountStatus;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsAccessible
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && (
            $user->status !== AccountStatus::Active ||
            $user->deletion_requested_at !== null ||
            $user->anonymized_at !== null
        )) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('status', 'Your account is currently unavailable.');
        }

        return $next($request);
    }
}
