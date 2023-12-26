<?php

namespace FluentBooking\App\Models;

use FluentBooking\App\Models\Model;
use FluentBooking\App\Services\BookingFieldService;
use FluentBooking\App\Services\DateTimeHelper;
use FluentBooking\App\Services\Helper;
use FluentBooking\Framework\Support\Arr;

class Booking extends Model
{
    protected $table = 'fcal_bookings';

    protected $guarded = ['id'];

    private static $bookingType = 'scheduling';

    protected $fillable = [
        'calendar_id',
        'event_id',
        'parent_id',
        'group_id',
        'hash',
        'person_user_id',
        'host_user_id',
        'person_contact_id',
        'person_time_zone',
        'start_time',
        'end_time',
        'slot_minutes',
        'first_name',
        'last_name',
        'email',
        'message',
        'internal_note',
        'phone',
        'country',
        'ip_address',
        'browser',
        'device',
        'other_info',
        'location_details',
        'cancelled_by',
        'status',
        'payment_method',
        'payment_status',
        'event_type',
        'source',
        'source_id',
        'source_url',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term'
    ];


    /**
     * $searchable Columns in table to search
     * @var array
     */
    protected $searchable = [
        'email',
        'first_name',
        'last_name'
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!isset($model->person_user_id) && $userId = get_current_user_id()) {
                $model->person_user_id = $userId;
            }

            if (is_null($model->group_id) || !isset($model->group_id)) {
                $lastEvent = static::orderBy('group_id', 'desc')->first(['group_id']);
                $nextEventId = $lastEvent ? $lastEvent->group_id + 1 : 1;
                $model->group_id = $nextEventId;
            }

            if (defined('FLUENTCRM') && !empty($model->email) && apply_filters('fluent_calender/auto_booking_fluent_crm_sync', true)) {
                $contact = FluentCrmApi('contacts')->getContact($model->email);
                if ($contact) {
                    $model->person_contact_id = $contact->id;
                }
            }

            if (empty($model->booking_type)) {
                $model->booking_type = self::$bookingType;
            }

