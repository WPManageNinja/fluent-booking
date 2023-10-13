<?php

namespace FluentBooking\App\Services\Integrations;

use FluentBooking\App\Models\Meta;
use FluentBooking\Framework\Support\Arr;
use FluentBooking\Framework\Validator\ValidationException;

class CalendarIntegrationService
{
    public function find($attr)
    {
        $slotId = intval(Arr::get($attr, 'slot_id'));
        $integrationId = intval(Arr::get($attr, 'integration_id'));
        $integrationName = sanitize_text_field(Arr::get($attr, 'integration_name'));

        $settings = [
            'conditionals' => [
                'conditions' => [],
                'status'     => false,
                'type'       => 'all',
            ],
            'enabled'      => true,
            'list_id'      => '',
            'list_name'    => '',
            'name'         => '',
            'merge_fields' => [],
        ];

        $mergeFields = false;
        if ($integrationId) {
            $feed = Meta::where(['object_id' => $slotId, 'id' => $integrationId])->first();

            if ($feed->value) {
                $settings = json_decode($feed->value, true);
                $settings = apply_filters('fluent_booking/get_integration_values_' . $integrationName, $settings, $feed, $slotId);
                if (!empty($settings['list_id'])) {
                    $mergeFields = apply_filters('fluent_booking/get_integration_merge_fields_' . $integrationName, false, $settings['list_id'], $slotId);
                }
            }
        } else {
            $settings = apply_filters('fluent_booking/get_integration_defaults_' . $integrationName, false, $slotId);
        }

        if ('true' == $settings['enabled']) {
            $settings['enabled'] = true;
        } elseif ('false' == $settings['enabled'] || $settings['enabled']) {
            $settings['enabled'] = false;
        }

        $settingsFields = apply_filters('fluent_booking/get_integration_settings_fields_' . $integrationName, $settings, $slotId, $settings);

        return [
            'settings'        => $settings,
            'settings_fields' => $settingsFields,
            'merge_fields'    => $mergeFields,
        ];
    }

    public function update($attr)
    {
        $slotId = intval(Arr::get($attr, 'slot_id'));
        $integrationId = intval(Arr::get($attr, 'integration_id'));
        $integrationName = sanitize_text_field(Arr::get($attr, 'integration_name'));
        $dataType = sanitize_text_field(Arr::get($attr, 'data_type'));
        $status = Arr::get($attr, 'status', true);
        $metaValue = Arr::get($attr, 'integration');

        if ('stringify' == $dataType) {
            $metaValue = \json_decode($metaValue, true);
        } else {
            $metaValue = wp_unslash($metaValue);
        }
        $isUpdatingStatus = empty($metaValue);

        if ($isUpdatingStatus) {
            $integrationData = Meta::findOrFail($integrationId);
            $metaValue = \json_decode($integrationData->value, true);
            $metaValue['enabled'] = $status;
            $metaKey = $integrationData->key;
        } else {
            if (empty($metaValue['name'])) {
                $errors['name'] = [__('Feed name is required', 'fluentform')];
                throw new ValidationException(__('Validation Failed! Feed name is required', 'fluent_booking'), 423, null, $errors);
            }
            $metaValue = apply_filters('fluent_booking/save_integration_value_' . $integrationName, $metaValue, $integrationId, $slotId);
            $metaKey = $integrationName . '_feeds';
        }

        $data = [
            'object_id'   => $slotId,
            'object_type' => 'integration',
            'key'         => $metaKey,
            'value'       => \json_encode($metaValue),
        ];

        $data = apply_filters('fluent_booking/save_integration_settings_' . $integrationName, $data, $integrationId);
        $created = false;

        if ($integrationId) {
            Meta::where('object_id', $slotId)
                ->where('id', $integrationId)
                ->update($data);
        } else {
            $integrationId = Meta::insertGetId($data);
            $created = true;
        }

        return [
            'message'          => __('Integration successfully saved', 'fluent_booking'),
            'integration_id'   => $integrationId,
            'integration_name' => $integrationName,
            'created'          => $created,
        ];
    }

    public function get($slotId)
    {
        $notificationKeys = apply_filters('fluent_booking/global_notification_types', [], $slotId);

        $feeds = [];
        if ($notificationKeys) {
            $feeds = Meta::whereIn('key', $notificationKeys)->where('object_id', $slotId)->get();
        }
        $formattedFeeds = [];

        if (!empty($feeds)) {
            foreach ($feeds as $feed) {
                $data = json_decode($feed->value, true);
                $enabled = $data['enabled'];
                if ($enabled && 'true' == $enabled) {
                    $enabled = true;
                } elseif ('false' == $enabled) {
                    $enabled = false;
                }
                $feedData = [
                    'id'       => $feed->id,
                    'name'     => Arr::get($data, 'name'),
                    'enabled'  => $enabled,
                    'provider' => $feed->meta_key,
                    'feed'     => $data,
                ];

                $feedData = apply_filters('fluent_booking/global_notification_feed_' . $feed->meta_key, $feedData, $slotId);

                $formattedFeeds[] = $feedData;
            }
        }

        $availableIntegrations = apply_filters('fluent_booking/get_available_form_integrations', [], $slotId);

        return [
            'feeds'                  => $formattedFeeds,
            'available_integrations' => $availableIntegrations,
            'all_module_config_url'  => admin_url('admin.php?page=fluent_forms_add_ons'),
        ];
    }

    public function delete($id)
    {
        Meta::where('id', $id)->delete();
    }
}
