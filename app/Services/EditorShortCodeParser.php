<?php

namespace FluentBooking\App\Services;

use FluentBooking\Framework\Support\Arr;

class EditorShortCodeParser
{
    protected static $store = [
        'time'          => null,
        'booking'       => null,
        'calendar'      => null,
        'host'          => null,
        'user'          => null,
    ];

    public static function parse($parsable, $booking, $time = null)
    {
        try {
            static::setData($booking, $time);

            return static::parseShortCodes($parsable);
        } catch (\Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log($e->getTraceAsString());
            }
            return '';
        }
    }

    protected static function setData($booking, $time)
    {
        static::$store['time']     = $time;
        static::$store['booking']  = $booking;
        static::$store['calendar'] = $booking->slot->calendar;
        static::$store['host']     = $booking->slot->getAuthorProfile(false);
    }

    protected static function getEventData($key)
    {
        $event = static::$store['booking'];
        $time  = static::$store['time'];
        if (is_null($event)) {
            return '';
        }

        if('name' == $key) {
            return $event->slot['title'];
        }
        if('datetime' == $key) {
            $timezone = $event['person_time_zone'];
            return $event->getShortBookingDateTime($timezone);
        }
        if('full_datetime' == $key) {
            $timezone = $event['person_time_zone'];
            return $event->getFullBookingDateTimeText($timezone);
        }
        if('location' == $key) {
            return $event->getLocationDetailsHtml();
        }
        if('reminder_time' == $key) {
            return $time['value'] . ' ' . $time['unit'];
        }
        if('cancel_reason' == $key) {
            return $event->getCancelReasonDescription();
        }

        return Arr::get($event, $key);
    }

    protected static function getHostData($key)
    {
        $calendar = static::$store['calendar'];
        $host = static::$store['host'];

        if (is_null($calendar) || is_null($host)) {
            return '';
        }

        if ('timezone' == $key) {
            return $calendar['author_timezone'];
        }

        return Arr::get($host, $key, '');
    }

    protected static function getGuestData($key)
    {
        $guest = static::$store['booking'];
        if (is_null($guest)) {
            return '';
        }

        if('full_name' == $key) {
            return $guest['first_name'] . ' ' . $guest['last_name'];
        }
        if('timezone' == $key) {
            return $guest['person_time_zone'];
        }
        if ('notes' == $key) {
            return $guest->getMessage();
        }

        return Arr::get($guest, $key, '');
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
        return preg_replace_callback('/{+(.*?)}/', function ($matches) {
            $value = '';
            if (false !== strpos($matches[1], 'event.')) {
                $userProperty = substr($matches[1], strlen('event.'));
                $value = static::getEventData($userProperty);
            } elseif (false !== strpos($matches[1], 'host.')) {
                $wpProperty = substr($matches[1], strlen('host.'));
                $value = static::getHostData($wpProperty);
            } elseif (false !== strpos($matches[1], 'guest.')) {
                $wpProperty = substr($matches[1], strlen('guest.'));
                $value = static::getGuestData($wpProperty);
            } elseif (false !== strpos($matches[1], 'user.')) {
                $userProperty = substr($matches[1], strlen('user.'));
                $value = static::getUserData($userProperty);
            } elseif (false !== strpos($matches[1], 'wp.')) {
                $wpProperty = substr($matches[1], strlen('wp.'));
                $value = static::getWPData($wpProperty);
            } else {
                $value = static::getOtherData($matches[1]);
            }

            return $value;
        }, $parsable);
    }
}
