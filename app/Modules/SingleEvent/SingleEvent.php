<?php

namespace FluentBooking\App\Modules\SingleEvent;

class SingleEvent
{
    public function register()
    {
        // add_action('fluent_booking/public_event_vars', [$this, 'publicEventVars']);
    }

    public function publicEventVars($eventVars)
    {
        foreach ($eventVars['form_fields'] as &$field) {
            if ($field['type'] === 'date' && !empty($field['date_format'])) {
                $field['date_format'] = \FluentBooking\App\Services\DateTimeHelper::convertPhpDateToDayJSFormay($field['date_format']);
            }
        }

        return $eventVars;
    }
}