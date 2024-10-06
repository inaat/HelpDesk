<?php

namespace App;

use App\Models\Setting;
use App\Models\Ticket;

class HelpDesk{
    /**
     * The HelpDesk version.
     *
     * @var string
     */
    const VERSION = '1';

    /**
     * The envato item ID.
     *
     * @var string
     */
    const ITEM_ID = '';

    public function getSettingsEmailNotifications(): array
    {
        $settingQuery = Setting::where('slug','email_notifications')->first();
        $settings = \json_decode($settingQuery->value, true);
        $notifications = [];
        foreach ($settings as $setting){
            $notifications[$setting['slug']] = $setting['value'];
        }
        return $notifications;
    }

    public function getUniqueUid($id)
    {
        do {
            $uid = random_int(100000, 909999)+(int) $id;
            $ticketByUid = Ticket::where('uid', $uid)->first();
        } while (!empty($ticketByUid));
        return $uid;
    }
}
