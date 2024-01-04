<?php

namespace FluentBooking\App\Http\Controllers;

use FluentBooking\App\Models\Calendar;
use FluentBooking\App\Models\Meta;
use FluentBooking\App\Services\PermissionManager;
use FluentBooking\Framework\Request\Request;

class AdminController extends Controller
{
    public function getRemainingHosts(Request $request)
    {
        if (!current_user_can('list_users')) {
            $user = get_user_by('ID', get_current_user_id());
            return [
                'hosts' => [
                    [
                        'is_own' => true,
                        'id'     => $user->ID,
                        'label'  => $user->display_name . ' (' . $user->user_email . ')'
                    ]
                ]
            ];
        }
        $args = array(
            'role__not_in' => array('subscriber'),
            'number'       => 50,
            'search'       => '*' . sanitize_text_field($request->get('search')) . '*',
        );

        $users = get_users($args);

        $hosts = [];
        $pushedIds = [];

        $calendarUserIds = Calendar::all()->pluck('user_id')->toArray();

        foreach ($users as $user) {
            $pushedIds[] = $user->ID;
            $hosts[] = [
                'id'       => $user->ID,
                'label'    => $user->display_name . ' (' . $user->user_email . ')',
                'disabled' => in_array($user->ID, $calendarUserIds)
            ];
        }

        if ($selectedId = $request->get('selected_id')) {
            if (!in_array($selectedId, $pushedIds)) {
                $user = get_user_by('ID', $selectedId);
                if ($user) {
                    $hosts[] = [
                        'id'       => $user->ID,
                        'label'    => $user->display_name . ' (' . $user->user_email . ')',
                        'disabled' => in_array($user->ID, $calendarUserIds)
                    ];
                }
            }
        }

        return [
            'hosts' => $hosts
        ];
    }

    public function getOtherHosts(Request $request)
    {
        if (!current_user_can('list_users')) {
            return [
                'hosts' => []
            ];
        }

        $currentUserId = get_current_user_id();

        $calendars = Calendar::with(['user'])
            ->where('user_id', '!=', $currentUserId)
            ->where('type', '!=', 'team')
            ->get();

        $allHosts = [];

        foreach ($calendars as $calendar) {
            $userName = __('Deleted User', 'fluent-booking-pro');
            if ($calendar->user) {
                $userName = $calendar->user->full_name;
            }

            if ($currentUserId == $calendar->user_id) {
                $userName = __('My Meetings', 'fluent-booking-pro');
            }

            $allHosts[] = [
                'id'    => (string)$calendar->user_id,
                'label' => $userName
            ];
        }

        return [
            'hosts' => $allHosts
        ];
    }

    public function getAllHosts(Request $request)
    {
        $calendars = Calendar::with(['user'])
            ->where('type', '!=', 'team')
            ->get();
        
        $hosts = [];
        foreach ($calendars as $calendar) {
            $hosts[] = [
                'id'          => $calendar->user->ID,
                'name'        => $calendar->user->full_name,
                'avatar'      => $calendar->getAuthorPhoto(),
                'calendar_id' => $calendar->id
            ];
        }

        return [
            'hosts' => $hosts
        ];
    }

    public function getTeamMembers()
    {
        $teamMembers = [];

        $calendars = Calendar::where('type', '!=', 'team')->get();

        foreach ($calendars as $calendar) {
            $user = get_user_by('ID', $calendar->user_id);
            if (!$user) {
                continue;
            }
            $name = trim($user->first_name . ' ' . $user->last_name);
            if (!$name) {
                $name = $user->display_name;
            }

            $isAdmin = user_can($user, 'manage_options');

            $data = [
                'id'               => $calendar->user_id,
                'name'             => $name,
                'email'            => $user->user_email,
                'avatar'           => $calendar->getAuthorPhoto(),
                'is_admin'         => $isAdmin,
                'is_calendar_user' => true,
            ];

            if (!$isAdmin) {
                $permissions = PermissionManager::getMetaPermissions($user->ID);
                if (!$permissions) {
                    $permissions = ['manage_own_calendar'];
                }
                $data['permissions'] = $permissions;
            }

            $teamMembers[$user->ID] = $data;
        }

        $userIds = array_keys($teamMembers);

        $otherReadonlyUsers = Meta::where('object_type', 'user_meta')
            ->where('key', '_access_permissions');

        if ($userIds) {
            $otherReadonlyUsers = $otherReadonlyUsers->whereNotIn('object_id', $userIds);
        }

        $otherReadonlyUsers = $otherReadonlyUsers->get();

        foreach ($otherReadonlyUsers as $meta) {
            $user = get_user_by('ID', $meta->object_id);
            if (!$user) {
                $meta->delete();
                continue;
            }

            if (user_can($user, 'manage_options') && !$meta->meta_value) {
                $meta->delete();
                continue;
            }

            $name = trim($user->first_name . ' ' . $user->last_name);
            if (!$name) {
                $name = $user->display_name;
            }

            $data = [
                'id'          => (string) $meta->object_id,
                'name'        => $name,
                'email'       => $user->user_email,
                'avatar'      => apply_filters('fluent_booking/author_photo', get_avatar_url($user->id), $user->id),
                'is_admin'    => false,
                'permissions' => $meta->value
            ];

            $teamMembers[$user->ID] = $data;
        }

        return [
            'members'         => array_values($teamMembers),
            'permission_sets' => PermissionManager::allPermissionSets()
        ];
    }

    public function updateMemberPermission(Request $request)
    {
        $this->validate($request->all(), [
            'user_id'     => 'required|int',
            'permissions' => 'required|array'
        ]);

        $user = get_user_by('ID', $request->get('user_id'));

        if (user_can($user, 'manage_options')) {
            return $this->sendError([
                'success' => false,
                'message' => __('This is an admin user. You can not change permissions', 'fluent-booking-pro')
            ]);
        }

        $permissions = $request->get('permissions');

        $permissionSets = PermissionManager::allPermissionSets();

        $validPermissions = array_intersect($permissions, array_keys($permissionSets));

        if (!in_array('manage_own_calendar', $validPermissions)) {
            $validPermissions[] = 'manage_own_calendar';
        }
        $validPermissions = array_unique($validPermissions);

        $meta = Meta::where('object_type', 'user_meta')
            ->where('object_id', $user->ID)
            ->where('key', '_access_permissions')
            ->first();

        $message = __('Access Permissions has been updated successfully', 'fluent-booking-pro');

        if ($meta) {
            $meta->value = $validPermissions;
            $meta->save();
        } else {
            Meta::create([
                'object_type' => 'user_meta',
                'object_id'   => $user->ID,
                'key'    => '_access_permissions',
                'value'  => $validPermissions
            ]);
            $message = __('New member has been added with the selected access permissions', 'fluent-booking-pro');
        }

        return [
            'message' => $message
        ];
    }

    public function deleteMember(Request $request, $id)
    {
        if (!$id) {
            return;
        }

        Meta::where('object_type', 'user_meta')
            ->where('object_id', $id)
            ->where('key', '_access_permissions')
            ->delete();

        $message = __('Member has been deleted successful', 'fluent-booking-pro');
        return [
            'message' => $message
        ];

    }
}
