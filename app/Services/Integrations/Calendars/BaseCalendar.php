<?php

namespace FluentBooking\App\Services\Integrations\Calendars;

use FluentBooking\Framework\Support\Arr;

class BaseCalendar
{

    private $dbId;

    private $settingsKey = '';

    private $settings = [];

    private $client;

    private $calendarId;

    public function __construct($config = [])
    {
        $this->settingsKey = Arr::get($config, 'settings_key');
        $this->settings = Arr::get($config, 'settings');
        $this->calendarId = Arr::get($config, 'calendar_id');
        $this->dbId = Arr::get($config, 'db_id');
    }


    public function setClient($client)
    {
        $this->client = $client;
    }
    

}
