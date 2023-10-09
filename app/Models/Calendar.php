<?php

namespace FluentBooking\App\Models;

use FluentBooking\App\Models\Model;
use FluentBooking\App\Services\LandingPage\LandingPageHelper;
use FluentBooking\Framework\Support\Arr;

class Calendar extends Model
{
    protected $table = 'fcal_calendars';

    protected $guarded = ['id'];

    protected $fillable = [
        'hash',
        'user_id',
        'account_id',
        'title',
        'slug',
        'media_id',
        'description',
        'settings',
        'status',
        'type',
        'event_type',
        'account_type',
        'author_timezone',
        'max_book_per_slot',
        'visibility'
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->user_id)) {
                $model->user_id = get_current_user_id();
            }
            $model->hash = md5(wp_generate_uuid4() . time());
        });
    }

    public function setSettingsAttribute($settings)
    {
        $this->attributes['settings'] = \maybe_serialize($settings);
    }

    public function getSettingsAttribute($settings)
    {
        return \maybe_unserialize($settings);
    }

    public function slots()
    {
        return $this->hasMany(CalendarSlot::class, 'calendar_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'calendar_id');
    }

    public function getAuthorProfile($public = true)
    {
        $user = get_user_by('id', $this->user_id);
        if (!$user) {
            return [
                'avatar' => '',
                'name'   => 'Unknown'
            ];
        }

        $name = trim($user->first_name . ' ' . $user->last_name);

        if (!$name) {
            $name = $user->display_name;
        }

        return [
            'name'        => $name,
            'author_slug' => $user->user_nicename,
            'first_name'  => $user->first_name,
            'last_name'   => $user->last_name,
            'avatar'      => apply_filters('fluent_booking/author_photo', get_avatar_url($user->ID), $user)
        ];
    }

    public function getMeta($key, $default = null)
    {
        $meta = Meta::where('object_type', 'Calendar')
            ->where('object_id', $this->id)
            ->where('key', $key)
            ->first();

        if (!$meta) {
            return $default;
        }

        return $meta->value;
    }

    public function updateMeta($key, $value)
    {
        $exist = Meta::where('object_type', 'Calendar')
            ->where('object_id', $this->id)
            ->where('key', $key)
            ->first();

        if ($exist) {
            $exist->value = $value;
            $exist->save();
        } else {
            $exist = Meta::create([
                'object_type' => 'Calendar',
                'object_id'   => $this->id,
                'key'         => $key,
                'value'       => $value
            ]);
        }

        return $exist;
    }

    public function getLandingPageUrl($isForce = false)
    {
        $settings = LandingPageHelper::getSettings($this);
        if (Arr::get($settings, 'enabled') != 'yes' && !$isForce) {
            return '';
        }

        $author = $this->getAuthorProfile();

        if (empty($author['author_slug'])) {
            return '';
        }

        if (defined('FLUENT_BOOKING_LANDING_SLUG')) {
            return LandingPageHelper::getLandingBaseUrl() . $author['author_slug'];
        }

        return LandingPageHelper::getLandingBaseUrl() . '&host=' . $author['author_slug'];
    }
}
