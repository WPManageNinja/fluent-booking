<?php

namespace FluentCalendar\App\Services;

use FluentCalendar\Framework\Support\Arr;

class SanitizeService
{
    public static function weeklySchedules($schedules, $fromTimeZone = '', $toTimeZone = false)
    {
        foreach ($schedules as &$schedule) {
            $schedule['enabled'] = Arr::isTrue($schedule, 'enabled');
            if(!$schedule['enabled'] || empty($schedule['slots'] )) {
                $schedule['slots'] = [];
                $schedule['enabled'] = false;
                continue;
            }

            $schedule['enabled'] = true;

            foreach ($schedule['slots'] as $index => $slot) {
                $slot['start'] = sanitize_text_field($slot['start']);
                $slot['end'] = sanitize_text_field($slot['end']);

                if(!$slot['start'] || ! $slot['end']) {
                    unset($schedule['slots'][$index]);
                    continue;
                }

                if($toTimeZone && $fromTimeZone) {
                    $slot['start'] = DateTimeHelper::convertToTimeZone($slot['start'], $fromTimeZone, $toTimeZone, 'H:i');
                    $slot['end'] = DateTimeHelper::convertToTimeZone($slot['end'], $fromTimeZone, $toTimeZone, 'H:i');
                }

                $schedule['slots'][$index] = $slot;
            }

            $schedule['slots'] = array_values($schedule['slots']);
        }

        return $schedules;
    }
}
