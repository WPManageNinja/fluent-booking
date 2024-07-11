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
     * @param $email
     * @param $emailTo
     * @param $actionType
     * @return bool|mixed
     */
    public static function emailOnBooked(Booking $booking, $email, $emailTo, $actionType = 'scheduled')
    {
        $emailSubject = EditorShortCodeParser::parse($email['subject'], $booking);
        $emailBody = EditorShortCodeParser::parse($email['body'], $booking);

        $globalSettings = Helper::getGlobalSettings();
        $useHostName = Arr::get($globalSettings, 'emailing.use_host_name', 'yes');
        $useHostEmailOnReply = Arr::get($globalSettings, 'emailing.use_host_email_on_reply', 'yes');
        $attachIcsFile = Arr::get($globalSettings, 'emailing.attach_ics_on_confirmation', 'no');
        $settingsFromName = Arr::get($globalSettings, 'emailing.from_name', '');
        $settingsFromEmail = Arr::get($globalSettings, 'emailing.from_email', '');
        $settingsReplyToName = Arr::get($globalSettings, 'emailing.reply_to_name', '');
        $settingsReplyToEmail = Arr::get($globalSettings, 'emailing.reply_to_email', '');

        $calendarEvent = $booking->calendar_event;

        $attachments = [];
        if ($attachIcsFile == 'yes' && $actionType == 'scheduled') {
            $attachments = self::prepareAttachments($booking);
        }

        $guestAddress = self::getGuestAddress($booking);

        $authors = $booking->getHostsDetails(false);

        if ($emailTo == 'guest') {
            $authors = [$authors[0]];
        }

        foreach ($authors as $author)
        {
            $hostName = '';
            $hostAddress = $author['email'];
            if ($author['name']) {
                $hostName = $author['name'];
                $hostAddress = sprintf('%1s <%2s>', $author['name'], $author['email']);
            }
    
            // Assign-To & Reply-To
            if ('host' == $emailTo) {
                $to = $hostAddress;
                $replyTo = sprintf('%1s <%2s>', $settingsReplyToName, $settingsReplyToEmail ?: $settingsFromEmail);
                $from = sprintf('%1s <%2s>', $settingsFromName, $settingsFromEmail);
            } else {
                $to = $guestAddress;
                $replyName = $useHostName == 'no' ? $settingsReplyToName : $hostName;
                $fromName = $useHostName == 'no' ? $settingsFromName : $hostName;
    
                $replayToEmail = $useHostEmailOnReply == 'no' ? $settingsReplyToEmail : $author['email'];
                $replyFromEmail = $useHostEmailOnReply == 'no' ? $settingsFromEmail : $replayToEmail;
    
                $replyTo = sprintf('%1s <%2s>', $replyName, $replayToEmail);
                $from = sprintf('%1s <%2s>', $fromName, $replyFromEmail);
            }
    
            $headers = self::prepareEmailHeaders($replyTo, $from, $email);
    
            $body = (string)App::make('view')->make('emails.template', [
                'email_body'   => $emailBody,
                'email_footer' => self::getGlobalEmailFooter(),
            ]);
    
            $emogrifier = new Emogrifier($body);
            $emogrifier->disableInvisibleNodeRemoval();
            $body = (string)$emogrifier->emogrify();
    
            Mailer::send($to, $emailSubject, $body, $headers, $attachments);
        }

        if ($attachments) {
            wp_delete_file($attachments[0]);
        }
    }

    /**
     * @param \FluentBooking\App\Models\Booking $booking
     * @param $email
     * @param $time
     * @param $emailTo
     * @return bool|mixed
     */
    public static function reminderEmail(Booking $booking, $email, $emailTo)
    {
        $globalSettings = Helper::getGlobalSettings();
        $useHostName = Arr::get($globalSettings, 'emailing.use_host_name', 'yes');
        $useHostEmailOnReply = Arr::get($globalSettings, 'emailing.use_host_email_on_reply', 'yes');
        $settingsFromName = Arr::get($globalSettings, 'emailing.from_name', '');
        $settingsFromEmail = Arr::get($globalSettings, 'emailing.from_email', '');
        $settingsReplyToName = Arr::get($globalSettings, 'emailing.reply_to_name', '');
        $settingsReplyToEmail = Arr::get($globalSettings, 'emailing.reply_to_email', '');

        $calendarEvent = $booking->calendar_event;

        $guestAddress = self::getGuestAddress($booking);

        $authors = $booking->getHostsDetails(false);

        if ($emailTo == 'guest') {
            $authors = [$authors[0]];
        }

        foreach ($authors as $author) {
            $hostAddress = $author['email'];
            $hostName = '';
            if ($author['name']) {
                $hostName = $author['name'];
                $hostAddress = sprintf('%1s <%2s>', $author['name'], $author['email']);
            }
    
            $from = '';
            if ('host' == $emailTo) {
                $to = $hostAddress;
                $replyTo = sprintf('%1s <%2s>', $settingsReplyToName, $settingsReplyToEmail ?: $settingsFromEmail);
                $from = sprintf('%1s <%2s>', $settingsFromName, $settingsFromEmail);
            } else {
                $to = $guestAddress;
                $replyName = $useHostName == 'no' ? $settingsReplyToName : $hostName;
                $formName = $useHostName == 'no' ? $settingsFromName : $hostName;
    
                $replayToEmail = $useHostEmailOnReply == 'no' ? $settingsReplyToEmail : $author['email'];
                $replyFromEmail = $useHostEmailOnReply == 'no' ? $settingsFromEmail : $replayToEmail;
    
                $replyTo = sprintf('%1s <%2s>', $replyName, $replayToEmail);
                $from = sprintf('%1s <%2s>', $formName, $replyFromEmail);
            }
    
            $headers = self::prepareEmailHeaders($replyTo, $from, $email);
    
            $subject = EditorShortCodeParser::parse($email['subject'], $booking);
            $html = EditorShortCodeParser::parse($email['body'], $booking);
    
            $body = (string)App::make('view')->make('emails.template', [
                'email_body'   => $html,
                'email_footer' => self::getGlobalEmailFooter()
            ]);
    
            $emogrifier = new Emogrifier($body);
            $emogrifier->disableInvisibleNodeRemoval();
            $body = (string)$emogrifier->emogrify();
    
            Mailer::send($to, $subject, $body, $headers);
    
            do_action('fluent_booking/log_booking_note', [
                'title'       => __('Reminder Email Sent', 'fluent-booking'),
                'type'        => 'activity',
                /* translators: Email address where the reminder email was sent */
                'description' => sprintf(__('Reminder email sent to %s.', 'fluent-booking'), $emailTo),
                'booking_id'  => $booking->id
            ]);
        }
    }

    /**
     * @param \FluentBooking\App\Models\Booking $booking
     * @param $email
     * @param $emailTo
     * @param $actionType
     * @return bool|mixed
     */
    public static function bookingCancelOrRejectEmail(Booking $booking, $email, $emailTo, $actionType = 'cancel')
    {
        $globalSettings = Helper::getGlobalSettings();
        $useHostName = Arr::get($globalSettings, 'emailing.use_host_name', 'yes');
        $useHostEmailOnReply = Arr::get($globalSettings, 'emailing.use_host_email_on_reply', 'yes');
        $settingsFromName = Arr::get($globalSettings, 'emailing.from_name', '');
        $settingsFromEmail = Arr::get($globalSettings, 'emailing.from_email', '');
        $settingsReplyToName = Arr::get($globalSettings, 'emailing.reply_to_name', '');
        $settingsReplyToEmail = Arr::get($globalSettings, 'emailing.reply_to_email', '');
        
        $calendarEvent = $booking->calendar_event;

        $guestAddress = self::getGuestAddress($booking);

        $authors = $booking->getHostsDetails(false);

        if ($emailTo == 'guest') {
            $authors = [$authors[0]];
        }

        foreach ($authors as $author) {
            $hostAddress = $author['email'];
            $hostName = '';
            if ($author['name']) {
                $hostName = $author['name'];
                $hostAddress = sprintf('%1s <%2s>', $author['name'], $author['email']);
            }
    
            if ('host' == $emailTo) {
                $to = $hostAddress;
                $replyTo = sprintf('%1s <%2s>', $settingsReplyToName, $settingsReplyToEmail ?: $settingsFromEmail);
                $from = sprintf('%1s <%2s>', $settingsFromName, $settingsFromEmail);
            } else {
                $to = $guestAddress;
                $replyName = $useHostName == 'no' ? $settingsReplyToName : $hostName;
                $formName = $useHostName == 'no' ? $settingsFromName : $hostName;
    
                $replayToEmail = $useHostEmailOnReply == 'no' ? $settingsReplyToEmail : $author['email'];
                $replyFromEmail = $useHostEmailOnReply == 'no' ? $settingsFromEmail : $replayToEmail;
    
                $replyTo = sprintf('%1s <%2s>', $replyName, $replayToEmail);
                $from = sprintf('%1s <%2s>', $formName, $replyFromEmail);
            }
    
            $headers = self::prepareEmailHeaders($replyTo, $from, $email);
    
            $subject = EditorShortCodeParser::parse($email['subject'], $booking);
            $html = EditorShortCodeParser::parse($email['body'], $booking);
    
            $body = (string)App::make('view')->make('emails.template', [
                'email_body'   => $html,
                'email_footer' => self::getGlobalEmailFooter()
            ]);
    
            $emogrifier = new Emogrifier($body);
            $emogrifier->disableInvisibleNodeRemoval();
            $body = (string)$emogrifier->emogrify();
    
            Mailer::send($to, $subject, $body, $headers);
    
            $actionType = $actionType == 'reject' ? __('Rejection', 'fluent-booking') : __('Cancellation', 'fluent-booking');
    
            do_action('fluent_booking/log_booking_note', [
                'title'       => $actionType . __(' booking email sent to ', 'fluent-booking') . $emailTo,
                'type'        => 'activity',
                'description' => $actionType . __(' email sent to ', 'fluent-booking') . $emailTo,
                'booking_id'  => $booking->id
            ]);
        }
    }

    /**
     * @param \FluentBooking\App\Models\Booking $booking
     * @param $email
     * @param $emailTo
     * @return bool|mixed
     */
    public static function bookingRescheduledEmail(Booking $booking, $email, $emailTo)
    {
        $globalSettings = Helper::getGlobalSettings();
        $useHostName = Arr::get($globalSettings, 'emailing.use_host_name', 'yes');
        $useHostEmailOnReply = Arr::get($globalSettings, 'emailing.use_host_email_on_reply', 'yes');
        $attachIcsFile = Arr::get($globalSettings, 'emailing.attach_ics_on_confirmation', 'no');
        $settingsFromName = Arr::get($globalSettings, 'emailing.from_name', '');
        $settingsFromEmail = Arr::get($globalSettings, 'emailing.from_email', '');
        $settingsReplyToName = Arr::get($globalSettings, 'emailing.reply_to_name', '');
        $settingsReplyToEmail = Arr::get($globalSettings, 'emailing.reply_to_email', '');
        
        $calendarEvent = $booking->calendar_event;

        $attachments = [];
        if ($attachIcsFile == 'yes') {
            $attachments = self::prepareAttachments($booking);
        }
        
        $guestAddress = self::getGuestAddress($booking);

        $authors = $booking->getHostsDetails(false);

        if ($emailTo == 'guest') {
            $authors = [$authors[0]];
        }

        foreach ($authors as $author) {
            $hostAddress = $author['email'];
            $hostName = '';
            if ($author['name']) {
                $hostName = $author['name'];
                $hostAddress = sprintf('%1s <%2s>', $author['name'], $author['email']);
            }
    
            if ('host' == $emailTo) {
                $to = $hostAddress;
                $replyTo = sprintf('%1s <%2s>', $settingsReplyToName, $settingsReplyToEmail ?: $settingsFromEmail);
                $from = sprintf('%1s <%2s>', $settingsFromName, $settingsFromEmail);
            } else {
                $to = $guestAddress;
                $replyName = $useHostName == 'no' ? $settingsReplyToName : $hostName;
                $formName = $useHostName == 'no' ? $settingsFromName : $hostName;
    
                $replayToEmail = $useHostEmailOnReply == 'no' ? $settingsReplyToEmail : $author['email'];
                $replyFromEmail = $useHostEmailOnReply == 'no' ? $settingsFromEmail : $replayToEmail;
    
                $replyTo = sprintf('%1s <%2s>', $replyName, $replayToEmail);
                $from = sprintf('%1s <%2s>', $formName, $replyFromEmail);
            }
    
            $headers = self::prepareEmailHeaders($replyTo, $from, $email);
    
            $subject = EditorShortCodeParser::parse($email['subject'], $booking);
            $html = EditorShortCodeParser::parse($email['body'], $booking);
    
            $body = (string)App::make('view')->make('emails.template', [
                'email_body'   => $html,
                'email_footer' => self::getGlobalEmailFooter()
            ]);
    
            $emogrifier = new Emogrifier($body);
            $emogrifier->disableInvisibleNodeRemoval();
            $body = (string)$emogrifier->emogrify();
    
            Mailer::send($to, $subject, $body, $headers, $attachments);
    
            do_action('fluent_booking/log_booking_note', [
                'title'       => __('Rescheduled booking email sent to', 'fluent-booking') . ' ' . $emailTo,
                'type'        => 'activity',
                /* translators: Email address where the rescheduling email was sent */
                'description' => sprintf(__('Rescheduling email sent to %s', 'fluent-booking'), $emailTo),
                'booking_id'  => $booking->id
            ]);
        }

        if ($attachments) {
            wp_delete_file($attachments[0]);
        }
    }

    private static function getGuestAddress($booking)
    {
        $guestAddress = $booking->email;
        if ($booking->first_name && $booking->last_name) {
            $guestAddress = sprintf('%1s %2s <%3s>', $booking->first_name, $booking->last_name, $booking->email);
        } else if ($booking->first_name) {
            $guestAddress = sprintf('%1s <%2s>', $booking->first_name, $booking->email);
        }

        if ($additionalGuests = $booking->getAdditionalGuests()) {
            $guestAddress .= ', ' . implode(', ', $additionalGuests);
        }
        return $guestAddress;
    }

    private static function prepareEmailHeaders($replyTo, $from, $email)
    {
        $headers = [
            'Reply-To: ' . $replyTo
        ];

        if ($from) {
            $headers[] = 'From: ' . $from;
        }

        if (isset($email['recipients'])) {
            $headers[] = 'bcc: ' . implode(', ', $email['recipients']);
        }

        return $headers;
    }

    private static function prepareAttachments($booking)
    {
        $icsContent = BookingService::generateBookingICS($booking);
    
        $filePath = wp_tempnam(null, 'event') . '.ics';
        
        if (WP_Filesystem()) {
            global $wp_filesystem;
            $wp_filesystem->put_contents($filePath, $icsContent);
            return [$filePath];
        }
        return [];
    }
    
    public static function getGlobalEmailFooter()
    {
        $globalSettings = Helper::getGlobalSettings();

        return Arr::get($globalSettings, 'emailing.email_footer', '');
    }

}
