<?php

namespace FluentCalendar\App\Saas\Hooks\Handlers;

class FluentCRMConnector
{
    public function register()
    {
        add_action('fluent_calendar/after_create_calendar', [$this, 'addToCrmContact']);
    }

    public function addToCrmContact($calendar)
    {
        $contactApi = FluentCrmApi('contacts');
        $existingContact = $contactApi->getContactByUserRef($calendar->user_id);

        if ($existingContact) {
            $existingContact->attachLists([2]); // 2 is the Organizer list
            $existingContact->attachTags([1]); // Active Users
            return;
        }

        $user = get_user_by('ID', $calendar->user_id);
        $contactApi->createOrUpdate([
            'first_name' => $user->first_name,
            'last_name'  => $user->last_name,
            'email'      => $user->user_email,
            'lists'      => [1, 2], // 1 = Users 2 = Organizers
            'tags'       => [1], // 1 = Active Users
            'status'     => 'subscribed'
        ]);

    }
}
