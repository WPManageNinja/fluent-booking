<?php

(new \FluentCalendar\App\Saas\Hooks\Handlers\SaasHandler)->register();
(new \FluentCalendar\App\Saas\Hooks\Handlers\FluentCRMConnector())->register();

add_filter('fluent_calendar/admin_base_url', function ($url, $extension) {
    return site_url('calendar/#/'.$extension);
}, 10, 2);

add_filter('fluent_calendar/verify_calendar_api', function ($can, $request) {
    if ($can) {
        return $can; // it's and super admin
    }

    $userId = get_current_user_id();
    if (!$userId) {
        return false;
    }

    $calendarId = $request->get('id');

    if (!$calendarId) {
        return true;
    }

    $calendar = \FluentCalendar\App\Models\Calendar::findOrFail($calendarId);
    return $calendar->user_id == $userId;

}, 10, 2);

add_action('fluent_calendar/before_patch_booking_schedule', function ($spot) {
    if ($spot->calendar->user_id != get_current_user_id()) {
        throw new \Exception('You are not allowed to edit this schedule');
    }
});

add_action('fluent_calendar/before_create_calendar', function ($data) {
    $exist = \FluentCalendar\App\Models\Calendar::where('user_id', get_current_user_id())->first();
    if ($exist) {
        throw new \Exception('You already have a calendar. You can not create more than one calendar');
    }
});

add_action('fluent_calendar/calendar', function ($calendar) {
    $calendar->public_url = site_url($calendar->slug);
});

add_filter('fluent_calendar/supported_featured', function ($features) {
    $features['multi_users'] = false;
    $features['is_hosted'] = true;
    return $features;
});

add_action('fluent_calendar/before_update_calendar', function ($calendar, $data) {

    if (!empty($data['slug']) && $calendar->slug != $data['slug']) {
        $newSlug = sanitize_title($data['slug'], $calendar->slug, 'display');

        if(!\FluentCalendar\App\Services\Helper::isCalendarSlugAvailable($newSlug, true, $calendar->id)) {
            throw new \Exception('This slug is not available. Please choose a different calendar slug');
        }
        $calendar->slug = $newSlug;
    }

    $authorData = \FluentCalendar\Framework\Support\Arr::get($data, 'author_profile', []);

    if(!empty($authorData['first_name']) || !empty($authorData['last_name'])) {
        wp_update_user([
            'ID' => $calendar->user_id,
            'first_name' => sanitize_text_field($authorData['first_name']),
            'last_name' => sanitize_text_field($authorData['last_name'])
        ]);
    }

    if(!empty($data['visibility'])) {
        $calendar->visibility = sanitize_text_field($data['visibility']);
    }

}, 10, 2);

add_filter('fluent_calendar/has_all_calendar_access', function ($result) {
    return false;
});

add_action('fluent_calendar/booking_confirmation_footer', function ($booking) {
    ?>
    <div class="book_up">
        <h3>Make your online scheduling Easy . Simple . Automated</h3>
        <a target="_blank" rel="noopener" href="<?php echo site_url('login/?register=1'); ?>">Get Started with ConvertLeap (free)</a>
    </div>
    <?php
});


/*
 * Disable FluentCRM Auto Booking Contact Syncing
 */
add_filter('fluent_calender/auto_booking_fluent_crm_sync', '__return_false');

