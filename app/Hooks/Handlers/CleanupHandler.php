<?php
namespace FluentCalendar\App\Hooks\Handlers;


use FluentCalendar\App\Models\Booking;
use FluentCalendar\App\Models\Calendar;
use FluentCalendar\App\Models\CalendarSlot;

class CleanupHandler
{
    public function register()
    {
        add_action( 'delete_user', [$this, 'handleDeleteUser'], 10, 2);
    }

    public function handleDeleteUser($userId, $reassignId)
    {
        if($reassignId) {
            CalendarSlot::where('user_id', $reassignId)->update(['user_id' => $userId]);
            Calendar::where('user_id', $reassignId)->update(['user_id' => $userId]);
            return;
        }

        $slots = CalendarSlot::where('user_id', $userId)->get();

        if($slots->isEmpty()) {
            Calendar::where('user_id', $userId)->delete();
            return;
        }

        foreach ($slots as $slot) {
            Booking::where('slot_id', $slot->id)
                ->where('calendar_id', $slot->calendar_id)
                ->delete();
            $slot->delete();
        }

        Calendar::where('user_id', $userId)->delete();
    }
}
