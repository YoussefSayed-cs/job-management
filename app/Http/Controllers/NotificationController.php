<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;
use Shagalni\Shared\Models\Application;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = $request->user()->notifications()->latest()->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(Request $request, string $notification): RedirectResponse
    {
        $item = $request->user()->notifications()->where('id', $notification)->firstOrFail();
        $item->markAsRead();

        return redirect($this->targetUrl($item) ?? route('notifications.index'));
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('status', 'All notifications marked as read.');
    }

    private function targetUrl(DatabaseNotification $notification): ?string
    {
        $applicationId = $notification->data['application_id'] ?? null;

        if ($applicationId !== null && Application::whereKey($applicationId)->exists()) {
            return route('applications.show', $applicationId);
        }

        $url = $notification->data['url'] ?? null;

        if (! is_string($url)) {
            return null;
        }

        $host = parse_url($url, PHP_URL_HOST);

        return is_string($host) && $host === request()->getHost()
            ? $url
            : null;
    }
}
