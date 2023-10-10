<?php

namespace FluentBooking\App\Http\Controllers;

use FluentBooking\App\App;
use FluentBooking\App\Models\Booking;
use FluentBooking\App\Models\BookingActivity;
use FluentBooking\App\Services\Helper;
use FluentBooking\Framework\Support\Arr;
use FluentBooking\Framework\Request\Request;
use FluentBooking\App\Services\PermissionManager;
use FluentBooking\Framework\Pagination\LengthAwarePaginator;

class SchedulesController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->get('filters', []);

        $period = Arr::get($filters, 'period', 'upcoming');

        $query = Booking::with(['slot', 'custom_field']);

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

        do_action_ref_array('fluent_booking/schedules_query', [&$query]);

        $query->applyComputedStatus($period);

        if ($period == 'upcoming') {
            $query = $query->orderBy('start_time', 'ASC');
        } else {
            $query = $query->orderBy('start_time', 'DESC');
        }

        $query->groupBy('group_id');

        $schedules = $query->paginate();

        foreach ($schedules as $schedule) {
            if ($schedule->status == 'scheduled' && (time() - strtotime($schedule->end_time)) > 3600) {
                $schedule->status = 'completed';
                $schedule->save();
                do_action('fluent_booking/booking_schedule_completed', $schedule);
            }

            $schedule->happening_status = $schedule->getOngoingStatus();
            $schedule->location = $schedule->getLocationDetailsHtml();

            if (!$schedule->slot) {
                $schedule->author = [
                    'name' => 'unknown'
                ];
                $schedule->slot = (object)[];
            } else {
                $schedule->author = $schedule->slot->getAuthorProfile(false);
            }

            if ($schedule->event_type == 'group') {
                $schedule->booked_count = Booking::where('group_id', $schedule->group_id)->count();
            }

            do_action_ref_array('fluent_booking/booking_schedule', [&$schedule]);
        }

        return $this->sendSuccess([
            'schedules' => $schedules,
            'timezone'  => 'UTC'
        ]);
    }

    public function patchBooking(Request $request, $bookingId)
    {
        $booking = Booking::findOrFail($bookingId);
        $oldSBooking = clone $booking;

        $data = $request->all();
        $this->validate($data, [
            'column' => 'required',
        ]);

        do_action('fluent_booking/before_patch_booking_schedule', $booking, $data);

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
                $title = sprintf(__('Cancelled By %s', 'fluent-booking'), Helper::getUserDisplayName());
                $booking->addCancelReason($title, sanitize_textarea_field($request->get('cancel_reason')));
            }

            do_action('fluent_booking/booking_schedule_' . $value, $booking);
        }

        do_action('fluent_booking/after_patch_booking_schedule', $booking, $oldSBooking);

        return $this->sendSuccess([
            'message' => sprintf(__('%s has been updated', 'fluent-booking'), $column)
        ]);
    }

    public function getBooking(Request $request, $bookingId)
    {
        $isAdmin = current_user_can('manage_options');

        $booking = Booking::with(['slot', 'custom_field']);

        if (!$isAdmin) {
            $booking->whereHas('calendar', function ($q) {
                $q->where('user_id', get_current_user_id());
            });
        }

        $booking = $booking->findOrFail($bookingId);

        if ($booking->status == 'scheduled' && (time() - strtotime($booking->end_time)) > 3600) {
            $booking->status = 'completed';
            $booking->save();
            do_action('fluent_booking/booking_schedule_completed', $booking);
        }

        $booking->happening_status = $booking->getOngoingStatus();

        if ($booking->slot) {
            $booking->author = $booking->slot->getAuthorProfile(false);
        }

        $booking->location = $booking->getLocationDetailsHtml();

        do_action_ref_array('fluent_booking/booking_schedule', [&$booking]);

        return [
            'schedule' => $booking
        ];
    }

    public function getGroupAttendees(Request $request, $groupId)
    {
        $isAdmin = current_user_can('manage_options');

        $booking = Booking::with('slot');

        if (!$isAdmin) {
            $booking->whereHas('calendar', function ($q) {
                $q->where('user_id', get_current_user_id());
            });
        }

        $booking = $booking->where('group_id', $groupId)->first();

        if (!$booking || $booking->event_type != 'group') {
            return $this->sendError(['message' => 'Invalid group id or the event is not a group event']);
        }

        $attendees = Booking::with('custom_field')
            ->where('group_id', $booking->group_id)
            ->paginate();

        return [
            'attendees' => $attendees
        ];
    }

    public function getBookingActivities(Request $request, $bookingId)
    {
        $activities = BookingActivity::where('booking_id', $bookingId)
            ->orderBy('id', 'DESC')
            ->get();

        return [
            'activities' => $activities
        ];
    }


    public function getCrmProfile(Request $request)
    {
        $email = $request->get('crmProfile');

        if (!defined('FLUENTCRM')) {
            return '';
        }

        // Attempt to retrieve the CRM profile HTML for the provided email address.
        $profileHtml = fluentcrm_get_crm_profile_html($email, false);

        if (!$profileHtml) {
            return '';
        }

        return $this->sendSuccess([
            'crm_profile' => $profileHtml
        ]);
    }

}
