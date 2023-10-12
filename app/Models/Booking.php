<?php

namespace FluentBooking\App\Models;

use FluentBooking\App\Models\Model;
use FluentBooking\App\Services\BookingFieldService;
use FluentBooking\App\Services\DateTimeHelper;
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

    public static function boot()
    {
        parent::boot();

        static::creating( function ($model) {
            if (!isset($model->person_user_id) && $userId = get_current_user_id()) {
                $model->person_user_id = $userId;
            }

            if (is_null($model->group_id)) {
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

    public function order()
    {
        return $this->hasOne(Order::class, 'parent_id');
    }

    public function calendar_event()
    {
        return $this->belongsTo(CalendarSlot::class, 'event_id');
    }

    public function getCustomFormData($isFormatted = true)
    {
        if($isFormatted) {
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
        return $query->where('end_time', '>=', date('Y-m-d H:i:s'));
    }

    public function scopePast($query)
    {
        return $query->where('end_time', '<', date('Y-m-d H:i:s'));
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
            return $query->where('end_time', '>=', date('Y-m-d H:i:s'))
                ->where('status', 'scheduled');
        }

        if ($status == 'completed') {
            return $query->where('end_time', '<', date('Y-m-d H:i:s'))
                ->whereIn('status', ['scheduled', 'completed']); // maybe cron did not mark few as completed yet
        }

        return $query->where('status', $status);
    }

    public function getFullBookingDateTimeText($timeZone = 'UTC')
    {
        $html = DateTimeHelper::convertFromUtc($this->start_time, $timeZone, 'h:ia');
        $html .= ' - ' . DateTimeHelper::convertFromUtc($this->end_time, $timeZone, 'h:ia') . ', ';
        $html .= DateTimeHelper::convertFromUtc($this->start_time, $timeZone, 'l, F d, Y');
        return $html;
    }

    public function getShortBookingDateTime($timeZone = 'UTC')
    {
        // date format for Fri Feb 10, 2023
        $html = DateTimeHelper::convertFromUtc($this->start_time, $timeZone, 'D M d, Y');
        $html .= ' ' . DateTimeHelper::convertFromUtc($this->start_time, $timeZone, 'h:ia');

        return $html;
    }

    public function getLocationDetailsHtml()
    {
        $details = $this->location_details;

        if (empty($details['type'])) {
            return 'n/a';
        }

        $locationType = $details['type'];

        if ($locationType == 'in_person_organizer') {
            $html = '<b>' . $details['title'] . '</b>';
            if ($description = Arr::get($details, 'description')) {
                $html .= wpautop($description);
            }
            return $html;
        }

        if ($locationType == 'google_meet') {
            $html = '<b> Google Meet </b>';
            if ($meetingLink = Arr::get($details, 'description')) {
                $html .= '<a target="_blank" href="' . esc_url($meetingLink) . '">' . esc_html('join now') . '</a>';
            }
            return $html;
        }

        if ($locationType == 'phone_guest') {
            return '<b>Phone Call: </b>' . $this->phone;
        } else if ($locationType == 'phone_organizer') {
            return '<b>Phone Call: </b>' . Arr::get($details, 'host_phone_number') . ' (Host phone number)';
        }

        if ($locationType == 'custom') {
            $html = '<b>' . Arr::get($details, 'title') . '</b>';
            $html .= wpautop(Arr::get($details, 'description'));

            return $html;
        }

        return '';
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

    public function getCancelReason()
    {
        return BookingActivity::where('booking_id', $this->id)
            ->where('type', 'cancel_reason')
            ->first();
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
            'meta_key'   => $key,
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
}
