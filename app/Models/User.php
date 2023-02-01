<?php

namespace FluentCalendar\App\Models;

use FluentCalendar\App\Models\Model;

class User extends Model
{
    protected $table = 'users';

    protected $guarded = ['ID', 'user_pass'];

    protected $hidden = ['user_pass', 'user_activation_key'];

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
        return $this->belongsToMany(CalendarSlot::class, 'fcal_booking_users', 'user_id', 'booking_id')
            ->withPivot('status');
    }

    public function user() {
        return get_user_by('ID', $this->ID);
    }

}
