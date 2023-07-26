<?php

namespace FluentCalendar\App\Models;

use FluentCalendar\App\Models\Model;

class User extends Model
{
    protected $table = 'users';

    protected $guarded = ['ID', 'user_pass'];

    protected $hidden = ['user_pass', 'user_activation_key'];

    protected $appends = ['full_name'];

    protected $primaryKey = 'ID';

    /**
     * @return \FluentCalendar\Framework\Database\Orm\Relations\HasMany
     */
    public function calendars()
    {
        return $this->hasMany(Calendar::class, 'user_id');
    }

    /**
     * @return \FluentCalendar\Framework\Database\Orm\Relations\BelongsToMany
     */
    public function booings()
    {
        return $this->belongsToMany(CalendarSlot::class, 'fcal_booking_hosts', 'user_id', 'booking_id')
            ->withPivot('status');
    }

    public function user() {
        return get_user_by('ID', $this->ID);
    }

    public function getFullNameAttribute() {
        $user = $this->user();
        $name =  trim($user->first_name . ' ' . $user->last_name);
        if(!$name) {
            $name = $user->display_name;
        }
        return $name;
    }

    public function staff() {
        return $this->hasOne(Staff::class, 'object_id');
    }

}
