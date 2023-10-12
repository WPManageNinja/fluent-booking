<?php

namespace FluentBooking\App\Services;

use FluentBooking\App\App;
use FluentBooking\App\Models\Booking;
use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\App\Services\EditorShortCodeParser;
use FluentBooking\App\Services\Libs\Emogrifier\Emogrifier;
use FluentBooking\Framework\Support\Arr;

class EmailNotificationService
{

    /**
     * @param \FluentBooking\App\Models\Booking $booking
     * @param \FluentBooking\App\Models\CalendarSlot $calendarEvent
     * @param $email
     * @param $emailTo
     * @return bool|mixed
     */
    public static function emailOnBooked(Booking $booking, CalendarSlot $calendarEvent, $email, $emailTo)
    {
        $author = $calendarEvent->getAuthorProfile(false);

        // Host Address
        $hostAddress = $author['email'];
        if ($author['name']) {
            $hostAddress = sprintf('%1s <%2s>', $author['name'], $author['email']);
        }

        // Guest Address
        $guestAddress = $booking->email;
        if ($booking->first_name && $booking->last_name) {
            $guestAddress = sprintf('%1s %2s <%3s>', $booking->first_name, $booking->last_name, $booking->email);
        } else if ($booking->first_name) {
            $guestAddress = sprintf('%1s <%2s>', $booking->first_name, $booking->email);
        }

        // Assign-To & Reply-To
        if ('host' == $emailTo) {
            $to = $hostAddress;
            $replyTo = $guestAddress;
        } else {
            $to = $guestAddress;
            $replyTo = $hostAddress;
        }

        $headers = [
            'Reply-To: ' . $replyTo
        ];

        $subject = EditorShortCodeParser::parse($email['subject'], $booking);
        $html = EditorShortCodeParser::parse($email['body'], $booking);

        $body = (string)App::make('view')->make('emails.template', [
            'email_body'      => $html,
            'email_footer' => '',
        ]);

        $emogrifier = new Emogrifier($body);
        $emogrifier->disableInvisibleNodeRemoval();
        $body = (string)$emogrifier->emogrify();

        $result = Mailer::send($to, $subject, $body, $headers);

        do_action('fluent_booking/booking_confirmation_email_sent_to_' . $emailTo, $booking, $calendarEvent, [
            'subject' => $subject,
            'body'    => $body,
            'to'      => $to
        ]);

        return $result;
    }

    /**
     * @param \FluentBooking\App\Models\Booking $booking
     * @param \FluentBooking\App\Models\CalendarSlot $calendarEvent
     * @param $email
     * @param $time
     * @param $emailTo
     * @return bool|mixed
     */
    public static function reminderEmail(Booking $booking, CalendarSlot $calendarEvent, $email, $time, $emailTo)
    {
        $author = $calendarEvent->getAuthorProfile(false);

        // Host Address
        $hostAddress = $author['email'];
        if ($author['name']) {
            $hostAddress = sprintf('%1s <%2s>', $author['name'], $author['email']);
        }

        // Guest Address
        $guestAddress = $booking->email;
        if ($booking->first_name && $booking->last_name) {
            $guestAddress = sprintf('%1s %2s <%3s>', $booking->first_name, $booking->last_name, $booking->email);
        } else if ($booking->first_name) {
            $guestAddress = sprintf('%1s <%2s>', $booking->first_name, $booking->email);
        }

        if ('host' == $emailTo) {
            $to = $hostAddress;
            $replyTo = $guestAddress;
        } else {
            $to = $guestAddress;
            $replyTo = $hostAddress;
        }

        $headers = [
            'Reply-To: ' . $replyTo
        ];

        $subject = EditorShortCodeParser::parse($email['subject'], $booking, $time);
        $html = EditorShortCodeParser::parse($email['body'], $booking, $time);

        $body = (string)App::make('view')->make('emails.template', [
            'email_body'      => $html,
            'email_footer' => ''
        ]);

        $emogrifier = new Emogrifier($body);
        $emogrifier->disableInvisibleNodeRemoval();
        $body = (string)$emogrifier->emogrify();

        $result = Mailer::send($to, $subject, $body, $headers);

        do_action('fluent_booking/booking_reminder_email_sent_to_' . $emailTo, $booking, $calendarEvent, [
            'subject' => $subject,
            'body'    => $body,
            'time'    => $time,
            'to'      => $to
        ]);

        do_action('fluent_booking/log_booking_note', [
            'title'       => $time['value'] . ' ' . $time['unit'] . ' reminder to ' . $emailTo,
            'type'        => 'activity',
            'description' => sprintf(__('%s %s reminder email sent to guest. Email Subject: %s'),$time['value'], $time['unit'], $subject),
            'booking_id'  => $booking->id
        ]);

        return $result;
    }

    /**
     * @param \FluentBooking\App\Models\Booking $booking
     * @param \FluentBooking\App\Models\CalendarSlot $calendarEvent
     * @param $email
     * @param $emailTo
     * @return bool|mixed
     */
    public static function bookingCancelledEmail(Booking $booking, CalendarSlot $calendarEvent, $email, $emailTo)
    {
        $author = $calendarEvent->getAuthorProfile(false);

        // Host Address
        $hostAddress = $author['email'];
        if ($author['name']) {
            $hostAddress = sprintf('%1s <%2s>', $author['name'], $author['email']);
        }

        // Guest Address
        $guestAddress = $booking->email;
        if ($booking->first_name && $booking->last_name) {
            $guestAddress = sprintf('%1s %2s <%3s>', $booking->first_name, $booking->last_name, $booking->email);
        } else if ($booking->first_name) {
            $guestAddress = sprintf('%1s <%2s>', $booking->first_name, $booking->email);
        }

        if ('host' == $emailTo) {
            $to = $hostAddress;
            $replyTo = $guestAddress;
        } else {
            $to = $guestAddress;
            $replyTo = $hostAddress;
        }

        $headers = [
            'Reply-To: ' . $replyTo
        ];

        $subject = EditorShortCodeParser::parse($email['subject'], $booking);
        $html = EditorShortCodeParser::parse($email['body'], $booking);

        $body = (string)App::make('view')->make('emails.template', [
            'email_body'      => $html,
            'email_footer' => ''
        ]);

        $emogrifier = new Emogrifier($body);
        $emogrifier->disableInvisibleNodeRemoval();
        $body = (string)$emogrifier->emogrify();

        $result = Mailer::send($to, $subject, $body, $headers);

        do_action('fluent_booking/booking_cancelled_email_sent_to_' . $emailTo, $booking, $calendarEvent, [
            'subject' => $subject,
            'body'    => $body,
            'to'      => $to
        ]);

        do_action('fluent_booking/log_booking_note', [
            'title'       => 'Cancelled booking email sent to ' . $emailTo,
            'type'        => 'activity',
            'description' => sprintf(__('Cancellation email sent to %s. Email Subject: %s'), $emailTo, $subject),
            'booking_id'  => $booking->id
        ]);

        return $result;
    }

}
