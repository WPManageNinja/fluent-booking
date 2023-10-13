<?php

namespace FluentBooking\App\Services\Integrations\FluentCRM;

use FluentCrm\App\Models\Tag;
use FluentCrm\App\Models\Lists;
use FluentCrm\App\Models\Subscriber;
use FluentBooking\Framework\Support\Arr;
use FluentCrm\App\Models\CustomContactField;
use FluentCrm\App\Services\Funnel\FunnelHelper;
use FluentForm\App\Modules\Form\FormFieldsParser;
use FluentForm\App\Services\FormBuilder\ShortCodeParser;
use FluentBooking\App\Http\Controllers\IntegrationManagerController;

class Bootstrap extends IntegrationManagerController
{
    public $hasGlobalMenu = false;

    public $disableGlobalSettings = 'yes';

    public function __construct()
    {
        parent::__construct(
            __('FluentCRM', 'fluent_booking'),
            'fluentcrm',
            '_fluent_booking_fluentcrm_settings',
            'fluentcrm_feeds',
            10
        );

        $this->logo = FLUENTCRM_PLUGIN_URL . 'assets/images/fluentcrm-logo.svg';

        $this->description = __('Connect FluentCRM with Fluent Booking and subscribe a contact when a booking is created.', 'fluent_booking');

        $this->registerAdminHooks();

        add_filter('fluent_booking/notifying_async_fluentcrm', '__return_false');

        // $this->registerPaymentEvents();
    }

    public function pushIntegration($integrations, $slotId)
    {
        $integrations[$this->integrationKey] = [
            'title'                 => $this->title . ' Integration',
            'logo'                  => $this->logo,
            'is_active'             => $this->isConfigured(),
            'configure_title'       => __('Configuration required!', 'fluent_booking'),
            'global_configure_url'  => '#',
            'configure_message'     => __('FluentCRM is not configured yet! Please configure your FluentCRM api first', 'fluent_booking'),
            'configure_button_text' => __('Set FluentCRM', 'fluent_booking'),
        ];

        return $integrations;
    }

    public function getIntegrationDefaults($settings, $slotId)
    {
        return [
            'name'         => '',
            'first_name'   => '',
            'last_name'    => '',
            'full_name'    => '',
            'email'        => '',
            'other_fields' => [
                [
                    'item_value' => '',
                    'label'      => '',
                ],
            ],
            'list_id'                => '',
            'tag_ids'                => [],
            'tag_ids_selection_type' => 'simple',
            'tag_routers'            => [],
            'skip_if_exists'         => false,
            'double_opt_in'          => false,
            'force_subscribe'        => false,
            'skip_primary_data'      => false,
            'conditionals'           => [
                'conditions' => [],
                'status'     => false,
                'type'       => 'all',
            ],
            'run_events_only' => [],
            'remove_tags'     => [],
            'enabled'         => true,
        ];
    }

