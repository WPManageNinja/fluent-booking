<?php

namespace FluentCalendar\App\Http\Controllers;

use FluentCalendar\App\Models\Calendar;
use FluentCalendar\Framework\Request\Request;

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
                        'id'    => $user->ID,
                        'label' => $user->display_name . ' (' . $user->user_email . ')'
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
}
