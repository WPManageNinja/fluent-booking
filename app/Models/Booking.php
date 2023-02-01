<?php

namespace FluentCalendar\App\Models;

use FluentCalendar\App\Models\Model;
use FluentCalendar\App\Services\DateTimeHelper;
use FluentCalendar\Framework\Database\Orm\DateTime;

class Booking extends Model
{
    protected $table = 'fcal_bookings';

    protected $guarded = ['id'];

    protected $fillable = [
        'calendar_id',
        'slot_id',
        'parent_id',
        'person_user_id',
        'person_contact_id',
        'hash',
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
        'booking_instructions',
        'reminder_stage',
        'last_reminder_sent',
        'next_reminder',
        'status',
        'source',
        'source_id',
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

            if (defined('FLUENTCRM') && !empty($model->email)) {
                $contact = FluentCrmApi('contacts')->getContact($model->email);
                if ($contact) {
                    $model->person_contact_id = $contact->id;
                }
            }

            $model->hash = md5(wp_generate_uuid4() . time());
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

    public function users()
    {
        $class = __NAMESPACE__ . '\User';

        return $this->belongsToMany(
            $class,
            'fcal_booking_users',
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
        $html .= ' - '.DateTimeHelper::convertFromUtc($this->end_time, $timeZone, 'h:ia').', ';
        $html .= DateTimeHelper::convertFromUtc($this->start_time, $timeZone, 'l, F d, Y');
        return $html;
    }

    public function getOngoingStatus()
    {

        if($this->status != 'scheduled') {
            return '';
        }

        $currentTime = time();
        $startTime = strtotime($this->start_time);
        $endTime = strtotime($this->end_time);

        if($currentTime > $startTime && $currentTime < $endTime) {
            return 'happening_now';
        } elseif(($startTime - $currentTime) < 1800 && ($startTime - $currentTime) > 0) {
            return 'starting_soon';
        } else if (($endTime - $currentTime ) > -3600 && ($endTime - $currentTime) < 0) {
            return 'recently_happened';
        }

        return '';
    }

}
