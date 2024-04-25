<?php

namespace FluentBooking\App\Http\Controllers;

use FluentBooking\App\App;
use FluentBooking\App\Models\Booking;
use FluentBooking\App\Models\Calendar;
use FluentBooking\App\Models\BookingActivity;
use FluentBooking\App\Models\Meta;
use FluentBooking\App\Services\Helper;
use FluentBooking\App\Services\Integrations\PaymentMethods\CurrenciesHelper;
use FluentBooking\Framework\Support\Arr;
use FluentBooking\Framework\Request\Request;
use FluentBooking\App\Services\PermissionManager;
use FluentBooking\App\Services\CalendarService;
use FluentBooking\Framework\Pagination\LengthAwarePaginator;

class SchedulesController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->get('filters', []);

        $period = Arr::get($filters, 'period', 'upcoming');

        $eventId = Arr::get($filters, 'event');

        $eventType = Arr::get($filters, 'event_type');

        $query = Booking::with(['calendar_event']);

        $author = Arr::get($filters, 'author');

        if ($author !== 'all') {
            $author = (int)$author;
        }

        if (!PermissionManager::userCanSeeAllBookings()) {
            $authorCalendar = Calendar::where('user_id', get_current_user_id())
                ->where('type', '!=', 'team')
                ->first();

            if($authorCalendar) {
                $author = $authorCalendar->id;
            }
        }

        if ($author && $author !== 'all') {
            $query->where('calendar_id', $author);

            if ($eventId && $eventId !== 'all') {
                $query->where('event_id', $eventId);
            }

            if ($eventType && $eventType !== 'all') {
                $query->where('event_type', $eventType);
            }
        }

        do_action_ref_array('fluent_booking/schedules_query', [&$query]);

        $query->applyComputedStatus($period);

        if ($period == 'upcoming') {
            $query = $query->orderBy('start_time', 'ASC');
        } else if ($period == 'latest_bookings') {
            $query = $query->orderBy('created_at', 'DESC');
        } else if ($period == 'no_show') {
            $query = $query->where('status', 'no_show')->orderBy('start_time', 'DESC');
        } else if ($period == 'latest_bookings') {
            $query = $query->orderBy('id', 'DESC');
        } else {
            $query = $query->orderBy('start_time', 'DESC');
        }

        $query->groupBy('group_id');

        $search = Arr::get($filters, 'search');

        if (!empty($search)) {
            $author = 'all';
            $query = $query->orderBy('start_time', 'DESC');
            $query = $query->searchBy($search);
        }

        $schedules = $query->paginate();

        foreach ($schedules as $schedule) {
            $this->formatBooking($schedule);
        }

        $data = [
            'schedules' => $schedules,
            'timezone'  => 'UTC'
        ];

        $data['calendar_event_lists'] = CalendarService::getCalendarOptionsByTitle();

        if ($author && $author != 'all') {
            $slotOptions = CalendarService::getSlotOptions($author);
            $data['slot_options'] = $slotOptions;
        }

        if ($request->get('page') == 1) {
            if ($author && $author !== 'all') {
                $pendingCount = Booking::where('host_user_id', $author)
                    ->where('status', 'pending')
                    ->count();
            } else {
                $pendingCount = Booking::where('status', 'pending')->count();
            }

            $data['no_show_count'] = Booking::where('status', 'no_show')->count();
            $data['pending_count'] = $pendingCount;
            $data['cancelled_count'] = Booking::where('status', 'cancelled')->count();
        }

        return $data;
    }

    public function patchBooking(Request $request, $bookingId)
    {
        $booking = Booking::findOrFail($bookingId);
        $oldBooking = clone $booking;

        $data = $request->all();
        
        $this->validate($data, [
            'column' => 'required',
        ]);

        do_action('fluent_booking/before_patch_booking_schedule', $booking, $data);

        $value = $request->get('value');

        $column = $data['column'];

        if ($booking->{$column} == $value) {
            return $this->sendError(['message' => __('No changes found', 'fluent-booking-pro')]);
        }

        $validColumns = [
            'internal_note',
            'email',
            'phone',
            'first_name',
            'last_name',
            'status'
        ];

        if (!in_array($column, $validColumns)) {
            return $this->sendError(['message' => __('Invalid column', 'fluent-booking-pro')]);
        }

        if ($column === 'email') {
            if (!$value || !is_email($value)) {
                return $this->sendError(['message' => __('Invalid email address', 'fluent-booking-pro')]);
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
                return $this->sendError(['message' => __('Invalid status', 'fluent-booking-pro')]);
            }

            if ($value == 'scheduled') {
                if ($booking->payment_method && $booking->payment_order) {
                    $order = $booking->payment_order;
                    $order->total_paid = $order->total_amount;
                    $order->completed_at = gmdate('Y-m-d H:i:s'); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
                    $order->status = 'paid';
                    $order->save();

                    $updateData['payment_status'] = 'paid';

                    do_action('fluent_booking/log_booking_activity', $this->getPaymentLog($booking->id));

                    do_action('fluent_booking/payment/update_payment_status_paid', $booking);
                }
            }

            if ($value == 'cancelled') {
                $cancelReason = $data['cancel_reason'];
                $booking->cancelMeeting($cancelReason, 'host', get_current_user_id());

                if ($booking->payment_method && Arr::get($data, 'refund_payment') == 'yes') {
                    do_action('fluent_booking/refund_payment_' . $booking->payment_method, $booking, $booking->calendar_event);
                }

                return [
                    'message' => __('The booking has been cancelled', 'fluent-booking-pro')
                ];
            }
        }

        $updateData[$column] = $value;
        $booking->fill($updateData);
        $booking->save();

        if ($column === 'status') {
            do_action('fluent_booking/booking_schedule_' . $value, $booking, $booking->calendar_event);
        }

        do_action('fluent_booking/after_patch_booking_schedule', $booking, $oldBooking);

        do_action('fluent_booking/after_patch_booking_' . $column, $booking, $booking->calendar_event, $oldBooking->{$column});

        return [
            /* translators: Updated column name */
            'message' => sprintf(__('%s has been updated', 'fluent-booking-pro'), $column)
        ];
    }

    public function getBooking(Request $request, $bookingId)
    {
        $booking = Booking::with('slot');

        if (!PermissionManager::userCanSeeAllBookings()) {
            $booking->whereHas('calendar', function ($q) {
                $q->where('user_id', get_current_user_id());
            });
        }

        $booking = $booking->findOrFail($bookingId);
        $booking = $this->formatBooking($booking);

        do_action_ref_array('fluent_booking/booking_schedule', [&$booking]);

        $data = [
            'schedule' => $booking
        ];

        if (in_array('all_data', $this->request->get('with', []))) {
            $data = array_merge($data, $this->getBookingMetaInfo($request, $bookingId));
        }

        return $data;
    }

    public function deleteBooking(Request $request, $bookingId)
    {
        $booking = Booking::findOrFail($bookingId);

        $eventId = $booking->event_id;

        do_action('fluent_booking/before_delete_booking', $booking);

        $booking->delete();

        do_action('fluent_booking/after_delete_booking', $bookingId);

        if ($booking->event_type == 'group') {
            Booking::where('event_id', $eventId)->delete();
        }

        return [
            'message' => __('Booking Deleted Successfully!', 'fluent-booking-pro')
        ];
    }

    public function getGroupAttendees(Request $request, $groupId)
    {
        $booking = Booking::with('slot');

        if (!PermissionManager::userCanSeeAllBookings()) {
            $booking->whereHas('calendar', function ($q) {
                $q->where('user_id', get_current_user_id());
            });
        }

        $booking = $booking->where('group_id', $groupId)->first();

        if (!$booking || $booking->event_type != 'group') {
            return $this->sendError(['message' => __('Invalid group id or the event is not a group event', 'fluent-booking-pro')]);
        }

        $attendees = Booking::where('group_id', $booking->group_id);
        $search = sanitize_text_field($request->get('search'));

        if (!empty($search)) {
            $attendees = $attendees->searchBy($search);
        }
        $attendees = $attendees->paginate();

        foreach ($attendees as $attendee) {
            $attendee = $this->formatBooking($attendee);
        }

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

    public function getBookingMetaInfo(Request $request, $bookingId)
    {
        $booking = Booking::findOrFail($bookingId);

        $activities = BookingActivity::where('booking_id', $booking->id)
            ->orderBy('id', 'DESC')
            ->get();

        $sidebarContents = [];
        $mainBodyContents = [];

        if (defined('FLUENTCRM')) {
            $profileHtml = fluentcrm_get_crm_profile_html($booking->email, false);
            if ($profileHtml) {
                $sidebarContents[] = [
                    'id'      => 'fluent_crm_profule',
                    'title'   => __('CRM Profile', 'fluent-booking-pro'),
                    'content' => $profileHtml
                ];
            }
        }

        $order = null;
        if ($booking->payment_method && $booking->payment_order) {
            $order = $booking->payment_order;
            $order->load(['items', 'transaction']);
            $order->currency_sign = CurrenciesHelper::getCurrencySign($order->currency);
        }

        $mainBodyContents = apply_filters('fluent_booking/booking_meta_info_main_meta', $mainBodyContents, $booking);
        $mainBodyContents = apply_filters('fluent_booking/booking_meta_info_main_meta_' . $booking->source, $mainBodyContents, $booking);

        return [
            'activities'         => $activities,
            'sidebar_contents'   => $sidebarContents,
            'payment_order'      => $order,
            'main_body_contents' => $mainBodyContents
        ];
    }

    private function formatBooking(&$booking)
    {
        $autoCompleteTimeOut = (int) Helper::getGlobalAdminSetting('auto_complete_timing', 60) * 60; // 10 minutes

        if ($booking->status == 'scheduled' && (time() - strtotime($booking->end_time)) > $autoCompleteTimeOut) {
            $booking->status = 'completed';
            $booking->save();
            do_action('fluent_booking/booking_schedule_completed', $booking, $booking->calendar_event);
        }

        if ($booking->event_type == 'group') {
            $booking->booked_count = Booking::where('group_id', $booking->group_id)->count();
        } else {
            $booking->additional_guests = $booking->getAdditionalGuests();
        }

        $booking->author           = $booking->getHostDetails(false);
        $booking->location         = $booking->getLocationDetailsHtml();
        $booking->reschedule_url   = $booking->getRescheduleUrl();
        $booking->happening_status = $booking->getOngoingStatus();
        $booking->custom_form_data = $booking->getCustomFormData();

        $booking->slot = $booking->calendar_event;

        return $booking;
    }

    private function getPaymentLog($bookingId)
    {
        return [
            'booking_id'  => $bookingId,
            'status'      => 'closed',
            'type'        => 'success',
            'title'       => __('Payment Successfully Completed', 'fluent-booking-pro'),
            'description' => __('Payment marked as paid by admin', 'fluent-booking-pro')
        ];
    }

}
