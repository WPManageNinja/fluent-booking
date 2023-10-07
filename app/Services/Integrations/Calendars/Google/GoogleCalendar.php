<?php

namespace FluentBooking\App\Services\Integrations\Calendars\Google;

use FluentBooking\App\Models\Meta;
use FluentBooking\App\Services\Integrations\Calendars\BaseCalendar;

class GoogleCalendar extends BaseCalendar
{
    public function __construct($settings)
    {
        if (is_numeric($settings)) {
            $settings = Meta::where('id', $settings)
                ->where('object_type', 'google_calendar')
                ->where('key', 'google_calendar_config')
                ->first();

            if (!$settings) {
                throw new \Exception('Google Calendar settings could be not found');
            }
        }

        $configData = $settings->value;

        $config = [
            'id'           => $settings->id,
            'settings_key' => 'google_calendar_config',
            'settings'     => $configData,
            'calendar_id'  => $settings->object_id,
            'db_id'        => $settings->id,
        ];

        parent::__construct($config);
    }

    public function getToken()
    {

    }

    public function renewToken()
    {

    }

}
