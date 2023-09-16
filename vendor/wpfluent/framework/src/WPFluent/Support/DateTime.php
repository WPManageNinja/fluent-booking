<?php

namespace FluentCalendar\Framework\Support;;

use DateTimeZone;
use DateTime as PHPDateTime;
use InvalidArgumentException;

class DateTime extends PHPDateTime
{
    /**
     * Construct the DateTime Object
     * 
     * @param string $datetime
     * @param \DateTimeZone $timezone|null
     */
    public function __construct($datetime = "now", $timezone = null)
    {
        $timezone = $timezone ?: $this->getTimezone();

        parent::__construct($datetime, $timezone);
    }

    /**
     * Create a new DateTime Object with current time
     * 
     * @return self
     */
    public static function now()
    {
        return new static;
    }

    /**
     * Get the timezone
     *
     * @return \DateTimeZone
     */
    #[\ReturnTypeWillChange]
    public function getTimezone()
    {
        // if site timezone string exists, return it
        if ($timezone = wp_timezone_string()) {
            return new DateTimeZone($timezone);
        }

        // get UTC offset, if it isn't set then return UTC
        $utcOffset = get_option('gmt_offset', 0);
        if ($utcOffset === 0) {
            return new DateTimeZone('UTC');
        }

        // Adjust UTC offset from hours to seconds
        $utcOffset *= 3600;

        // Attempt to guess the timezone string from the UTC offset
        $timezone = timezone_name_from_abbr('', $utcOffset, 0);
        if ($timezone) {
            return new DateTimeZone($timezone);
        }

        // Guess timezone string manually
        $isDst = date('I');
        foreach (timezone_abbreviations_list() as $abbr) {
            foreach ($abbr as $city) {
                if ($city['dst'] == $isDst && $city['offset'] == $utcOffset) {
                    $timezoneId = $city['timezone_id'];
                    $timezone = $timezoneId ?: timezone_name_from_abbr('', $timezoneId, 0);
                    if ($timezone) return new DateTimeZone($timezone);
                }
            }
        }

        // Fallback
        return new DateTimeZone('UTC');
    }

    /**
     * Get the default date format
     * 
     * @return string
     */
    public function getDateFormat()
    {   
        return 'Y-m-d H:i:s';
    }

    /**
     * {@inheritdoc}
     */
    #[\ReturnTypeWillChange]
    public static function createFromFormat($format, $datetimeString, $timezone = null)
    {
        $timezone = $timezone ?: (new static)->getTimezone();

        if (!($timezone instanceof DateTimeZone)) {
            $timezone = new DateTimeZone($timezone);
        }

        $dateTime = new PHPDateTime($datetimeString, $timezone);

        if ($dateTime instanceof PHPDateTime) {
            return new static($dateTime->format($format), $timezone);
        }

        throw new InvalidArgumentException('Unable to handle datetime.');
    }

    /**
     * Parse a datetime string
     * @param  string $datetimeString
     * @param  string $timezone
     * @return self
     * @throws InvalidArgumentException
     */
    public static function parse($datetimeString, $timezone = null)
    {
        $parsedDate = date_parse($datetimeString);
        
        $datetimeString = date('Y-m-d H:i:s', mktime(
            $parsedDate['hour'],
            $parsedDate['minute'],
            $parsedDate['second'],
            $parsedDate['month'],
            $parsedDate['day'],
            $parsedDate['year']
        ));

        if ($timezone && is_scalar($timezone)) {
            $timezone = new DateTimeZone($timezone);
        } elseif (isset($parsedDate['tz_id'])) {
            $timezone = new DateTimeZone($parsedDate['tz_id']);
        }

        $dateTime = new PHPDateTime($datetimeString, $timezone);

        if ($dateTime instanceof PHPDateTime) {
            return new static($datetimeString, $timezone);
        }

        throw new InvalidArgumentException('Unable to handle datetime.');
    }

    /**
     * Get human friendly time difference (2 hours ago/ 2 hours from now)
     * 
     * @param  \DateTime|string|timestamp $from The datetime to compare from
     * @param  \DateTime|string|timestamp $to The datetime to compare to (default: time())

     * @return string Human readable string, ie. 5 days ago/from now
     */
    public function diffForHumans($from = null, $to = null)
    {
        // Convert the $from value to unix timestamp if needed.
        if (is_null($from)) {
            $from = (new DateTime($this->format($this->getDateFormat())))->getTimestamp();
        } else {
            if (!is_numeric($from)) {
                $from = ($from instanceof DateTime ? $from : new DateTime($from))->getTimestamp();
            }
        }

        // Convert the $to value to unix timestamp if needed.
        if (!is_null($to)) {
            if (!is_numeric($to)) {
                $to = ($to instanceof DateTime ? $to : new DateTime($to))->getTimestamp();
            }
        }

        $dateTime = human_time_diff($from, $to);

        $diff = (time() - $from);

        if ($diff > 0) {
            if ($diff < 60) {
                $message = sprintf(__('just now'), $dateTime);
            } else {
                $message = sprintf(__('%s ago'), $dateTime);
            }
        } else {
            $message = sprintf(__('%s from now'), $dateTime);
        }

        return $message;
    }

    /**
     * Return the ISO-8601 string
     *
     * @see https://stackoverflow.com/a/11173072/741747
     *
     * @return mixed
     */
    public function toJSON()
    {
        return date('c', $this->getTimestamp());
    }

    public function __toString()
    {
        return $this->format($this->getDateFormat());
    }
}