    public function getSettingsFields($settings, $slotId)
    {
        // $form = fluentFormApi('forms')->find($slotId);
        // $paymentFields = FormFieldsParser::getPaymentFields($form, ['element']);

        $fieldOptions = [];

        foreach (Subscriber::mappables() as $key => $column) {
            $fieldOptions[$key] = $column;
        }

        foreach ((new CustomContactField)->getGlobalFields()['fields'] as $field) {
            $fieldOptions[$field['slug']] = $field['label'];
        }

        $fieldOptions['avatar'] = 'Profile Photo';

        unset($fieldOptions['email']);
        unset($fieldOptions['first_name']);
        unset($fieldOptions['last_name']);

        $fields = [
            [
                'key'         => 'name',
                'label'       => __('Feed Name', 'fluent_booking'),
                'required'    => true,
                'placeholder' => __('Your Feed Name', 'fluent_booking'),
                'component'   => 'text',
            ],
            [
                'key'         => 'list_id',
                'label'       => __('FluentCRM List', 'fluent_booking'),
                'placeholder' => __('Select FluentCRM List', 'fluent_booking'),
                'tips'        => __('Select the FluentCRM List you would like to add your contacts to.', 'fluent_booking'),
                'component'   => 'select',
                'required'    => true,
                'options'     => $this->getLists(),
            ],
            [
                'key'                => 'CustomFields',
                'require_list'       => false,
                'label'              => __('Primary Fields', 'fluent_booking'),
                'tips'               => __('Associate your FluentCRM merge tags to the appropriate Fluent Form fields by selecting the appropriate form field from the list.', 'fluent_booking'),
                'component'          => 'map_fields',
                'field_label_remote' => __('FluentCRM Field', 'fluent_booking'),
                'field_label_local'  => __('Form Field', 'fluent_booking'),
                'primary_fileds'     => [
                    [
                        'key'           => 'email',
                        'label'         => __('Email Address', 'fluent_booking'),
                        'required'      => true,
                        'input_options' => 'emails',
                    ],
                    [
                        'key'   => 'first_name',
                        'label' => __('First Name', 'fluent_booking'),
                    ],
                    [
                        'key'   => 'last_name',
                        'label' => __('Last Name', 'fluent_booking'),
                    ],
                    [
                        'key'       => 'full_name',
                        'label'     => __('Full Name', 'fluent_booking'),
                        'help_text' => __('If First Name & Last Name is not available full name will be used to get first name and last name', 'fluent_booking'),
                    ],
                ],
            ],
            [
                'key'                => 'other_fields',
                'require_list'       => false,
                'label'              => __('Other Fields', 'fluent_booking'),
                'tips'               => __('Select which Fluent Form fields pair with their<br /> respective FlunentCRM fields.', 'fluent_booking'),
                'component'          => 'dropdown_many_fields',
                'field_label_remote' => __('FluentCRM Field', 'fluent_booking'),
                'field_label_local'  => __('Form Field', 'fluent_booking'),
                'options'            => $fieldOptions,
            ],
            [
                'key'                => 'tag_ids',
                'require_list'       => false,
                'label'              => __('Contact Tags', 'fluent_booking'),
                'placeholder'        => __('Select Tags', 'fluent_booking'),
                'component'          => 'selection_routing',
                'simple_component'   => 'select',
                'routing_input_type' => 'select',
                'routing_key'        => 'tag_ids_selection_type',
                'settings_key'       => 'tag_routers',
                'is_multiple'        => true,
                'labels'             => [
                    'choice_label'      => __('Enable Dynamic Tag Selection', 'fluent_booking'),
                    'input_label'       => '',
                    'input_placeholder' => __('Set Tag', 'fluent_booking'),
                ],
                'options' => $this->getTags(),
            ],
            [
                'key'            => 'skip_if_exists',
                'require_list'   => false,
                'checkbox_label' => __('Skip if contact already exist in FluentCRM', 'fluent_booking'),
                'component'      => 'checkbox-single',
            ],
            [
                'key'            => 'skip_primary_data',
                'require_list'   => false,
                'checkbox_label' => __('Skip name update if existing contact have old data (per primary field)', 'fluent_booking'),
                'component'      => 'checkbox-single',
            ],
            [
                'key'            => 'double_opt_in',
                'require_list'   => false,
                'checkbox_label' => __('Enable Double opt-in for new contacts', 'fluent_booking'),
                'component'      => 'checkbox-single',
            ],
            [
                'key'            => 'force_subscribe',
                'require_list'   => false,
                'checkbox_label' => __('Enable Force Subscribe if contact is not in subscribed status (Existing contact only)', 'fluent_booking'),
                'component'      => 'checkbox-single',
                'inline_tip'     => __('If you enable this then contact will forcefully subscribed no matter in which status that contact had', 'fluent_booking'),
            ],
            [
                'require_list' => false,
                'key'          => 'conditionals',
                'label'        => __('Conditional Logics', 'fluent_booking'),
                'tips'         => __('Allow FluentCRM integration conditionally based on your submission values', 'fluent_booking'),
                'component'    => 'conditional_block',
            ],
            [
                'require_list'   => false,
                'key'            => 'event_trigger',
                'options' =>      [
                    'after_booking_scheduled' => 'Booking Confirmed',
                    'booking_schedule_completed' => 'Booking Completed',
                    'booking_schedule_cancelled' => 'Booking Canceled',
                ],
                'label'          => __('Event Trigger', 'fluent_booking'),
                'component'      => 'checkbox-multiple-text',
                'checkbox_label' => __('Event Trigger For This Feed', 'fluent_booking'),
            ]
        ];

        // if ($paymentFields) {
        //     $hasSubscriptionFields = (bool) FormFieldsParser::getInputsByElementTypes($form, ['subscription_payment_component']);

        //     $options = [
        //         'fluentform_payment_refunded' => 'On Payment Refund',
        //     ];

        //     if ($hasSubscriptionFields) {
        //         $options = [
        //             'fluentform_subscription_payment_active'   => __('On Subscription Active', 'fluent_booking'),
        //             'fluentform_subscription_payment_canceled' => __('On Subscription Cancel', 'fluent_booking'),
        //             'fluentform_payment_refunded'              => __('On Payment Refund', 'fluent_booking'),
        //         ];
        //     }

        //     $fields[] = [
        //         'require_list' => false,
        //         'key'          => 'run_events_only',
        //         'label'        => __('Run only on events', 'fluent_booking'),
        //         'component'    => 'checkbox-multiple-text',
        //         'options'      => $options,
        //         'tips'         => __('If you check any of the events then this feed will only run to the selected events', 'fluent_booking'),
        //     ];
        // }

        $fields[] = [
            'require_list' => false,
            'key'          => 'remove_tags',
            'label'        => __('Remove Contact Tags', 'fluent_booking'),
            'placeholder'  => __('Select Tags (remove from contact)', 'fluent_booking'),
            'tips'         => __('(Optional) The selected tags will be removed from the contact (if exist)', 'fluent_booking'),
            'component'    => 'select',
            'is_multiple'  => true,
            'required'     => false,
            'options'      => $this->getTags(),
        ];

        $fields[] = [
            'require_list'   => false,
            'key'            => 'enabled',
            'label'          => __('Status', 'fluent_booking'),
            'component'      => 'checkbox-single',
            'checkbox_label' => __('Enable This feed', 'fluent_booking'),
        ];

        return [
            'fields'              => $fields,
            'button_require_list' => false,
            'integration_title'   => $this->title,
        ];
    }

