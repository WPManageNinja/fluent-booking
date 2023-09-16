<?php

namespace FluentBooking\App\Http\Controllers;

use FluentBooking\App\Models\Calendar;
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

        $users = get_users([
            'role__not_in' => ['subscriber'],
            'number'       => 50,
            'search'       => sanitize_text_field($request->get('search'))
        ]);

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
            ->get();

        $allHosts = [];

        foreach ($calendars as $calendar) {
            $userName = 'Deleted User';
            if ($calendar->user) {
                $userName = $calendar->user->full_name;
            }

            if ($currentUserId == $calendar->user_id) {
                $userName = __('My Meetings', 'fluent-calendar');
            }

            $allHosts[] = [
                'id'    => (int) $calendar->user_id,
                'label' => $userName
            ];
        }

        return [
            'hosts' => $allHosts
        ];
    }
}
