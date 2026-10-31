<?php

declare(strict_types=1);

namespace Mve\FcmPhp\Models;

/**
 * @link https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages
 */
class MessageAndroidNotification
{
    private ?string $title;
    private ?string $body;

    /**
     * The sound to play when the device receives the notification. Supports "default" or the filename of a sound resource bundled in the app. Sound files must reside in /res/raw/.
     */
    private ?string $sound;

    /**
     * The notification's icon. Sets the notification icon to myicon for drawable resource myicon. If you don't send this key in the request, FCM displays the launcher icon specified in your app manifest.
     */
    private ?string $icon;

    /**
     * The notification's icon color, expressed in #rrggbb format.
     */
    private ?string $color;

    /**
     * The notification's channel id (new in Android O). The app must create a channel with this channel ID before any notification with this channel ID is received. If you don't send this channel ID in the request, or if the channel ID provided has not yet been created by the app, FCM uses the channel ID specified in the app manifest.
     * Output: channel_id
     */
    private ?string $channelId;

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
    //   "notification_priority": enum (NotificationPriority),
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

    // NotificationPriority
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


    public function toArray(): array
    {
        $msg = [];
        if (!empty($this->title)) {
            $msg['title'] = $this->title;
        }
        if (!empty($this->body)) {
            $msg['body'] = $this->body;
        }
        if (!empty($this->color)) {
            $msg['color'] = $this->color;
        }
        if (!empty($this->icon)) {
            $msg['icon'] = $this->icon;
        }
        if (!empty($this->sound)) {
            $msg['sound'] = $this->sound;
        }
        if (!empty($this->channelId)) {
            $msg['channel_id'] = $this->channelId;
        }
        return $msg;
    }
}
