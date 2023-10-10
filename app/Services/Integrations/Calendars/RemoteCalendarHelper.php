<?php

namespace FluentBooking\App\Services\Integrations\Calendars;

use FluentBooking\App\App;
use FluentBooking\App\Models\Meta;
use FluentBooking\App\Services\Helper;
use FluentBooking\Framework\Support\Arr;

class RemoteCalendarHelper
{
    public static function getUserRemoteCreatableCalendarSettings($userId)
    {
        $exist = Meta::where('object_type', '_calendar_user_meta')
            ->where('object_id', $userId)
            ->where('key', 'create_remote_calendar')
            ->first();

        if ($exist) {
            $value = $exist->value;
            if (!is_array($value)) {
                return [];
            }
            return $value;
        }

        return [];
    }

    public static function updateUserRemoteCreatableCalendarSettings($userId, $settings)
    {
        if (!$settings) {
            $settings = [];
        }

        $exist = Meta::where('object_type', '_calendar_user_meta')
            ->where('object_id', $userId)
            ->where('key', 'create_remote_calendar')
            ->first();

        if ($exist) {
            $exist->value = $settings;
            $exist->save();
            return $exist;
        }

        return Meta::create([
            'object_type' => '_calendar_user_meta',
            'object_id'   => $userId,
            'key'         => 'create_remote_calendar',
            'value'       => $settings
        ]);
    }

    public static function getRemoteCalendarConfig($userId)
    {
        $settings = self::getUserRemoteCreatableCalendarSettings($userId);
        if (!$settings) {
            return null;
        }

        $idConfig = Arr::get($settings, 'id');
        $driver = Arr::get($settings, 'driver');

        if (!$idConfig || !$driver) {
            return null;
        }

        $idArr = explode('__||__', $idConfig);

        if (count($idArr) < 2) {
            return null;
        }

        $metaId = (int)array_shift($idArr);

        if (!$metaId) {
            return null;
        }

        $remoteCalendarId = implode('__||__', $idArr);

        return [
            'db_id'              => $metaId,
            'remote_calendar_id' => $remoteCalendarId,
            'driver'             => $driver
        ];
    }

    public static function showGeneralError($data = [])
    {
        $defaults = [
            'title' => 'Unknow error',
            'body' => 'Something went wrong. Please try again later.',
            'btn_url' => Helper::getAppBaseUrl(),
            'btn_text' => 'Back to dashboard'
        ];

        $data = array_merge($defaults, $data);

        $app = App::getInstance();

        header('Content-Type: text/html; charset=utf-8');
        $app->view->render('admin.general_error', $data);
        exit();
    }
}
