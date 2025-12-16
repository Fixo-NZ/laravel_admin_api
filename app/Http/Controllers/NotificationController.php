<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    /**
     * Fetch all notifications for the authenticated user.
     * Optionally, pass ?unread=true to get only unread notifications.
     */
    public function index(Request $request)
    {
        $user = $request->user(); // Tradie or Homeowner

        $query = $user->notifications()->latest();

        if ($request->query('unread') === 'true') {
            $query->whereNull('read_at');
        }

        $notifications = $query->get()->map(function ($notification) {
            $data = $notification->data;

            // Ensure data is always an array
            if (is_string($data)) {
                $data = json_decode($data, true) ?? [];
            } elseif (!is_array($data)) {
                $data = [];
            }

            return [
                'id' => $notification->id,
                'type' => class_basename($notification->type),
                'data' => $data,
                'isRead' => $notification->read_at !== null,
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(Request $request, $id)
    {
        $user = $request->user();

        $notification = $user->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read.',
        ]);
    }

    /**
     * Mark all notifications as read (optional).
     */
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        $user->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read.',
        ]);
    }
}
