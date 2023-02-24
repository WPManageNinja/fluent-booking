<?php

namespace FluentCalendar\App\Services;

class DateTimeHelper
{
    public static function getTimeZones($grouped = false)
    {
        $tz_identifiers = timezone_identifiers_list();

        if (!$grouped) {
            return $tz_identifiers;
        }

        $lists = [];

        $topLevels = [];
        foreach ($tz_identifiers as $tz) {
            $parts = explode('/', $tz);
            if (count($parts) > 1) {
                $lists[$parts[0]][] = $tz;
            } else {
                $topLevels[$tz][] = $tz;
            }
        }

        return array_merge($topLevels, $lists);
    }

    public static function getFlatGroupedTimeZones()
    {
        $tz_identifiers = timezone_identifiers_list();

        $lists = [];
        $topLevels = [];
        foreach ($tz_identifiers as $tz) {
            $parts = explode('/', $tz);
            if (count($parts) > 1) {
                $lists[] = [
                    'label' => $tz,
                    'value' => $tz,
                    'group' => $parts[0]
                ];
            } else {
                $topLevels[] = [
                    'label' => $tz,
                    'value' => $tz,
                    'group' => $tz
                ];
            }
        }

        return $topLevels + $lists;
    }

    public static function convertToUtc($dateTime, $timezone, $format = 'Y-m-d H:i:s')
    {
        $dateTime = new \DateTime($dateTime, new \DateTimeZone($timezone));
        $dateTime->setTimezone(new \DateTimeZone('UTC'));
        return $dateTime->format($format);
    }

    public static function convertFromUtc($dateTime, $timezone, $format = 'Y-m-d H:i:s')
    {
        $dateTime = new \DateTime($dateTime, new \DateTimeZone('UTC'));

        if($timezone != 'UTC') {
            $dateTime->setTimezone(new \DateTimeZone($timezone));
        }

        return $dateTime->format($format);
    }

    public static function convertToTimeZone($dateTime, $fromTimeZone, $toTimeZone, $format = 'Y-m-d H:i:s')
    {
        if($fromTimeZone == $toTimeZone) {
            return date($format, strtotime($dateTime));
        }

        $dateTime = new \DateTime($dateTime, new \DateTimeZone($fromTimeZone));
        $dateTime->setTimezone(new \DateTimeZone($toTimeZone));
        return $dateTime->format($format);
    }

    public static function getTimestamp($timezone = 'UTC')
    {
        $dateTime = new \DateTime(date('Y-m-d H:i:s'), new \DateTimeZone('UTC'));
        $dateTime->setTimezone(new \DateTimeZone($timezone));
        $date = $dateTime->format('Y-m-d H:i:s');
        return strtotime($date);
    }
}
