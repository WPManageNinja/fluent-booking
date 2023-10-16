<?php

namespace FluentBooking\App\Hooks\Handlers\CleanupHandlers;

use FluentBooking\App\Models\Booking;
use FluentBooking\App\Models\Order;
use FluentBooking\App\Models\OrderItems;

class OrderCleaner
{
    public function register()
    {
        add_action('fluent_booking/before_delete_order', [$this, 'handleBeforeDelete'], 10, 2);
    }

    public function handleBeforeDelete($order, $booking)
    {
        if (empty($order)) {
            return;
        }

        OrderItems::query()->where('order_id', $order)
            ->when($booking, function ($query, $booking) {
                $query->where('booking_id', $booking->id);
            })->delete();

    }
}