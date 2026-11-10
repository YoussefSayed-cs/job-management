<?php

namespace App\Http\Middleware;

use App\Enums\RegistrationStatus;
use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureManagementPortalAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        $isAdmin = $user->role === UserRole::Admin;

        $isApprovedCompanyOwner =
            $user->role === UserRole::CompanyOwner &&
            $user->company?->registration_status === RegistrationStatus::Approved;

        abort_unless(
            $isAdmin || $isApprovedCompanyOwner,
            403,
            'Your account is not authorized to access the Management Portal.'
        );

        return $next($request);
    }
}
