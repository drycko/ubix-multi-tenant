<?php

namespace App\Traits;

use App\Models\Tenant\TenantUserActivity;
use App\Models\Tenant\TenantUserNotification;

trait LogsTenantUserActivity
{
    /**
     * Log tenant user activity
     *
     * @param string $activityType
     * @param string $description
     * @param mixed|null $subject
     * @param array $properties
     * @return void
     */
    protected function logTenantActivity(
        string $activityType,
        string $description,
        $subject = null,
        array $properties = []
    ) {
        if (auth()->guard('tenant')->check()) {
            TenantUserActivity::log(
                auth()->guard('tenant')->id(),
                $activityType,
                $description,
                $subject,
                $properties
            );
        }
    }

    /**
     * Create tenant user notification
     *
     * @param string $type
     * @param string $title
     * @param string $message
     * @param array $data
     * @param array $options
     * @return void
     */
    protected function createTenantNotification(
        string $type,
        string $title,
        string $message,
        array $data = [],
        array $options = []
    ) {
        if (auth()->guard('tenant')->check()) {
            TenantUserNotification::notify(
                auth()->guard('tenant')->id(),
                $type,
                $title,
                $message,
                $data,
                $options
            );
        }
    }

    /**
     * Log both tenant user activity and notification
     *
     * @param string $activityType
     * @param string $description
     * @param string $notificationType
     * @param string $notificationTitle
     * @param string $notificationMessage
     * @param mixed|null $subject
     * @param array $properties
     * @param array $notificationData
     * @param array $notificationOptions
     * @return void
     */
    protected function logTenantActivityAndNotification(
        string $activityType,
        string $description,
        string $notificationType,
        string $notificationTitle,
        string $notificationMessage,
        $subject = null,
        array $properties = [],
        array $notificationData = [],
        array $notificationOptions = []
    ) {
        $this->logTenantActivity($activityType, $description, $subject, $properties);
        $this->createTenantNotification($notificationType, $notificationTitle, $notificationMessage, $notificationData, $notificationOptions);
    }
}