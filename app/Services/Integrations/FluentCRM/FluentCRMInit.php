<?php

namespace FluentBooking\App\Services\Integrations\FluentCRM;

use FluentBooking\App\Models\Booking;
use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\App\Services\DateTimeHelper;

class FluentCRMInit
{
    public function init()
    {
        add_action('fluent_crm/global_appjs_loaded', [$this, 'enqueueAssets'], 10);
        add_action('fluent_booking/booking_schedule', [$this, 'addProfileLink'], 10, 1);
        add_filter('fluentcrm_profile_sections', [$this, 'addProfileSection'], 10, 1);
        add_filter('fluent_crm/scheduled_meeting_providers', [$this, 'pushProvider'], 10, 1);
        add_filter('fluent_crm/get_scheduled_meetings_fluent_booking', [$this, 'getScheduledMeetings'], 10, 2);      
    }

    public function enqueueAssets()
    {
        wp_enqueue_script('fluent_booking_crm_profile_extended', FLUENT_BOOKING_URL . 'assets/admin/fluentcrm.js');
    }

    private function getSubscriberId($email)
    {
        $contact = FluentCrmApi('contacts')->getContact($email);
        return $contact ? $contact->id : null;
    }

    public function addProfileLink(&$booking)
    {
        $subscriberId = $booking->person_user_id ?: $this->getSubscriberId($booking->email);

        if (!$subscriberId) {
            return;
        }
        
        $url = admin_url('admin.php?page=fluentcrm-admin#/subscribers/' . $subscriberId);

        $link = '<a target="_blank" href="' . esc_url($url) . '">' . 'profile' . '</a>';

        $booking->crm_profile = $link;
    }

    public function addProfileSection($sections)
    {
        $sections['subscriber_scheduled_meetings'] = [
            'name'    => 'subscriber_scheduled_meetings',
            'title'   => __('Scheduled Meetings', 'fluent-crm'),
            'handler' => 'route'
        ];

        return $sections;
    }
    
    public function pushProvider($providers)
    {
        $providers['fluent_booking'] = [
            'title' => __('Upcoming Meetings (Fluent Booking)', 'fluent-crm'),
            'name'  => __('Fluent Booking', 'fluent-crm')
        ];

        return $providers;
    }

    private function getActionUrl($meeting)
    {
        $url = admin_url('admin.php?page=fluent-booking#/scheduled-events?spot_id=' . $meeting->event_id);

        $link = '<a target="_blank" href="' . esc_url($url) . '">' . 'view' . '</a>';
        
        return $link;
    }

    private function getFormattedTime($meeting)
    {
        $formattedTime = DateTimeHelper::convertToTimeZone($meeting->start_time, 'utc', $meeting->calendar->author_timezone, 'j M Y, g:i A');

        return $formattedTime;
    }

    public function getScheduledMeetings($data, $subsriber)
    {
        $app      = fluentCrm();
        $page     = intval($app->request->get('page', 1));
        $perPage  = intval($app->request->get('per_page', 10));

        $meetings = Booking::with(['slot', 'calendar'])
            ->whereHas('calendar', function ($query) use ($subsriber) {
                $query->where('user_id', $subsriber->user_id);
            })
            ->upcoming()
            ->distinct('event_id')
            ->orderBy('start_time', 'ASC')
            ->whereIn('status', ['scheduled', 'pending'])
            ->paginate();

        $formattedMeetings = [];

        foreach ($meetings->items() as $meeting){
            $formattedMeetings[] = [
                'id'           => '#'.$meeting->event_id,
                'title'        => $meeting->slot->title,
                'status'       => $meeting->status,
                'meeting_at'   => $this->getFormattedTime($meeting),
                'action'       => $this->getActionUrl($meeting)
            ];
        }

        return [
            'total' => $meetings->total(),
            'data'  => $formattedMeetings,
            'columns_config' => [
                'id' => [
                    'label' => 'ID',
                    'width' => '100px'
                ],
                'title' => [
                    'label' => 'Title',
                ],
                'status' => [
                    'label' => 'Status',
                    'width' => '150px'
                ],
                'meeting_at' => [
                    'label' => 'Meeting At',
                    'width' => '200px'
                ],
                'action' => [
                    'label' => 'Action',
                    'width' => '100px'
                ]
            ]
        ];
    }

}
