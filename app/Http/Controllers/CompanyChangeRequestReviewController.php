<?php

namespace App\Http\Controllers;

use App\Enums\ChangeRequestStatus;
use App\Models\CompanyChangeRequest;
use App\Services\Companies\CompanyChangeRequestReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CompanyChangeRequestReviewController extends Controller
{
    public function __construct(
        private CompanyChangeRequestReviewService $service
    ) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', CompanyChangeRequest::class);

        $statusFilter = $request->query('status', ChangeRequestStatus::Pending->value);

        $status = null;




        if ($statusFilter !== 'all') {
            $status = is_string($statusFilter)
                ? ChangeRequestStatus::tryFrom($statusFilter)
                : null;

            $status ??= ChangeRequestStatus::Pending;
        }

        $changeRequests = CompanyChangeRequest::query()
            ->with(['company.owner', 'reviewer'])
            ->when(
                $status,
                fn($query) => $query->where('status', $status)
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('companies.change-requests.admin-index', [
            'changeRequests' => $changeRequests,
            'selectedStatus' => $status?->value ?? 'all',
        ]);
    }

    public function approve(
        Request $request,
        CompanyChangeRequest $companyChangeRequest
    ): RedirectResponse {
        Gate::authorize('review', $companyChangeRequest);

        $this->service->approve(
            $companyChangeRequest,
            $request->user()
        );

        return redirect()
            ->route('company-change-requests.index', [
                'status' => ChangeRequestStatus::Approved->value,
            ])
            ->with('success', 'Company change request approved successfully.');
    }

    public function reject(
        Request $request,
        CompanyChangeRequest $companyChangeRequest
    ): RedirectResponse {
        Gate::authorize('review', $companyChangeRequest);

        $this->service->reject(
            $companyChangeRequest,
            $request->user()
        );

        return redirect()
            ->route('company-change-requests.index', [
                'status' => ChangeRequestStatus::Rejected->value,
            ])
            ->with('success', 'Company change request rejected successfully.');
    }
}