    public function getMergeFields($list, $listId, $slotId)
    {
        return [];
    }

    protected function getLists()
    {
        $lists = Lists::orderBy('title', 'ASC')->get();
        $formattedLists = [];
        foreach ($lists as $list) {
            $formattedLists[$list->id] = $list->title;
        }

        return $formattedLists;
    }

    protected function getTags()
    {
        $tags = Tag::orderBy('title', 'ASC')->get();
        $formattedTags = [];
        foreach ($tags as $tag) {
            $formattedTags[strval($tag->id)] = $tag->title;
        }

        return $formattedTags;
    }

    /*
     * Submission Hooks Here
     */
    public function notify($feed, $booking, $slot)
    {
        // check if only on payment event
        // if (Arr::get($feed, 'settings.run_events_only')) {
        //     // We have running events selected. So we may not run this feed.
        //     $paymentFields = FormFieldsParser::getPaymentFields($form, ['element']);
        //     if ($paymentFields) {
        //         return false;
        //     }
        // }

        return $this->runFeed($feed, $booking, $slot);
    }

    private function runFeed($feed, $booking, $slot)
    {
        $data = $feed['processedValues'];
        $contact = Arr::only($data, ['first_name', 'last_name', 'email']);

        if (!$contact['first_name'] && !$contact['last_name']) {
            $fullName = Arr::get($data, 'full_name');
            if ($fullName) {
                $nameArray = explode(' ', $fullName);
                if (count($nameArray) > 1) {
                    $contact['last_name'] = array_pop($nameArray);
                    $contact['first_name'] = implode(' ', $nameArray);
                } else {
                    $contact['first_name'] = $fullName;
                }
            }
        }

        foreach (Arr::get($data, 'other_fields') as $field) {
            if ($field['item_value']) {
                $contact[$field['label']] = $field['item_value'];
            }
        }

        if ($booking->ip_address) {
            $contact['ip'] = $booking->ip_address;
        }

        if (!is_email($contact['email'])) {
            $this->addLog(
                $feed['settings']['name'],
                'failed',
                __('FluentCRM API called skipped because no valid email available', 'fluent_booking'),
                $slot->id,
                $booking->id
            );

            return false;
        }

        if (isset($contact['country'])) {
            $country = FunnelHelper::getCountryShortName($contact['country']);
            if ($country) {
                $contact[$contact['country']] = $country;
            } else {
                unset($contact['country']);
            }
        }

        $subscriber = Subscriber::where('email', $contact['email'])->first();

        if ($subscriber && Arr::isTrue($data, 'skip_if_exists')) {
            $this->addLog(
                $feed['settings']['name'],
                'info',
                __('Contact creation has been skipped because contact already exist in the database', 'fluent_booking'),
                $slot->id,
                $booking->id
            );

            return false;
        }

        if (!empty($contact['avatar'])) {
            // validate the avatar photo
            $validUrl = '';
            if (false !== filter_var($contact['avatar'], FILTER_VALIDATE_URL)) {
                $url = $contact['avatar'];
                $dots = explode('.', $url);
                $ext = strtolower(end($dots));

                if (in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'gif'])) {
                    $validUrl = $contact['avatar'];
                }
            }

