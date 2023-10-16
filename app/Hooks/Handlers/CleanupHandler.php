<?php
namespace FluentBooking\App\Hooks\Handlers;


use FluentBooking\App\Models\Booking;
use FluentBooking\App\Models\Calendar;
use FluentBooking\App\Models\CalendarSlot;

class CleanupHandler
{
    public function register()
    {
        add_action( 'delete_user', [$this, 'handleDeleteUser'], 10, 2);

        add_action('fluent_booking/before_delete_calendar', [$this, 'handleDeleteCalendar'], 10, 1);

    }

    public function handleDeleteUser($userId, $reassignId)
    {
        if($reassignId) {
            CalendarSlot::where('user_id', $reassignId)->update(['user_id' => $userId]);
            Calendar::where('user_id', $reassignId)->update(['user_id' => $userId]);
            return;
        }

        $calendars = Calendar::where('user_id', $userId)->get();

        foreach ($calendars as $calendar) {
            $this->removeCalendarAssets($calendar->id);
            $calendar->delete();
        }
        return;
    }

    public function handleDeleteCalendar($calendar)
    {
        //
    }

    protected function removeCalendarAssets($calendarId)
    {

        // Remove the Booking Activities

        // Remove the Booking Meta

        // Remove Associate Order, Transactions, OrderItems

        // delete bookings
        Booking::where('calendar_id', $calendarId)
            ->delete();

        // Remove the Calendar meta

        // Remove All CalendarSlots

        // Remove All Calendar Slots Meta

    }
}
