<?php

namespace App\Services;

use App\Constants\RoleConstants;
use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class AdminNotificationService
{
    /**
     * Send a notification to all admin users.
     */
    public static function notifyAdmins(Notification $notification): void
    {
        try {
            $admins = User::where('role_id', RoleConstants::ADMIN)->get();

            foreach ($admins as $admin) {
                $admin->notify($notification);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send admin notification: ' . $e->getMessage());
        }
    }

    /**
     * Send a notification to a specific user.
     */
    public static function notifyUser(User $user, Notification $notification): void
    {
        try {
            $user->notify($notification);
        } catch (\Exception $e) {
            Log::error('Failed to send user notification: ' . $e->getMessage());
        }
    }

    /**
     * Send a notification to users with specific roles.
     */
    public static function notifyByRole(int $roleId, Notification $notification): void
    {
        try {
            $users = User::where('role_id', $roleId)->get();

            foreach ($users as $user) {
                $user->notify($notification);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send role notification: ' . $e->getMessage());
        }
    }
}
