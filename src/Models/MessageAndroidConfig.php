<?php

declare(strict_types=1);

namespace Mve\FcmPhp\Models;

/**
 * Configuration class for Android.
 * 
 * @link https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#AndroidConfig
 */
class MessageAndroidConfig
{
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_HIGH = 'high';

    public const VISIBILITY_UNSPECIFIED = 'VISIBILITY_UNSPECIFIED';
    public const VISIBILITY_PRIVATE = 'VISIBILITY_PRIVATE';
    public const VISIBILITY_PUBLIC = 'VISIBILITY_PUBLIC';
    public const VISIBILITY_SECRET = 'VISIBILITY_SECRET';

    function __construct(?string $priority = null, ?string $visibility = null, ?array $data = null, ?MessageAndroidNotification $notification = null)
    {
        $this->priority = $priority;
        $this->visibility = $visibility;
        $this->data = $data;
        $this->notification = $notification;
    }

    /**
     * Priority of a message to send to Android devices. Note this priority is an FCM concept that controls when the message is delivered.
     * Additionally, you can determine notification display priority on targeted Android devices using AndroidNotification.NotificationPriority.
     * See PRIORITY_NORMAL and PRIORITY_HIGH.
     */
    private ?string $priority;

    /**
     * Different visibility levels of a notification. See VISIBILITY_UNSPECIFIED and other visibility constants.
     */
    private ?string $visibility;

    /**
     * Arbitrary key/value payload.
     */
    private ?array $data;

    /**
     * Notification to send to android devices.
     */
    private ?MessageAndroidNotification $notification;

    // MessageAndroidConfig
    // "collapse_key": string,
    //   "priority": enum (AndroidMessagePriority),
    //   "ttl": string,
    //   "restricted_package_name": string,
    //   "data": {
    //     string: string,
    //     ...
    //   },
    //   "notification": {
    //     object (AndroidNotification)
    //   },
    //   "fcm_options": {
    //     object (AndroidFcmOptions)
    //   },
    //   "direct_boot_ok": boolean,
    //   "bandwidth_constrained_ok": boolean,
    //   "restricted_satellite_ok": boolean

    // AndroidMessagePriority
    // NORMAL / HIGH

    // AndroidNotification
    // {
    //   "title": string,
    //   "body": string,
    //   "icon": string,
    //   "color": string,
    //   "sound": string,
    //   "tag": string,
    //   "click_action": string,
    //   "body_loc_key": string,
    //   "body_loc_args": [
    //     string
    //   ],
    //   "title_loc_key": string,
    //   "title_loc_args": [
    //     string
    //   ],
    //   "channel_id": string,
    //   "ticker": string,
    //   "sticky": boolean,
    //   "event_time": string,
    //   "local_only": boolean,
    //   "notification_priority": enum (NotificationPriority), // deprecated, now use channels!
    //   "default_sound": boolean,
    //   "default_vibrate_timings": boolean,
    //   "default_light_settings": boolean,
    //   "vibrate_timings": [
    //     string
    //   ],
    //   "visibility": enum (Visibility),
    //   "notification_count": integer,
    //   "light_settings": {
    //     object (LightSettings)
    //   },
    //   "image": string,
    //   "proxy": enum (Proxy)
    // }

    // NotificationPriority - // deprecated, now use channels!
    // PRIORITY_UNSPECIFIED, PRIORITY_MIN, PRIORITY_LOW, PRIORITY_DEFAULT, PRIORITY_HIGH, PRIORITY_MAX

    // Visibility
    // VISIBILITY_UNSPECIFIED, PRIVATE, PUBLIC, SECRET

    // LightSettings
    // {
    //   "color": {
    //     object (Color)
    //   },
    //   "light_on_duration": string,
    //   "light_off_duration": string
    // }

    // Color
    // {
    //   "red": number,
    //   "green": number,
    //   "blue": number,
    //   "alpha": number
    // }

    //Proxy
    //PROXY_UNSPECIFIED, ALLOW, DENY, IF_PRIORITY_LOWERED

    // Not used.
    //AndroidFcmOptions
    //     {
    //   "analytics_label": string
    // }

    /**
     * Returns the array format that Google expects.
     */
    public function toArray(): array
    {
        $msg = [];
        if (!empty($this->visibility)) {
            $msg['visibility'] = $this->visibility;
        }
        if (!empty($this->priority)) {
            $msg['priority'] = $this->priority;
        }
        if (!empty($this->data)) {
            $msg['data'] = $this->data;
        }
        if (!empty($this->notification)) {
            $msg['notification'] = $this->notification->toArray();
        }
        return $msg;
    }
}
