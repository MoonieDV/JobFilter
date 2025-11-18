<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function markRead(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }

        $notification->update(['is_read' => true]);

        return back()->with('status', 'Notification marked as read.');
    }

    public function markAll(Request $request)
    {
        $request->user()->alerts()->where('is_read', false)->update(['is_read' => true]);

        return back()->with('status', 'All notifications cleared.');
    }
}
