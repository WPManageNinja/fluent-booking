<?php

namespace FluentBooking\App\Hooks\Handlers\CleanupHandlers;

use FluentBooking\App\Models\Booking;
use FluentBooking\App\Models\BookingActivity;
use FluentBooking\App\Models\BookingHost;
use FluentBooking\App\Models\BookingMeta;
use FluentBooking\App\Models\Order;
use FluentBooking\App\Models\OrderItems;

class BookingCleaner
{
    public function register()
    {
        add_action('fluent_booking/before_delete_booking', [$this, 'handleBeforeDelete'], 10, 1);
    }

    public function handleBeforeDelete($booking)
    {
        if (empty($booking)) {
            return;
        }

        BookingMeta::query()->where('booking_id', $booking->id)->delete();
        BookingHost::query()->where('booking_id', $booking->id)->delete();
        BookingActivity::query()->where('booking_id', $booking->id)->delete();

        $order = Order::query()
            ->where('parent_id', $booking->id)
            ->first();

        if ($order) {

            do_action('fluent_booking/before_delete_order', $order, $booking);
            $order->delete();
            do_action('fluent_booking/after_delete_order', $order, $booking);
        }

    }
}
