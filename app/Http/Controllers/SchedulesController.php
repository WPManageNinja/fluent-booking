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
            $author = (int)$author;
        }

        if ($author) {
            $query->whereHas('calendar', function ($q) use ($author) {
                $q->where('user_id', $author);
            });
        }

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
                continue;
            }

            $schedule->happening_status = $schedule->getOngoingStatus();
        }


        return [
            'schedules' => $schedules,
            'timezone'  => 'UTC'
        ];
    }

    public function patchSpot(Request $request, $spot_id)
    {
        $spot = Booking::findOrFail($spot_id);

        $data = $request->all();
        $this->validate($data, [
            'column' => 'required|in:internal_note,email,phone,first_name,last_name',
        ]);

        $value = $request->get('value');
        $column = $data['column'];
        if ($column === 'email') {
            if (!$value || !is_email($value)) {
                return $this->sendError(['message' => 'Invalid email address']);
            }
            $value = sanitize_email($value);
        } else {
            $value = sanitize_textarea_field($value);
        }

        $updateData[$column] = $value;
        $spot->fill($updateData);
        $spot->save();

        return [
            'message' => sprintf(__('%s has been updated', 'fluent-calendar'), $column)
        ];
    }
}
