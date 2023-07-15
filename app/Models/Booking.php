<?php

namespace FluentCalendar\App\Models;

use FluentCalendar\App\Models\Model;
use FluentCalendar\App\Services\DateTimeHelper;
use FluentCalendar\Framework\Support\Arr;

class Booking extends Model
{
    protected $table = 'fcal_bookings';

    protected $guarded = ['id'];

    protected $fillable = [
        'calendar_id',
        'slot_id',
        'parent_id',
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
        static::creating(function ($model) {
            if (!isset($model->person_user_id) && $userId = get_current_user_id()) {
                $model->person_user_id = $userId;
            }

            if (defined('FLUENTCRM') && !empty($model->email) && apply_filters('fluent_calender/auto_booking_fluent_crm_sync', true)) {
                $contact = FluentCrmApi('contacts')->getContact($model->email);
                if ($contact) {
                    $model->person_contact_id = $contact->id;
                }
            }

            $model->hash = md5(wp_generate_uuid4() . time());
        });

        static::deleting(function ($model) { // before delete() method call this
            $model->hosts()->delete();
        });
    }

    public function calendar()
    {
        return $this->belongsTo(Calendar::class, 'calendar_id');
    }

    public function slot()
    {
        return $this->belongsTo(CalendarSlot::class, 'slot_id');
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

        if (empty($details['location_type'])) {
            return 'n/a';
        }

        $locationType = $details['location_type'];

        if ($locationType == 'in_person') {
            $html = '<b>' . $details['location_heading'] . '</b>';
            if ($description = Arr::get($details, 'location_settings.description')) {
                $html .= wpautop($description);
            }
            return $html;
        }

        if ($locationType == 'phone') {
            $html = '<b>Phone Call: </b>';
            if (Arr::get($details, 'location_settings.call_type') == 'outbound') {
                $html .= $this->phone;
            } else {
                $html .= Arr::get($details, 'location_settings.host_phone_number') . ' (Host phone number)';
            }
            return $html;
        }

        if ($locationType == 'custom') {
            $html = '<b>' . Arr::get($details, 'location_heading') . '</b>';
            $html .= wpautop(Arr::get($details, 'location_settings.description'));

            return $html;
        }

        return '';
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

}
