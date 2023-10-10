<?php

namespace FluentBooking\App\Http\Controllers;

use FluentBooking\App\Hooks\Handlers\AdminMenuHandler;

class SettingsController extends Controller
{
    public function getSettingsMenu()
    {
        return [
            'menu_items' => apply_filters('fluent_booking/settings_menu_items', []),
        ];
    }
}
