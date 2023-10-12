<?php

namespace FluentBooking\App\Services;

use FluentBooking\Framework\Support\Arr;

class EditorShortCodeParser
{
    protected static $requireHtml = true;

    protected static $store = [
        'booking'          => null,
        'calendar_booking' => null,
        'calendar'         => null,
        'host'             => null,
        'user'             => null,
    ];

    public static function parse($parsable, $booking, $requireHtml = true)
    {
        try {
            static::$requireHtml = $requireHtml;
            static::setData($booking);
            return static::parseShortCodes($parsable);
        } catch (\Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log($e->getTraceAsString());
            }
            return '';
        }
    }

    protected static function setData($booking)
    {
        $bookingEvent = $booking->slot;
        static::$store['booking'] = $booking;
        static::$store['booking_event'] = $bookingEvent;
        static::$store['calendar'] = $bookingEvent->calendar;
        static::$store['host'] = $bookingEvent->getAuthorProfile(false);
    }

    protected static function getBookingData($key)
    {
        $bookingEvent = static::$store['booking_event'];
        $calendar = static::$store['calendar'];
        $booking = static::$store['booking'];

        if (!$bookingEvent || !$calendar || !$booking) {
            return '';
        }

        if ($key == 'event_name') {
            return $bookingEvent->title;
        }

        if ($key == 'description') {
            return $bookingEvent->description;
        }

        if ('full_start_end_guest_timezone' == $key) {
            return $booking->getShortBookingDateTime($booking->person_time_zone) . ' (' . $booking->person_time_zone . ')';
        }

        if ($key == 'full_start_end_host_timezone') {
            return $booking->getShortBookingDateTime($calendar->author_timezone) . ' (' . $calendar->author_timezone . ')';
        }

        if ($key == 'start_date_time') {
            return $booking->start_time;
        }

        if ($key == 'start_date_time_for_attendee') {
            return DateTimeHelper::convertFromUtc($booking->start_time, $booking->person_time_zone, 'Y-m-d H:i:s');
        }

        if ($key == 'start_date_time_for_host') {
            return DateTimeHelper::convertFromUtc($booking->start_time, $calendar->author_timezone, 'Y-m-d H:i:s');
        }

        if ($key == 'cancel_reason') {
            return $booking->getCancelReasonDescription();
        }

        if ($key == 'start_time_human_format') {
            if (time() > strtotime($booking->start_time)) {
                $suffix = ' ago';
            } else {
                $suffix = ' from now';
            }

            return human_time_diff(time(), strtotime($booking->start_time)) . ' ' . $suffix;
        }

        if ($key == 'cancelation_url') {
            return add_query_arg([
                'fluent-booking' => 'cancel-booking',
                'booking_token'  => $booking->hash
            ], site_url('index.php'));
        }

        if ($key == 'reschedule_url') {
            return add_query_arg([
                'fluent-booking' => 'reschedule-booking',
                'booking_token'  => $booking->hash
            ], site_url('index.php'));
        }

        if ($key == 'location_details_html') {
            return $booking->getLocationDetailsHtml;
        }

        if ($key == 'booking_hash') {
            return $booking->hash;
        }

        if (property_exists($booking, $key)) {
            return $booking->{$key};
        }

        return '';
    }

    protected static function getHostData($key)
    {
        $host = static::$store['host'];

        if (is_null($host)) {
            return '';
        }

        if ($key == 'timezone') {
            $calendar = static::$store['calendar'];
            return $calendar->author_timezone;
        }

        return Arr::get($host, $key, '');
    }

    protected static function getGuestData($key)
    {
        $guest = static::$store['booking'];
        if (is_null($guest)) {
            return '';
        }

        if ('full_name' == $key) {
            return $guest['first_name'] . ' ' . $guest['last_name'];
        }
        if ('timezone' == $key) {
            $booking = static::$store['booking'];
            return $booking->person_time_zone;
        }
        if ('notes' == $key) {
            return $guest->getMessage();
        }

        if ($key == 'form_data_html') {
            return 'will be available soon';
        }

        return Arr::get($guest, $key, '');
    }

    protected static function getBookingEventData($key)
    {
        $bookingEvent = static::$store['booking_event'];

        if (is_null($bookingEvent)) {
            return '';
        }

        if (property_exists($bookingEvent, $key)) {
            return $bookingEvent->{$key};
        }
        return '';
    }

    protected static function getCalendarData($key)
    {
        $calendar = static::$store['calendar'];

        if (is_null($calendar)) {
            return '';
        }

        if (property_exists($calendar, $key)) {
            return $calendar->{$key};
        }

        return '';
    }

    protected static function getUserData($key)
    {
        if (is_null(static::$store['user'])) {
            static::$store['user'] = wp_get_current_user();
        }
        return static::$store['user']->{$key};
    }

    protected static function getWPData($key)
    {
        if ('site_url' == $key) {
            return site_url();
        }
        if ('admin_email' == $key) {
            return get_option('admin_email');
        }
        if ('site_title' == $key) {
            return get_option('blogname');
        }
        return $key;
    }

    protected static function getOtherData($key)
    {
        if (0 === strpos($key, 'date.')) {
            $format = str_replace('date.', '', $key);
            return date($format, strtotime(current_time('mysql')));
        }
        return $key;
    }

    protected static function parseShortCodes($parsable)
    {
        if (!$parsable) {
            return '';
        }

        return preg_replace_callback('/({{|##)+(.*?)(}}|##)/', function ($matches) {
            $value = '';

            if (empty($matches[2])) {
                return '';
            }

            $match = $matches[2];

            if (false !== strpos($match, 'guest.')) {
                $guestProperty = substr($match, strlen('guest.'));
                $value = static::getGuestData($guestProperty);
            } elseif (false !== strpos($match, 'booking.')) {
                $bookingProperty = substr($match, strlen('booking.'));
                $value = static::getBookingData($bookingProperty);
            } elseif (false !== strpos($match, 'host.')) {
                $hostProperty = substr($match, strlen('host.'));
                $value = static::getHostData($hostProperty);
            } elseif (false !== strpos($match, 'event.')) {
                $eventProperty = substr($match, strlen('host.'));
                $value = static::getBookingEventData($eventProperty);
            } elseif (false !== strpos($match, 'calendar.')) {
                $calendarProperty = substr($match, strlen('calendar.'));
                $value = static::getCalendarData($calendarProperty);
            } else {
                $value = static::getOtherData($match);
            }

            if (static::$requireHtml && is_array($value)) {
                $value = Helper::fcalImplodeRecursive(', ', $value);
            }

            return $value;
        }, $parsable);
    }
}
