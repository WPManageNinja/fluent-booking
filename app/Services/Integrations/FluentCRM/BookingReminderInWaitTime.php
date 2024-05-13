<?php

namespace FluentBooking\App\Services\Integrations\FluentCRM;

use FluentBooking\App\Models\Booking;
use FluentBooking\Framework\Support\Arr;
use FluentCrm\App\Models\FunnelSubscriber;

class BookingReminderInWaitTime
{
	public function __construct() {
		add_filter('fluentcrm_funnel_block_fields', [$this, 'addBookingWaitTimeSection'], 1000, 2);
		add_filter('fluent_crm/funnel_seq_delay_in_seconds', [$this, 'maybeFluentBookingReminder'], 999999, 4);
	}

    public function addBookingWaitTimeSection($blocks, $funnel) {

		if ($funnel->trigger_name != 'fluent_booking/after_booking_scheduled') {
			return $blocks;
		}

        if (!Arr::get($blocks, 'fluentcrm_wait_times')) {
            return $blocks;
        }

        $blocks['fluentcrm_wait_times']['fields']['is_fluent_booking_reminder'] = [
            'type'          => 'yes_no_check',
            'check_label'   => 'Use the wait time as Fluent Booking Reminder',
            'inline_help'   => '<b>Example: </b> If the wait time is set to 30 minutes, the next funnel will be initiated 30 minutes before the meeting starting time.',
            'dependency'    => [
                'depends_on' => 'wait_type',
                'value'      => 'unit_wait',
                'operator'   => '=',
            ]
        ];

		return $blocks;
	}

	public function maybeFluentBookingReminder($seconds, $settings, $sequence, $funnelSubId) {
		if (Arr::get($settings, 'is_fluent_booking_reminder', 'no') != 'yes') {
            return $seconds;
		}

        if ($funnelSubId) {
            $funnelSubscriber = FunnelSubscriber::find($funnelSubId);
            $bookingId = Arr::get($funnelSubscriber, 'source_ref_id');
            $booking = Booking::find($bookingId);
        } else {
            $booking = Booking::latest()->first();
        }

        if (!$booking) {
            return $seconds;
        }

        $reminderTime = $this->getReminderTime($settings);
        $bookingTime  = $this->getTimeDifference($booking->start_time);

        return $bookingTime - $reminderTime;
	}

	public function getTimeDifference($bookingStartTime) {
		$currentTime = strtotime("now");
		$bookingTime = strtotime($bookingStartTime);
		$difference  = $bookingTime - $currentTime;

		return $difference;
	}

	public function getReminderTime($settings) {
        $value = Arr::get($settings, 'wait_time_amount');
        $unit  = Arr::get($settings, 'wait_time_unit', 'minutes');

		$timestamp = (int) $value * 60;
		if ($unit == 'hours') {
			$timestamp = $timestamp * 60;
		} elseif ($unit == 'days') {
			$timestamp = $timestamp * 60 * 24;
		}

		return $timestamp;
	}
}
