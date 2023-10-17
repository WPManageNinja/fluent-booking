<?php

namespace FluentBooking\App\Services;

use FluentBooking\App\Models\Calendar;
use FluentBooking\App\Models\Meta;
use FluentBooking\Framework\Support\Arr;

class PermissionManager
{
    public static function allPermissionSets()
    {
        return [
            'manage_own_calendar'                => 'Manage only own Calendar, Events, Bookings & Availability',
            'read_all_bookings'                  => 'Read Access to All Bookings',
            'manage_all_bookings'                => 'Read & Write Access to All Bookings',
            'read_other_calendars'               => 'Read Access of Other Users Calendars',
            'manage_other_calendars'             => 'Manage Other Users Calendars',
            'read_and_use_other_availabilities' => 'Read & Use Access of All Availabilities',
            'manage_other_availabilities'        => 'Manage All Availabilities',
            'invite_team_members'                => 'Invite Other Team Members'
        ];
    }

    public static function hasAllCalendarAccess()
    {
        return apply_filters('fluent_booking/has_all_calendar_access', current_user_can('manage_options'));
    }

    public static function canReadCalendar($calendarId)
    {
        if (current_user_can('manage_options')) {
            return true;
        }

        $calendar = Calendar::find($calendarId);

        if (!$calendar) {
            return false;
        }

        if ($calendar->user_id === get_current_user_id()) {
            return true;
        }

        $userPermissions = self::getUserPermissions();

        if (!$userPermissions) {
            return false;
        }

        return in_array('read_other_calendars', $userPermissions) || in_array('manage_other_calendars', $userPermissions);
    }

    public static function canWriteCalendar($calendarId)
    {
        if (current_user_can('manage_options')) {
            return true;
        }

        $calendar = Calendar::find($calendarId);

        if (!$calendar) {
            return false;
        }

        if ($calendar->user_id === get_current_user_id()) {
            return true;
        }

        $userPermissions = self::getUserPermissions();

        if (!$userPermissions) {
            return false;
        }

        return in_array('manage_other_calendars', $userPermissions);
    }

    public static function hasCalendarAccess($calendar)
    {
        return current_user_can('manage_options') || $calendar->user_id === get_current_user_id();
    }

    public static function currentUserHasAnyPemrmission()
    {
        if (current_user_can('manage_options')) {
            return true;
        }

        return !!self::getUserPermissions();
    }

    public static function userCan($permissions)
    {
        if (current_user_can('manage_options')) {
            return true;
        }

        $userPermissions = self::getUserPermissions();

        if (!$userPermissions) {
            return false;
        }

        if (is_string($permissions)) {
            return in_array($permissions, $userPermissions);
        }

        if (is_array($permissions)) {
            foreach ($permissions as $permission) {
                if (in_array($permission, $userPermissions)) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function getUserPermissions($user = null, $formatted = false)
    {
        if ($user === null) {
            $user = wp_get_current_user();
        }

        if (!$user || !$user->ID) {
            return [];
        }

        $allPermissions = self::allPermissionSets();
        if (user_can($user, 'manage_options')) {
            $permissions = array_merge(
                [
                    'super_admin' => 'All Access (Administrator)'
                ],
                $allPermissions
            );

            if ($formatted) {
                return $permissions;
            }

            return array_keys($permissions);
        }

        // maybe restricted Access
        $permissions = self::getMetaPermissions($user->ID);

        if (!$permissions) {
            $calendar = Calendar::where('user_id', $user->ID)->first();
            if (!$calendar) {
                return [];
            }

            Meta::create([
                'object_type' => 'user_meta',
                'object_id'   => $user->ID,
                'key'         => '_access_permissions',
                'value'       => ['manage_own_calendar']
            ]);

            if ($formatted) {
                return ['manage_own_calendar' => 'Manage only own Calendar, Events, Bookings & Availability'];
            }
            return ['manage_own_calendar'];
        }

        if (!$formatted) {
            return $permissions;
        }

        $formattedPermissions = [];

        foreach ($permissions as $permission) {
            if (isset($allPermissions[$permission])) {
                $formattedPermissions[$permission] = $allPermissions[$permission];
            }
        }

        return $formattedPermissions;
    }

    public static function getMetaPermissions($userId = null)
    {

        if ($userId === null) {
            $userId = get_current_user_id();
        }

        if (!$userId) {
            return [];
        }

        $meta = Meta::where('object_type', 'user_meta')
            ->where('object_id', $userId)
            ->where('key', '_access_permissions')
            ->first();

        if ($meta) {
            return $meta->value;
        }

        return [];
    }

    public static function getMenuPermission()
    {
        if (current_user_can('manage_options')) {
            return 'manage_options';
        }

        $userId = get_current_user_id();

        // Check if the user has any calendar
        $calendar = Calendar::where('user_id',)->first();
        if ($calendar) {
            $user = wp_get_current_user();
            $roles = (array)$user->roles;

            if (in_array('subscriber', $roles)) {
                return '';
            }

            return Arr::get($roles, 0);
        }

        // Check Meta Permissions
        $metaPermission = self::getMetaPermissions($userId);

        if (!$metaPermission) {
            return '';
        }

        $user = wp_get_current_user();
        $roles = (array)$user->roles;

        if (in_array('subscriber', $roles)) {
            return '';
        }

        return Arr::get($roles, 0);
    }

}