            $model->hash = md5(wp_generate_uuid4() . time());
        });

        static::deleting(function ($model) { // before delete() method call this
            $model->hosts()->delete();
            $model->booking_meta()->delete();
        });

        static::addGlobalScope('main_bookings', function ($builder) {
            $builder->where('booking_type', self::$bookingType);
        });
    }

    public function calendar()
    {
        return $this->belongsTo(Calendar::class, 'calendar_id');
    }

    public function slot()
    {
        return $this->belongsTo(CalendarSlot::class, 'event_id');
    }

    public function calendar_event()
    {
        return $this->belongsTo(CalendarSlot::class, 'event_id');
    }

    public function booking_meta()
    {
        return $this->hasMany(BookingMeta::class, 'booking_id');
    }

    public function getCustomFormData($isFormatted = true)
    {
        if ($isFormatted) {
            return BookingFieldService::getFormattedCustomBookingData($this);
        }

        return $this->getMeta('custom_fields_data', []);
    }

    public function hosts()
    {
        $class = __NAMESPACE__ . '\User';

        return $this->belongsToMany(
            $class,
            'fcal_booking_hosts',
            'booking_id',
            'user_id'
        )
            ->withPivot('status')
            ->withTimestamps();
    }

    public function scopeUpcoming($query)
    {
        return $query->where('end_time', '>=', gmdate('Y-m-d H:i:s')); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
    }

    public function scopePast($query)
    {
        return $query->where('end_time', '<', gmdate('Y-m-d H:i:s')); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
    }

    public function scopeApplyComputedStatus($query, $status)
    {
        $validStatuses = [
            'upcoming',
            'completed',
            'cancelled',
            'pending'
        ];

        if (!in_array($status, $validStatuses)) {
            return $query;
        }

        if ($status == 'upcoming') {
            return $query->where('end_time', '>=', gmdate('Y-m-d H:i:s')) // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
            ->where('status', 'scheduled');
        }

        if ($status == 'completed') {
            return $query->where('end_time', '<', gmdate('Y-m-d H:i:s')) // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
            ->where('status', '!=', 'cancelled')
                ->orWhere('status', 'completed'); // maybe cron did not mark few as completed yet
        }

        return $query->where('status', $status);
    }

    public function getFullBookingDateTimeText($timeZone = 'UTC', $isHtml = false)
    {
        $startDateTime = DateTimeHelper::convertFromUtc($this->start_time, $timeZone, 'Y-m-d H:i:s');
        $endDateTime = DateTimeHelper::convertFromUtc($this->end_time, $timeZone, 'Y-m-d H:i:s');

        $html = DateTimeHelper::formatToLocale($startDateTime, 'time') . ' - ' . DateTimeHelper::formatToLocale($endDateTime, 'time') . ', ';
        $html .= DateTimeHelper::formatToLocale($startDateTime, 'date');

        if ($isHtml && $this->status == 'cancelled') {
            $html = '<del>' . $html . '</del>';
        }

        return $html;
    }

    public function getShortBookingDateTime($timeZone = 'UTC')
    {
        // date format for Fri Feb 10, 2023
        $html = DateTimeHelper::convertFromUtc($this->start_time, $timeZone, 'D M d, Y');
        $html .= ' ' . DateTimeHelper::convertFromUtc($this->start_time, $timeZone, 'h:ia');

        return $html;
    }

    public function getPreviousMeetingTime($timeZone = 'UTC')
    {
        $previousMeetingTime = $this->getMeta('previous_meeting_time');
        $html = DateTimeHelper::convertFromUtc($previousMeetingTime, $timeZone, 'D M d, Y');
        $html .= ' ' . DateTimeHelper::convertFromUtc($previousMeetingTime, $timeZone, 'h:ia');

        return $html;
    }

    public function getLocationDetailsHtml()
    {
        $details = $this->location_details;
        $locationType = Arr::get($details, 'type');

        if (!$locationType) {
            return '--';
        }

        if ($locationType == 'in_person_guest') {
            return '<b>' . __('Invitee Address:', 'fluent-booking-pro') . ' </b>' . Arr::get($details, 'description');
        }

        if ($locationType == 'in_person_organizer') {
            $html = '<b>' . Arr::get($details, 'title') . ' </b>';
            if ($description = Arr::get($details, 'description')) {
                $html .= wpautop($description);
            }
            return $html;
        }

        if ($locationType == 'phone_guest') {
            return '<b>' . __('Phone Call:', 'fluent-booking-pro') . ' </b>' . $this->phone;
        }

        if ($locationType == 'phone_organizer') {
            return '<b>' . __('Phone Call:', 'fluent-booking-pro') . ' </b>' . Arr::get($details, 'description') . __('(Host phone number)', 'fluent-booking-pro');
        }

        if ($locationType == 'custom') {
            $html = '<b>' . Arr::get($details, 'title') . '</b>';
            $html .= wpautop(Arr::get($details, 'description'));
            return $html;
        }

        if (in_array($locationType, ['google_meet', 'online_meeting', 'zoom_meeting', 'ms_teams'])) {
            $platformLabels = [
                'google_meet'    => __('Google Meet', 'fluent-booking-pro'),
                'online_meeting' => __('Online Meeting', 'fluent-booking-pro'),
                'zoom_meeting'   => __('Zoom Video', 'fluent-booking-pro'),
                'ms_teams'       => __('MS Teams', 'fluent-booking-pro'),
            ];

            $html = '<b>' . $platformLabels[$locationType] . '</b> ';

            if ($meetingLink = Arr::get($details, 'online_platform_link')) {
                $html .= '<a target="_blank" href="' . esc_url($meetingLink) . '">' . __('Joining Meeting', 'fluent-booking-pro') . '</a>';
            }

            return $html;
        }

        return '--';
    }

    public function getLocationAsText()
    {
        $details = $this->location_details;

        $locationType = Arr::get($details, 'type');
        $meetingLink = Arr::get($details, 'online_platform_link');

        $onlinePlatforms = ['google_meet', 'zoom_meeting', 'online_meeting', 'ms_teams'];

        if ($meetingLink && in_array($locationType, $onlinePlatforms)) {
            return $meetingLink;
        }

        if ($locationType == 'phone_organizer') {
            return Arr::get($details, 'description');
        }

        if ($locationType == 'phone_guest') {
            return $this->phone;
        }

        return wp_strip_all_tags($this->getLocationDetailsHtml());
    }

    public function getMessage()
    {
        if (empty($this->message)) {
            return 'n/a';
        }
        return $this->message;
    }

    public function setLocationDetailsAttribute($locationDetails)
    {
        $this->attributes['location_details'] = \maybe_serialize($locationDetails);
    }

    public function getLocationDetailsAttribute($locationDetails)
    {
        return \maybe_unserialize($locationDetails);
    }

    public function getOngoingStatus()
    {
        if ($this->status == 'cancelled') {
            return '';
        }

        $currentTime = time();
        $startTime = strtotime($this->start_time);
        $endTime = strtotime($this->end_time);

        if ($currentTime > $startTime && $currentTime < $endTime) {
            return 'happening_now';
        } elseif (($startTime - $currentTime) < 1800 && ($startTime - $currentTime) > 0) {
            return 'starting_soon';
        } else if (($endTime - $currentTime) > -3600 && ($endTime - $currentTime) < 0) {
            return 'recently_happened';
        }

        return '';
    }

    public function payment_order()
    {
        return $this->hasOne(Order::class, 'parent_id');
    }

    public function getCancelReason($isHtml = false)
    {
        $row = BookingActivity::where('booking_id', $this->id)
            ->where('type', 'cancel_reason')
            ->first();

        if ($isHtml && $row) {
            return $row->description;
        }

        return $row;
    }

    public function getCancelReasonDescription()
    {
        $cancelReason = $this->getCancelReason();

        if ($cancelReason) {
            return $cancelReason->description;
        }

        return '';
    }

    public function addCancelReason($title, $reason)
    {
        if (!$reason && !$title) {
            return null;
        }

        $exist = $this->getCancelReason();

        if ($exist) {
            $exist->title = $title;
            $exist->description = $reason;
            $exist->save();
            return $exist;
        }

        return BookingActivity::create([
            'booking_id'  => $this->id,
            'type'        => 'cancel_reason',
            'title'       => $title,
            'description' => $reason
        ]);
    }

    public function cancelMeeting($reason = '', $cancelledByType = 'guest', $cancelledByUserId = null)
    {
        if ($this->status == 'cancelled') {
            return $this;
        }

        $cancellableStatuses = [
            'scheduled',
            'pending'
        ];

        if (!in_array($this->status, $cancellableStatuses)) {
            return new \WP_Error('invalid_status', __('This booking is not cancellable.', 'fluent-booking-pro'));
        }

        $this->status = 'cancelled';
        if ($cancelledByUserId) {
            $this->cancelled_by = $cancelledByUserId;
        }

        if (!$cancelledByUserId) {
            $cancelledByUserId = get_current_user_id();
        }

        $this->save();
        $this->updateMeta('cancelled_by_type', $cancelledByType);

        if ($reason) {
            $userName = $cancelledByType;
            if ($cancelledByUserId && $user = get_user_by('ID', $cancelledByUserId)) {
                $userName = $user->display_name;
            }

            $this->addCancelReason(sprintf(__('Meeting has been cancelled by %s', 'fluent-booking-pro'), $userName), $reason);
        }

        do_action('fluent_booking/booking_schedule_cancelled', $this, $this->calendar_event);
    }

    public function getRescheduleReason()
    {
        return $this->getMeta('reschedule_reason', '');
    }

    public function getMeetingTitle()
    {
        $calendarSlot = $this->calendar_event;

        $author = $this->getHostDetails(false);

        $guestName = trim($this->first_name . ' ' . $this->last_name);

        return sprintf(__('%1s Meeting between %2s and %3s', 'fluent-booking-pro'), $calendarSlot->title, $guestName, $author['name']);
    }

    public function getActivities()
    {
        return BookingActivity::where('booking_id', $this->id)
            ->orderBy('id', 'DESC')
            ->get();
    }

    public function updateMeta($key, $value)
    {
        $exist = BookingMeta::where('booking_id', $this->id)
            ->where('meta_key', $key)
            ->first();

        if ($exist) {
            $exist->value = $value;
            $exist->save();
            return $exist;
        }

        return BookingMeta::create([
            'booking_id' => $this->id,
            'meta_key'   => $key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
            'value'      => $value
        ]);
    }

    public function getMeta($key, $default = '')
    {
        $exist = BookingMeta::where('booking_id', $this->id)
            ->where('meta_key', $key)
            ->first();

        if ($exist) {
            return $exist->value;
        }

        return $default;
    }


    /**
     * Local scope to filter hosts by search/query string
     * @param string $search
     */
    public function scopeSearchBy($query, $search)
    {
        if ($search) {
            $fields = $this->searchable;
            $query->where(function ($query) use ($fields, $search) {
                $query->where(array_shift($fields), 'LIKE', "%$search%");

                $nameArray = explode(' ', $search);
                if (count($nameArray) >= 2) {
                    $query->orWhere(function ($q) use ($nameArray) {
                        $fname = array_shift($nameArray);
                        $lastName = implode(' ', $nameArray);
                        $q->where('first_name', 'LIKE', "%$fname%")
                            ->orWhere('last_name', 'LIKE', "%$lastName%");
                    });
                }

                foreach ($fields as $field) {
                    $query->orWhere($field, 'LIKE', "%$search%");
                }
            });
        }

        return $query;
    }

    public function getConfirmationUrl()
    {
        return add_query_arg([
            'fluent-booking' => 'booking',
            'meeting_hash'   => $this->hash,
            'type'           => 'confirmation',
        ], Helper::getBookingReceiptLandingBaseUrl());
    }

    public function getAdminViewUrl()
    {
        return Helper::getAppBaseUrl('scheduled-events?period=upcoming&booking_id=' . $this->id);
    }

    public function getIcsDownloadUrl()
    {
        return add_query_arg([
            'fluent-booking' => 'booking',
            'meeting_hash'   => $this->hash,
            'type'           => 'confirmation',
            'ics'            => 'download',
        ], Helper::getBookingReceiptLandingBaseUrl());
    }

    public function getRescheduleUrl()
    {
        return add_query_arg([
            'fluent-booking' => 'booking',
            'meeting_hash'   => $this->hash,
            'type'           => 'reschedule',
        ], Helper::getBookingReceiptLandingBaseUrl());
    }

    public function getCancelUrl()
    {
        return add_query_arg([
            'fluent-booking' => 'booking',
            'meeting_hash'   => $this->hash,
            'type'           => 'cancel',
        ], Helper::getBookingReceiptLandingBaseUrl());
    }

    public function canCancel()
    {
        $hasPermission = Arr::get($this->calendar_event, 'settings.can_cancel', 'yes') == 'yes';
        $isCancelable  = in_array($this->status, ['scheduled', 'pending']);

        return $hasPermission && $isCancelable;
    }

    public function canReschedule()
    {
        $hasPermission   = Arr::get($this->calendar_event, 'settings.can_reschedule', 'yes') == 'yes';
        $isReschedulable = in_array($this->status, ['scheduled', 'pending']);
        
        return $hasPermission && $isReschedulable;
    }

    public function getHostDetails($isPublic = true)
    {
        if ($this->host_user_id && $user = get_user_by('ID', $this->host_user_id)) {
            $name = trim($user->first_name . ' ' . $user->last_name);
            if (!$name) {
                $name = $user->display_name;
            }
            $data = [
                'name'       => $name,
                'email'      => $user->user_email,
                'first_name' => $user->first_name,
                'last_name'  => $user->last_name,
            ];
        } else {
            $data = $this->calendar->getAuthorProfile(false);
        }

        if ($isPublic) {
            unset($data['email']);
        }

        return $data;
    }

    public function getAdditionalData($isHtml = false)
    {
        $customData = BookingFieldService::getFormattedCustomBookingData($this);

        if (!$customData) {
            return '';
        }

        if (!$isHtml) {
            $lines = array_filter(array_map(function ($data) {
                return !empty($data['value']) ? $data['label'] . ': ' . PHP_EOL . esc_html($data['value']) : null;
            }, $customData));
        
            return implode(PHP_EOL . PHP_EOL, $lines);
        }

        $html = '<table>';
        foreach ($customData as $data) {
            if (empty($data['value'])) {
                continue;
            }
            $html .= '<tr>';
            $html .= '<td><b>' . $data['label'] . '</b></td>';
            $html .= '<td>' . $data['value'] . '</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';

        return $html;
    }

}