            if (!$validUrl) {
                unset($contact['avatar']);
            }
        }

        if ($subscriber) {
            if ($subscriber->ip && isset($contact['ip'])) {
                unset($contact['ip']);
            }

            if (Arr::isTrue($data, 'skip_primary_data')) {
                if ($subscriber->first_name) {
                    unset($contact['first_name']);
                    unset($contact['last_name']);
                }
            }
        }

        $user = get_user_by('email', $contact['email']);
        if ($user) {
            $contact['user_id'] = $user->ID;
        }

        $tags = $this->getSelectedTagIds($data, $booking, 'tag_ids');
        if ($tags) {
            $contact['tags'] = $tags;
        }

        if (!$subscriber) {
            if (empty($contact['source'])) {
                $contact['source'] = 'FluentForms';
            }

            if (Arr::isTrue($data, 'double_opt_in')) {
                $contact['status'] = 'pending';
            } else {
                $contact['status'] = 'subscribed';
            }

            if ($listId = Arr::get($data, 'list_id')) {
                $contact['lists'] = [$listId];
            }

            $subscriber = FluentCrmApi('contacts')->createOrUpdate($contact, false, false);

            if (!$subscriber) {
                return false;
            }

            if ('confirmed' == $booking->status && 'subscribed' != $subscriber->status) {
                $oldStatus = $subscriber->status;
                $subscriber->status = 'subscribed';
                $subscriber->save();
                do_action('fluentcrm_subscriber_status_to_subscribed', $subscriber, $oldStatus);
            }

            if ('pending' == $subscriber->status) {
                $subscriber->sendDoubleOptinEmail();
            }

            $this->addLog(
                $feed['settings']['name'],
                'success',
                __('Contact has been created in FluentCRM. Contact ID: ', 'fluent_booking') . $subscriber->id,
                $slot->id,
                $booking->id
            );

            do_action('fluent_crm/contact_added_by_fluent_booking', $subscriber, $booking, $slot, $feed);
        } else {
            if ($listId = Arr::get($data, 'list_id')) {
                $contact['lists'] = [$listId];
            }

            $hasDouBleOptIn = Arr::isTrue($data, 'double_opt_in');

            $forceSubscribed = !$hasDouBleOptIn && ('subscribed' != $subscriber->status);

            if (!$forceSubscribed) {
                $forceSubscribed = Arr::isTrue($data, 'force_subscribe');
            }

            if ($forceSubscribed) {
                $contact['status'] = 'subscribed';
            }

            $subscriber = FluentCrmApi('contacts')->createOrUpdate($contact, $forceSubscribed, false);

            if (!$subscriber) {
                return false;
            }

            if ('confirmed' == $booking->status && 'subscribed' != $subscriber->status) {
                $oldStatus = $subscriber->status;
                $subscriber->status = 'subscribed';
                $subscriber->save();
                do_action('fluentcrm_subscriber_status_to_subscribed', $subscriber, $oldStatus);
            }

            if ($hasDouBleOptIn && ('pending' == $subscriber->status || 'unsubscribed' == $subscriber->status)) {
                $subscriber->sendDoubleOptinEmail();
            }

            do_action('fluent_crm/contact_updated_by_fluent_booking', $subscriber, $booking, $slot, $feed);

            if ($removeTags = Arr::get($feed, 'settings.remove_tags', [])) {
                $subscriber->detachTags($removeTags);
            }

            $this->addLog(
                $feed['settings']['name'],
                'success',
                __('Contact has been updated in FluentCRM. Contact ID: ', 'fluent_booking') . $subscriber->id,
                $slot->id,
                $booking->id
            );
        }
    }

    public function isConfigured()
    {
        return true;
    }

    public function isEnabled()
    {
        return true;
    }

    protected function addLog($title, $status, $description, $slotId, $entryId)
    {
        do_action('fluent_booking_log_data', [
            'title'            => $title,
            'status'           => $status,
            'description'      => $description,
            'parent_source_id' => $slotId,
            'source_id'        => $entryId,
            'component'        => $this->integrationKey,
            'source_type'      => 'submission_item',
        ]);
    }

    /*
     * We will remove this in future
     */
    protected function getSelectedTagIds($data, $inputData, $simpleKey = 'tag_ids', $routingId = 'tag_ids_selection_type', $routersKey = 'tag_routers')
    {
        $routing = Arr::get($data, $routingId, 'simple');
        if (!$routing || 'simple' == $routing) {
            return Arr::get($data, $simpleKey, []);
        }

        $routers = Arr::get($data, $routersKey);
        if (empty($routers)) {
            return [];
        }

        return $this->evaluateRoutings($routers, $inputData);
    }

    /*
     * We will remove this in future
     */
    protected function evaluateRoutings($routings, $inputData)
    {
        $validInputs = [];
        foreach ($routings as $routing) {
            $inputValue = Arr::get($routing, 'input_value');
            if (!$inputValue) {
                continue;
            }
            $condition = [
                'conditionals' => [
                    'status'     => true,
                    'is_test'    => true,
                    'type'       => 'any',
                    'conditions' => [
                        $routing,
                    ],
                ],
            ];

            // if (\FluentForm\App\Services\ConditionAssesor::evaluate($condition, $inputData)) {
            //     $validInputs[] = $inputValue;
            // }
        }

        return $validInputs;
    }

    // private function registerPaymentEvents()
    // {
    //     add_action('fluentform_subscription_payment_active', function ($subscription, $submission) {
    //         $this->handlePaymentEvent($submission, 'fluentform_subscription_payment_active');
    //     }, 10, 2);
    //     add_action('fluentform_subscription_payment_canceled', function ($subscription, $submission) {
    //         $this->handlePaymentEvent($submission, 'fluentform_subscription_payment_canceled');
    //     }, 10, 2);

    //     add_action('fluentform_payment_refunded', function ($refund, $transaction, $submission) {
    //         $this->handlePaymentEvent($submission, 'fluentform_payment_refunded');
    //     }, 10, 3);
    // }

    // private function handlePaymentEvent($submission, $event)
    // {
    //     // Get Fluent Forms Feeds
    //     $feeds = fluentCrmDb()->table('fluentform_form_meta')
    //         ->where('form_id', $submission->form_id)
    //         ->where('meta_key', 'fluentcrm_feeds')
    //         ->orderBy('id', 'ASC')
    //         ->get();

    //     if (!$feeds) {
    //         return false;
    //     }
    //     if (!is_array($submission->response)) {
    //         $formData = json_decode($submission->response, true);
    //     } else {
    //         $formData = $submission->response;
    //     }

    //     $form = fluentFormApi('forms')->find($submission->form_id);

    //     $notificationManager = new GlobalNotificationManager(wpFluentForm());

    //     foreach ($feeds as $feed) {
    //         $parsedValue = json_decode($feed->value, true);
    //         if ($parsedValue && Arr::isTrue($parsedValue, 'enabled')) {
    //             $runEvents = Arr::get($parsedValue, 'run_events_only', []);

    //             // check if this is our event or not
    //             if (!$runEvents || !in_array($event, $runEvents)) {
    //                 continue;
    //             }

    //             // Now check if conditions matched or not
    //             $isConditionMatched = $notificationManager->checkCondition($parsedValue, $formData, $submission->id);
    //             if ($isConditionMatched) {
    //                 $item = [
    //                     'id'       => $feed->id,
    //                     'meta_key' => $feed->meta_key,
    //                     'settings' => $parsedValue,
    //                 ];

    //                 $processedValues = $item['settings'];
    //                 unset($processedValues['conditionals']);

    //                 $item['processedValues'] = ShortCodeParser::parse($processedValues, $submission->id, $formData, $form, false, $feed->meta_key);

    //                 $this->runFeed($item, $formData, $submission, $form);
    //             }
    //         }
    //     }
    // }
}
