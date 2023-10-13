<?php

namespace FluentBooking\App\Services\Integrations;

use FluentBooking\App\Models\Meta;
use FluentBooking\Framework\Support\Arr;
use FluentBooking\App\Services\ConditionAssesor;

class GlobalNotificationService
{
    public function checkCondition($parsedValue, $booking, $insertId)
    {
        $conditionSettings = Arr::get($parsedValue, 'conditionals');
        if (
            !$conditionSettings ||
            !Arr::isTrue($conditionSettings, 'status') ||
            !count(Arr::get($conditionSettings, 'conditions'))
        ) {
            return true;
        }

        return ConditionAssesor::evaluate($parsedValue, $booking);
    }

    public function getEntry($id, $form)
    {
        // $submission = Submission::find($id);
        // $formInputs = FormFieldsParser::getEntryInputs($form, ['admin_label', 'raw']);
        // return bookingParser::parseFormEntry($submission, $form, $formInputs);
    }

    /**
     * @param $feeds
     * @param $booking
     * @param $insertId
     *
     * @return array
     */
    public function getEnabledFeeds($feeds, $booking, $insertId)
    {
        $enabledFeeds = [];
        foreach ($feeds as $feed) {
            $parsedValue = json_decode($feed->value, true);
            if ($parsedValue && Arr::isTrue($parsedValue, 'enabled')) {
                // Now check if conditions matched or not
                $isConditionMatched = $this->checkCondition($parsedValue, $booking, $insertId);
                if ($isConditionMatched) {
                    $item = [
                        'id'       => $feed->id,
                        'key'      => $feed->key,
                        'settings' => $parsedValue,
                    ];

                    if ('user_registration_feeds' == $feed->key) {
                        array_unshift($enabledFeeds, $item);
                    } else {
                        $enabledFeeds[] = $item;
                    }
                }
            }
        }

        return $enabledFeeds;
    }

    public function getNotificationFeeds($slotId, $feedMetaKeys)
    {
        return Meta::where('object_id', $slotId)->whereIn('meta_key', $feedMetaKeys)->orderBy('id', 'ASC')->get();
    }
}
