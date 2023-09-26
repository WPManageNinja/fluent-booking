<?php

namespace FluentBooking\App\Http\Controllers;

use FluentBooking\App\Hooks\Handlers\AdminMenuHandler;

class SettingsController extends Controller
{
    public function index()
    {
        try {
            $menuItems = (new AdminMenuHandler())->settingMenuItems();

            return $this->sendSuccess([
                'items' => $menuItems,
            ]);

        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
