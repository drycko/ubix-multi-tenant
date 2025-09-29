<?php

namespace App\Traits;

use App\Models\AdminActivity;
use App\Models\AdminNotification;

trait LogsAdminActivity
{
    /**
     * Log admin activity
     *
     * @param string $activityType create|update|delete|soft_delete|restore
     * @param string $tableName
     * @param int $recordId
     * @param string $description
     * @return void
     */
    protected function logAdminActivity(string $activityType, string $tableName, int $recordId, string $description)
    {
        if (auth()->user()) {
            $adminActivity = new AdminActivity();
            $adminActivity->admin_id = auth()->user()->id;
            $adminActivity->activity_type = $activityType;
            $adminActivity->ip_address = request()->ip();
            $adminActivity->user_agent = request()->header('User-Agent');
            $adminActivity->table_name = $tableName;
            $adminActivity->record_id = $recordId;
            $adminActivity->description = $description;
            $adminActivity->is_read = false;
            $adminActivity->save();
        }
    }

    /**
     * Create admin notification
     *
     * @param string $message
     * @return void
     */
    protected function createAdminNotification(string $message)
    {
        if (auth()->user()) {
            $adminNotification = new AdminNotification();
            $adminNotification->admin_id = auth()->user()->id;
            $adminNotification->notification_type = 'system';
            $adminNotification->message = $message;
            $adminNotification->ip_address = request()->ip();
            $adminNotification->user_agent = request()->header('User-Agent');
            $adminNotification->is_read = false;
            $adminNotification->save();
        }
    }

    /**
     * Log both admin activity and notification
     *
     * @param string $activityType
     * @param string $tableName
     * @param int $recordId
     * @param string $description
     * @param string $notificationMessage
     * @return void
     */
    protected function logAdminActivityAndNotification(
        string $activityType,
        string $tableName,
        int $recordId,
        string $description,
        string $notificationMessage
    ) {
        $this->logAdminActivity($activityType, $tableName, $recordId, $description);
        $this->createAdminNotification($notificationMessage);
    }
}