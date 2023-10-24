<?php

namespace FluentBooking\App\Hooks\Handlers;


use FluentBooking\App\Models\Booking;
use FluentBooking\App\Services\PermissionManager;

class DataExporter
{

    public function exportBookingHosts()
    {

        if(!PermissionManager::hasAllCalendarAccess()) {
            die('You do not have permission to export data');
        }

        $groupId = (int)$_REQUEST['group_id']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

        if (!$groupId) {
            die('Please provide Group ID');
        }

        $attendees = Booking::where('group_id', $groupId)->get();

        $attendeesData = [];
        foreach ($attendees as $attendee) {

            if ($attendee->payment_status) {
                $order = $attendee->payment_order;
                if ($order) {
                    $order->load(['items', 'transaction']);
                    $attendeesData['payment_order'] = [
                        'status'         => $order->status,
                        'payment_method' => $order->payment_method,
                        'currency'       => $order->currency,
                        'total_amount'   => $order->total_amount / 100,
                        'created_at'     => $order->created_at,
                        'items'          => $order->items,
                        'transaction'    => [
                            'id'               => $order->transaction->id,
                            'vendor_charge_id' => $order->transaction->vendor_charge_id,
                            'payment_method'   => $order->transaction->payment_method,
                            'status'           => $order->transaction->status,
                            'total'            => $order->transaction->total / 100,
                            'created_at'       => $order->transaction->created_at,
                        ]
                    ];
                }
            }


            $attendeesData['first_name'] = $attendee->first_name;
            $attendeesData['last_name'] = $attendee->last_name;
            $attendeesData['email'] = $attendee->email;
            $attendeesData['message'] = $attendee->message;
            $attendeesData['location_details'] = $attendee->location_details;
            $attendeesData['source'] = $attendee->source;
            $attendeesData['booking_type'] = $attendee->booking_type;
            $attendeesData['source_url'] = $attendee->source_url;
            $attendeesData['start_time'] = $attendee->start_time;
        }

        $attendeesData = apply_filters('fluent_booking/exporting_booking_data', $attendeesData, $attendees);

        header('Content-disposition: attachment; filename=Booking-Event-Guests-' . $groupId . '.json');
        header('Content-type: application/json');
        echo wp_json_encode($attendeesData);
        exit();
    }

}
