<?php

namespace FluentCalendar\App\Http\Controllers;

use FluentCalendar\App\App;
use FluentCalendar\App\Models\Booking;
use FluentCalendar\Framework\Request\Request;
use FluentCalendar\Framework\Support\Arr;

class SchedulesController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->get('filters', []);

        $period = Arr::get($filters, 'period', 'upcoming');

        $query = Booking::with('slot');

        $author = Arr::get($filters, 'author');

        if ($author == 'me') {
            $author = get_current_user_id();
        } else {
            $author = (int) $author;
        }

        if(!current_user_can('manage_options')) {
            $author = get_current_user_id();
        }

        if ($author) {
            $query->whereHas('calendar', function ($q) use ($author) {
                $q->where('user_id', $author);
            });
        }

        do_action_ref_array('fluent_calendar/schedules_query', [&$query]);

        if ($period == 'upcoming') {
            $query = $query->orderBy('start_time', 'ASC')->upcoming();
        } else {
            $query = $query->orderBy('start_time', 'DESC')->past();
        }

        $schedules = $query->paginate();

        foreach ($schedules as $schedule) {
            if ($schedule->status == 'scheduled' && (time() - strtotime($schedule->end_time)) > 3600) {
                $schedule->status = 'completed';
                $schedule->save();
                do_action('fluent_calendar/schedule_completed', $schedule);
                continue;
            }

            $schedule->happening_status = $schedule->getOngoingStatus();
            $schedule->author = $schedule->slot->getAuthorProfile(false);
        }

        return [
            'schedules' => $schedules,
            'timezone'  => 'UTC'
        ];
    }

    public function patchSpot(Request $request, $spot_id)
    {
        $oldSpot = $spot = Booking::findOrFail($spot_id);

        $data = $request->all();
        $this->validate($data, [
            'column' => 'required',
        ]);

        do_action('fluent_calendar/before_patch_schedule', $spot, $data);

        $value = $request->get('value');
        $column = $data['column'];


        $validColumns = [
            'internal_note',
            'email',
            'phone',
            'first_name',
            'last_name',
            'status'
        ];

        if(!in_array($column, $validColumns)) {
            return $this->sendError(['message' => 'Invalid column']);
        }

        if ($column === 'email') {
            if (!$value || !is_email($value)) {
                return $this->sendError(['message' => 'Invalid email address']);
            }
            $value = sanitize_email($value);
        } else if($column === 'internal_note') {
            $value = sanitize_textarea_field($value);
        } else {
            $value = sanitize_textarea_field($value);
        }

        $updateData[$column] = $value;
        $spot->fill($updateData);
        $spot->save();

        if($column === 'status') {
            do_action('fluent_calendar/schedule_'.$value, $spot);
        }

        do_action('fluent_calendar/after_patch_schedule', $spot, $oldSpot);

        return [
            'message' => sprintf(__('%s has been updated', 'fluent-calendar'), $column)
        ];
    }

    public function getSpot(Request $request, $spot_id)
    {
        $isAdmin = current_user_can('manage_options');

        $spot = Booking::with('slot');

        if(!$isAdmin) {
            $spot->whereHas('calendar', function ($q) {
                $q->where('user_id', get_current_user_id());
            });
        }

        $schedule = $spot->findOrFail($spot_id);

        if ($schedule->status == 'scheduled' && (time() - strtotime($schedule->end_time)) > 3600) {
            $schedule->status = 'completed';
            $schedule->save();
            do_action('fluent_calendar/schedule_completed', $schedule);
        }

        $schedule->happening_status = $schedule->getOngoingStatus();
        $schedule->author = $schedule->slot->getAuthorProfile(false);

        return [
            'spot' => $schedule
        ];
    }
}
