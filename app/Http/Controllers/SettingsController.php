<?php

namespace FluentBooking\App\Http\Controllers;

use FluentBooking\App\Hooks\Handlers\AdminMenuHandler;

class SettingsController extends Controller
{
    public function getSettingsMenu()
    {
        $menuItems = (new AdminMenuHandler())->settingMenuItems();
        return [
            'menu_items' => $menuItems,
        ];
    }


}
