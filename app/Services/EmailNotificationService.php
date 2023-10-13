<?php

namespace FluentBooking\App\Services;

use FluentBooking\App\App;
use FluentBooking\App\Models\Booking;
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
    public static function emailOnBooked(Booking $booking, $email, $emailTo)
    {
        $emailSubject = EditorShortCodeParser::parse($email['subject'], $booking);
        $emailBody = EditorShortCodeParser::parse($email['body'], $booking);

        $calendarEvent = $booking->calendar_event;
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

        $body = (string)App::make('view')->make('emails.template', [
            'email_body'   => $emailBody,
            'email_footer' => self::getGlobalEmailFooter(),
        ]);

        $emogrifier = new Emogrifier($body);
        $emogrifier->disableInvisibleNodeRemoval();
        $body = (string)$emogrifier->emogrify();

        return Mailer::send($to, $emailSubject, $body, $headers);
    }

    /**
     * @param \FluentBooking\App\Models\Booking $booking
     * @param \FluentBooking\App\Models\CalendarSlot $calendarEvent
     * @param $email
     * @param $time
     * @param $emailTo
     * @return bool|mixed
     */
    public static function reminderEmail(Booking $booking, $email, $emailTo)
    {
        $calendarEvent = $booking->calendar_event;
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
            'email_body'   => $html,
            'email_footer' => self::getGlobalEmailFooter()
        ]);

        $emogrifier = new Emogrifier($body);
        $emogrifier->disableInvisibleNodeRemoval();
        $body = (string)$emogrifier->emogrify();

        $result = Mailer::send($to, $subject, $body, $headers);

        do_action('fluent_booking/log_booking_note', [
            'title'       => 'Reminder Email Sent',
            'type'        => 'activity',
            'description' => sprintf(__('%s %s reminder email sent to %s.'), $emailTo),
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
    public static function bookingCancelledEmail(Booking $booking, $email, $emailTo)
    {
        $calendarEvent = $booking->calendar_event();

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
            'email_body'   => $html,
            'email_footer' => self::getGlobalEmailFooter()
        ]);

        $emogrifier = new Emogrifier($body);
        $emogrifier->disableInvisibleNodeRemoval();
        $body = (string)$emogrifier->emogrify();

        $result = Mailer::send($to, $subject, $body, $headers);

        do_action('fluent_booking/log_booking_note', [
            'title'       => 'Cancelled booking email sent to ' . $emailTo,
            'type'        => 'activity',
            'description' => sprintf(__('Cancellation email sent to %s'), $emailTo),
            'booking_id'  => $booking->id
        ]);

        return $result;
    }

    public static function getGlobalEmailFooter()
    {
        $globalSettings = Helper::getGlobalSettings();

        return Arr::get($globalSettings, 'emailing.email_footer', '');
    }

}
