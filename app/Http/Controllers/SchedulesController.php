<?php

namespace FluentBooking\App\Http\Controllers;

use FluentBooking\App\App;
use FluentBooking\App\Models\Booking;
use FluentBooking\App\Models\BookingActivity;
use FluentBooking\App\Models\Meta;
use FluentBooking\App\Models\Order;
use FluentBooking\App\Models\Transactions;
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

        $slotId = Arr::get($filters, 'event_type');

        $query = Booking::with(['calendar_event']);

        $author = Arr::get($filters, 'author');

        if ($author == 'me') {
            $author = get_current_user_id();
        } else if ($author !== 'all') {
            $author = (int)$author;
        }

        if (!PermissionManager::userCanSeeAllBookings()) {
            $author = get_current_user_id();
        }

        if ($author && $author !== 'all') {
            $query->where('host_user_id', $author);

            if ($slotId && $slotId !== 'all') {
                $query->where('event_id', $slotId);
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

        if ($author && $author != 'all') {
            $slotOptions = CalendarService::getSlotOptions($author);
            $data['slotOptions'] = $slotOptions;
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

            if ($value == 'cancelled') {
                $cancelReason = $data['cancel_reason'];
                $booking->cancelMeeting($cancelReason, 'host', get_current_user_id());
                return [
                    'message' => __('The booking has been cancelled', 'fluent-booking-pro')
                ];
            }
        }

        $updateData[$column] = $value;
        $booking->fill($updateData);
        $booking->save();

        if ($column === 'status' && $oldSBooking->status != $booking->status) {
            do_action('fluent_booking/booking_schedule_' . $value, $booking, $booking->calendar_event);
        }

        do_action('fluent_booking/after_patch_booking_schedule', $booking, $oldSBooking);

        return [
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

        do_action('fluent_booking/before_delete_booking', $booking);

        $booking->delete();

        do_action('fluent_booking/after_delete_booking', $bookingId);

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
        if ($booking->payment_method == 'stripe' && $booking->payment_order) {
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


    private function formatBooking(&$booking)
    {
        if ($booking->status == 'scheduled' && (time() - strtotime($booking->end_time)) > 3600) {
            $booking->status = 'completed';
            $booking->save();
            do_action('fluent_booking/booking_schedule_completed', $booking, $booking->calendar_event);
        }

        $booking->happening_status = $booking->getOngoingStatus();
        $booking->location = $booking->getLocationDetailsHtml();
        $booking->custom_form_data = $booking->getCustomFormData();
        $booking->reschedule_url = $booking->getRescheduleUrl();

        if (!$booking->calendar_event) {
            $booking->author = [
                'name' => 'unknown'
            ];
            $booking->slot = (object)[];
        } else {
            $booking->author = $booking->calendar_event->getAuthorProfile(false);
        }
        
        if ($booking->event_type == 'group') {
            $booking->booked_count = Booking::where('group_id', $booking->group_id)->count();
        }

        $booking->slot = $booking->calendar_event;

        return $booking;
    }

}
