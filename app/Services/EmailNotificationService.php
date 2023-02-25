<?php
namespace FluentCalendar\App\Services;

use FluentCalendar\App\App;
use FluentCalendar\App\Models\Booking;
use FluentCalendar\App\Models\CalendarSlot;
use FluentCalendar\App\Services\Libs\Emogrifier\Emogrifier;

class EmailNotificationService
{
    /**
     * @param $booking Booking
     * @param $slot CalendarSlot
     * @return void
     */
    public static function emailToGuestOnBooked($booking, $slot = null)
    {
        if (!$slot) {
            $slot = $booking->slot;
        }

        $author = $slot->getAuthorProfile(false);

        $data = [
            'event_name'     => sprintf('%1s meeting with %2s', $slot->title, $author['name']),
            'event_date'     => $booking->getFullBookingDateTimeText($booking->person_time_zone) . ' (' . $booking->person_time_zone . ')',
            'event_location' => $booking->getLocationDetailsHtml(),
            'author_email'   => $author['email'],
            'booking'        => $booking,
        ];

        $data = apply_filters('fluent_calendar/booking_confirmation_email_data', $data, $booking, $slot);

        $html = (string) App::make('view')->make('emails.confirmation_to_user', $data);
        $subject = sprintf(__('Invitation: %1s and %2s @ %3s (%4s)', 'fluent-calendar'), $booking->first_name, $author['name'], $booking->getShortBookingDateTime($booking->person_time_zone), $booking->email);

        $body = (string) App::make('view')->make('emails.template', [
            'email_body' => $html,
            'wrapper_heading' => 'Booking Confirmation'
        ]);

        $emogrifier = new Emogrifier($body);
        $emogrifier->disableInvisibleNodeRemoval();
        $body = (string) $emogrifier->emogrify();

        $to = $booking->email;

        if($booking->first_name && $booking->last_name) {
            $to = sprintf('%1s %2s <%3s>', $booking->first_name, $booking->last_name, $booking->email);
        } else if($booking->first_name) {
            $to = sprintf('%1s <%2s>', $booking->first_name, $booking->email);
        }

        // add reply to header
        $replyTo = $author['email'];
        if($author['name']) {
            $replyTo = sprintf('%1s <%2s>', $author['name'], $author['email']);
        }

        $headers = [
            'Reply-To: ' . $replyTo
        ];

        $result = Mailer::send($to, $subject, $body, $headers);

        do_action('fluent_calendar/booking_confirmation_email_sent_to_guest', $booking, $slot, [
            'subject' => $subject,
            'body' => $body,
            'to' => $to
        ]);

        return $result;
    }

    public static function emailToHostOnBooked($booking, $slot = null)
    {
        if(!$slot) {
            $slot = $booking->slot;
        }

        $author = $slot->getAuthorProfile(false);

        $calendar = $slot->calendar;

        $data = [
            'event_name'     => sprintf('%1s meeting with %2s', $slot->title, trim($booking->first_name.' '.$booking->last_name)),
            'event_date'     => $booking->getFullBookingDateTimeText($calendar->author_timezone) . ' (' . $booking->author_timezone . ')',
            'event_location' => $booking->getLocationDetailsHtml(),
            'author_email'   => $author['email'],
            'booking'        => $booking,
        ];

        $data = apply_filters('fluent_calendar/booking_confirmation_email_data', $data, $booking, $slot);

        $html = (string) App::make('view')->make('emails.confirmation_to_admin', $data);
        $subject = sprintf(__('New Booking: %1s @ %2s (%3s)', 'fluent-calendar'), trim($booking->first_name.' '.$booking->last_name), $booking->getShortBookingDateTime($booking->person_time_zone), $booking->email);

        $body = (string) App::make('view')->make('emails.template', [
            'email_body' => $html,
            'wrapper_heading' => 'New Booking Confirmed'
        ]);

        $emogrifier = new Emogrifier($body);
        $emogrifier->disableInvisibleNodeRemoval();
        $body = (string) $emogrifier->emogrify();

        // add reply to header
        $replyTo = $booking->email;
        if($booking->first_name && $booking->last_name) {
            $replyTo = sprintf('%1s %2s <%3s>', $booking->first_name, $booking->last_name, $booking->email);
        } else if($booking->first_name) {
            $replyTo = sprintf('%1s <%2s>', $booking->first_name, $booking->email);
        }

        $headers = [
            'Reply-To: ' . $replyTo
        ];

        $to = $author['email'];

        if($author['name']) {
            $to = sprintf('%1s <%2s>', $author['name'], $author['email']);
        }

        $result = Mailer::send($to, $subject, $body, $headers);

        do_action('fluent_calendar/booking_confirmation_email_sent_to_host', $booking, $slot, [
            'subject' => $subject,
            'body' => $body,
            'to' => $to
        ]);

        return $result;
    }

    public static function emailToGuestOnOneHourReminder($booking, $slot = null)
    {
        if (!$slot) {
            $slot = $booking->slot;
        }

        

        return true;
    }

    public static function emailToHostOnOneHourReminder($booking, $slot = null)
    {
        if (!$slot) {
            $slot = $booking->slot;
        }

        return true;
    }

    public static function emailToGuest15MinutesReminder($booking, $slot = null)
    {
        if (!$slot) {
            $slot = $booking->slot;
        }

        return true;
    }

    public static function emailToHostOn15MinutesReminder($booking, $slot = null)
    {
        if (!$slot) {
            $slot = $booking->slot;
        }

        return true;
    }

}
