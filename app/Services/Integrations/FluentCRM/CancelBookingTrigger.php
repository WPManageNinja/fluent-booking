<?php

namespace FluentBooking\App\Services\Integrations\FluentCRM;

use FluentBooking\Framework\Support\Arr;
use FluentBooking\App\Models\Calendar;
use FluentBooking\App\Services\Helper;
use FluentCrm\App\Services\Funnel\FunnelHelper;
use FluentCrm\App\Services\Funnel\FunnelProcessor;
use FluentBooking\App\Services\PermissionManager;
use FluentCrm\App\Services\Funnel\BaseTrigger;

class CancelBookingTrigger extends BaseTrigger
{
    public function __construct()
    {
        $this->triggerName = 'fluent_booking/booking_schedule_cancelled';
        $this->actionArgNum = 1;
        $this->priority = 20;
        parent::__construct();
    }

    public function getCalendarOptions()
    {
        $calendarOptions = Helper::getCalendarOptionsByTitle();

        return apply_filters('fluent_booking/crm_trigger_calendar_options', $calendarOptions);
    } 

    public function getTrigger()
    {
        return [
            'category'    => __('Booking', 'fluent-crm'),
            'label'       => __('Booking Cancelled (Fluent Booking)', 'fluent-crm'),
            'description' => __('This Funnel will be initiated when a booking is cancelled', 'fluent-crm'),
            'icon'        => 'fc-icon-fluentforms',
        ];
    }

    public function getFunnelSettingsDefaults()
    {
        return [
            'subscription_status' => 'subscribed'
        ];
    }

    public function getFunnelConditionDefaults($funnel)
    {
        return [
            'run_only_one' => 'no'
        ];
    }

    public function getConditionFields($funnel)
    {
        return [
             'run_only_one' => [
                 'type'        => 'yes_no_check',
                 'label'       => '',
                 'check_label' => __('Run this automation only once per contact. If unchecked then it will over-write existing flow', 'fluent-crm'),
                 'help'        => __('If you enable this then this will run only once per customer otherwise, It will delete the existing automation flow and start new', 'fluent-crm'),
                 'options'     => FunnelHelper::getUpdateOptions()
             ],
        ];
    }

     public function getSettingsFields($funnel)
     {
         return [
             'title'     => __('New Booking Confirm Funnel', 'fluent-crm'),
             'sub_title' => __('This Funnel will be initiated when a new booking has been confirmed.', 'fluent-crm'),
             'fields'    => [
                'slot_id'  => [
                    'type'        => 'grouped-select',
                    'label'       => __('Booking Calendar', 'fluent-crm'),
                    'placeholder' => __('Select Calendar', 'fluent-crm'),
                    'is_multiple' => false,
                    'options'     => $this->getCalendarOptions()
                ],
                'subscription_status' => [
                    'type'        => 'option_selectors',
                    'option_key'  => 'editable_statuses',
                    'is_multiple' => false,
                    'label'       => __('Subscription Status', 'fluent-crm'),
                    'placeholder' => __('Select Status', 'fluent-crm')
                ]
             ]
         ];
     }

    public function handle($funnel, $originalArgs)
    {
        $booking = $originalArgs[0];

        $willProcess = $this->isProcessable($funnel, $booking);

        $willProcess = apply_filters('fluentcrm_funnel_will_process_' . $this->triggerName, $willProcess, $funnel, $originalArgs);
        
        if (!$willProcess) {
            return;
        }

        $subscriberData = [
            'email'  => $booking->email,
            'status' => Arr::get($funnel, 'settings.subscription_status'),
        ];

        (new FunnelProcessor())->startFunnelSequence($funnel, $subscriberData, [
            'source_trigger_name' => $this->triggerName
        ]);
    }

    private function isProcessable($funnel, $booking)
    {
        $slotId = Arr::get($funnel, 'settings.slot_id');

        if ($slotId != $booking->slot_id) {
            return false;
        }

        $subscriber = FunnelHelper::getSubscriber($booking->email);

        if ($subscriber && FunnelHelper::ifAlreadyInFunnel($funnel->id, $subscriber->id)) {

            $runMultiple = Arr::get($funnel, 'conditions.run_only_one') == 'no';
            
            if ($runMultiple) {
                FunnelHelper::removeSubscribersFromFunnel($funnel->id, [$subscriber->id]);
            }

            return $runMultiple;
        }

        return true;
    }
}