<?php

namespace FluentBooking\App\Services\Integrations\FluentCRM;

use FluentBooking\App\Models\Booking;
use FluentBooking\App\Services\DateTimeHelper;
use FluentBooking\App\Services\Integrations\FluentCRM\NewBookingTrigger;
use FluentBooking\App\Services\Integrations\FluentCRM\CancelBookingTrigger;

class FluentCrmInit {

	public function __construct() {
        $this->registerHooks();
		$this->registerIntegrations();
	}

	/**
	 * Register all the CRM integrations from here
	 * @return void
	 */
	public function registerIntegrations()
	{
		$this->addContactMenuSection();
		$this->addAutomations();
	}

    public function registerHooks()
    {
//        add_action('fluent_booking/booking_schedule', [$this, 'addProfileLink'], 10, 1);
        add_filter('fluentcrm_profile_sections', [$this, 'addProfileSection'], 10, 1);
        add_filter('fluentcrm_get_form_submissions_fluent_booking', [$this, 'getScheduledMeetings'], 10, 2);      
    }

	/**
	 * load Assets for to Fluent CRM  contact section
	 * @return void
	 */
	public function addContactMenuSection()
	{
		add_action( 'fluent_crm/global_appjs_loaded', function () {
			wp_enqueue_script( 'fluent_booking_in_crm', FLUENT_BOOKING_URL . 'assets/admin/fluent-crm-in-calendar.js');
		});
	}

	public function addAutomations()
	{
        new NewBookingTrigger();
        new CancelBookingTrigger();
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
        $sections['booking'] = [
            'name'    => 'booking',
            'title'   => __('Bookings', 'fluent-crm'),
            'handler' => 'route'
        ];

        return $sections;
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
            ->where('email', $subsriber->email)
            ->distinct('event_id')
            ->orderBy('start_time', 'DESC')
            ->paginate();

        $formattedMeetings = [];

        foreach ($meetings->items() as $meeting){
            $host = $meeting->calendar->getAuthorProfile();
            $formattedMeetings[] = [
                'id'           => '#'.$meeting->event_id,
                'title'        => $meeting->slot->title . ' with ' . $host['name'],
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
                    'label' => 'Event',
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