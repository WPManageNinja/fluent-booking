<?php

namespace FluentCalendar\App\Http\Controllers;

use FluentCalendar\App\App;
use FluentCalendar\App\Models\Booking;
use FluentCalendar\App\Services\Helper;
use FluentCalendar\Framework\Support\Arr;
use FluentCalendar\Framework\Request\Request;
use FluentCalendar\App\Services\PermissionManager;
use FluentCalendar\Framework\Pagination\LengthAwarePaginator;

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
        } else if ($author !== 'all') {
            $author = (int)$author;
        }

        if (!PermissionManager::hasAllCalendarAccess()) {
            $author = get_current_user_id();
        }

        if ($author && $author !== 'all') {
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

        $schedules = $query->get();

        foreach ($schedules as $schedule) {
            if ($schedule->status == 'scheduled' && (time() - strtotime($schedule->end_time)) > 3600) {
                $schedule->status = 'completed';
                $schedule->save();
                do_action('fluent_calendar/schedule_completed', $schedule);
            }

            $schedule->happening_status = $schedule->getOngoingStatus();
            $schedule->location = $schedule->getLocationDetailsHtml();
            $schedule->author = $schedule->slot->getAuthorProfile(false);
        }

        $groupedSchedules = $schedules->groupBy('event_id');

        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);
        $total = $groupedSchedules->count();

        $filteredSchedules = [];

        if ($total) {
            $chunkedSchedules = $groupedSchedules->chunk($perPage);
            $filteredSchedules = $chunkedSchedules->get($page - 1, []);
        }
        
        $paginatedSchedules = new LengthAwarePaginator($filteredSchedules, $total, $perPage, $page);

        return [
            'schedules' => $paginatedSchedules,
            'timezone'  => 'UTC'
        ];
    }

    public function patchBooking(Request $request, $bookingId)
    {
        $booking = Booking::findOrFail($bookingId);
        $oldSBooking = clone $booking;

        $data = $request->all();
        $this->validate($data, [
            'column' => 'required',
        ]);

        do_action('fluent_calendar/before_patch_booking_schedule', $booking, $data);

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

        if (!in_array($column, $validColumns)) {
            return $this->sendError(['message' => 'Invalid column']);
        }

        if ($column === 'email') {
            if (!$value || !is_email($value)) {
                return $this->sendError(['message' => 'Invalid email address']);
            }
            $value = sanitize_email($value);
        } else if ($column === 'internal_note') {
            $value = sanitize_textarea_field($value);
        } else {
            $value = sanitize_textarea_field($value);
        }

        if ($column == 'status') {
            $value = sanitize_text_field($value);
            if (!in_array($value, ['scheduled', 'completed', 'cancelled', 'no_show'])) {
                return $this->sendError(['message' => 'Invalid status']);
            }

            if ($value == 'cancelled') {
                $updateData['cancelled_by'] = get_current_user_id();
            }
        }

        $updateData[$column] = $value;
        $booking->fill($updateData);
        $booking->save();

        if ($column === 'status' && $oldSBooking->status != $booking->status) {

            if ($value == 'cancelled') {
                $title = sprintf(__('Cancelled By %s', 'fluent-calendar'), Helper::getUserDisplayName());
                $booking->addCancelReason($title, sanitize_textarea_field($request->get('cancel_reason')));
            }

            do_action('fluent_calendar/booking_schedule_' . $value, $booking);
        }

        do_action('fluent_calendar/after_patch_booking_schedule', $booking, $oldSBooking);

        return [
            'message' => sprintf(__('%s has been updated', 'fluent-calendar'), $column)
        ];
    }

    public function getBooking(Request $request, $bookingId)
    {
        $isAdmin = current_user_can('manage_options');

        $booking = Booking::with('slot');

        if (!$isAdmin) {
            $booking->whereHas('calendar', function ($q) {
                $q->where('user_id', get_current_user_id());
            });
        }

        $booking = $booking->findOrFail($bookingId);

        if ($booking->status == 'scheduled' && (time() - strtotime($booking->end_time)) > 3600) {
            $booking->status = 'completed';
            $booking->save();
            do_action('fluent_calendar/booking_schedule_completed', $booking);
        }

        $booking->happening_status = $booking->getOngoingStatus();
        $booking->author = $booking->slot->getAuthorProfile(false);
        $booking->location = $booking->getLocationDetailsHtml();

        return [
            'schedule' => [$booking]
        ];
    }

    public function getBookingActivities(Request $request, $bookingId)
    {
        $isAdmin = current_user_can('manage_options');

        if ($isAdmin) {
            $booking = Booking::findOrFail($bookingId);

        } else {
            $booking = Booking::whereHas('calendar', function ($q) {
                $q->where('user_id', get_current_user_id());
            })->findOrFail($bookingId);
        }

        return [
            'activities' => $booking->getActivities()
        ];
    }

}
