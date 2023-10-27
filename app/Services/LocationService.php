<?php

namespace FluentBooking\App\Services;

use FluentBooking\App\App;
use FluentBooking\App\Models\Booking;
use FluentBooking\Framework\Support\Arr;

class LocationService
{
    public static function getLocationIconHeadingHtml($details = [], $calendarEvent = null)
    {
        $html = '';
        $app = App::getInstance();

        foreach ($details as $location) {

            $displayOnBooking = Arr::get($location, 'display_on_booking') == 'yes';

            $html .= '<div class="slot_location fcal_icon_item fcal_img_item fcal_loc_google_meet">';
            if ($location['type'] == 'google_meet') {
                $html .= '<img class="fcal_loc_icon" src="' . $app['url.assets'] . 'images/google-meet.svg" alt="Google Meet" />';
                $html .= '<span class="fcal_loc_text">' . __('Google Meet', 'fluent-booking-pro') . '</span>';
            } else if ($location['type'] == 'zoom_meeting') {
                $html .= '<img class="fcal_loc_icon" src="' . $app['url.assets'] . 'images/zoom.svg" alt="Zoom Icon" />';
                $html .= '<span class="fcal_loc_text">' . __('Zoom Video', 'fluent-booking-pro') . '</span>';
            } else if ($location['type'] == 'online_meeting') {
                $html .= '<img class="fcal_loc_icon" src="' . $app['url.assets'] . 'images/link.svg" alt="Online Meeting" />';
                if ($displayOnBooking == 'yes') {
                    $html .= '<span class="fcal_loc_text">' . '<span class="fcal_loc_title">' . $location['meeting_link'] . '</span>';
                } else {
                    $html .= '<span class="fcal_loc_text">' . __('Online Meeting', 'fluent-booking-pro') . '</span>';
                }
            } else if ($location['type'] == 'in_person_guest') {
                $html .= '<img class="fcal_loc_icon" src="' . $app['url.assets'] . 'images/physical_location.svg" alt="' . __('Zoom Icon', 'fluent-booking-pro') . '" />';
                $html .= '<span class="fcal_loc_text">' . __('In Person (Attendee Address)', 'fluent-booking-pro') . '</span>';
            } else if ($location['type'] == 'custom') {
                $html .= '<img class="fcal_loc_icon" src="' . $app['url.assets'] . 'images/physical_location.svg" alt="' . __('Zoom Icon', 'fluent-booking-pro') . '" />';
                if ($displayOnBooking == 'yes') {
                    $html .= '<span class="fcal_loc_text">' . $location['description'] . '</span>';
                } else {
                    $html .= '<span class="fcal_loc_text">' . $location['title'] . '</span>';
                }
            } else if ($location['type'] == 'in_person_organizer') {
                $html .= '<img class="fcal_loc_icon" src="' . $app['url.assets'] . 'images/physical_location.svg" alt="' . __('Zoom Icon', 'fluent-booking-pro') . '" />';
                if ($displayOnBooking == 'yes') {
                    $html .= '<span class="fcal_loc_text">' . $location['description'] . '</span>';
                } else {
                    $html .= '<span class="fcal_loc_text"> In Person (Organizer Address) </span>';
                }
            } else if ($location['type'] == 'phone_guest') {
                $html .= '<img class="fcal_loc_icon" src="' . $app['url.assets'] . 'images/phone_call.svg" alt="' . __('Phone', 'fluent-booking-pro') . '" />';
                $html .= '<span class="fcal_loc_text">' . __('Attendee Phone Number', 'fluent-booking-pro') . '</span>';
            } else if ($location['type'] == 'phone_organizer') {
                $html .= '<img class="fcal_loc_icon" src="' . $app['url.assets'] . 'images/phone_call.svg" alt="' . __('Phone', 'fluent-booking-pro') . '" />';

                if ($displayOnBooking == 'yes') {
                    $html .= '<span class="fcal_loc_text">' . $location['host_phone_number'] . '</span>';
                } else {
                    $html .= '<span class="fcal_loc_text">' . __('Phone Call', 'fluent-booking-pro') . '</span>';
                }
            }
            $html .= '</div>';

        }

        return apply_filters('fluent_booking/location_icon_heading_html', $html, $details, $calendarEvent);

    }

    public static function getBookingLocationUrl(Booking $booking)
    {
        $details = $booking->location_details;

        if (!$details || empty($details['type'])) {
            return $booking->getConfirmationUrl();
        }

        if (!empty($details['online_platform_link'])) {
            return Arr::get($details, 'online_platform_link');
        }

        return $booking->getConfirmationUrl();
    }

    public static function getLocationDetails($calendarEvent, $userInput = [], $allInput = [])
    {
        $userInput = array_map('sanitize_text_field', $userInput);

        $locations = $calendarEvent->location_settings;

        if (empty($locations)) {
            return [
                'type'        => 'custom',
                'description' => ''
            ];
        }

        if (empty($allInput)) {
            $locations = [$locations[0]];
        }

        if (count($locations) == 1) {
            // return the first location
            $defaultLocation = $locations[0];

            $type = Arr::get($defaultLocation, 'type');

            $userInput = [
                'driver' => $type
            ];

            if ($type == 'phone_guest') {
                $userInput['user_location_input'] = Arr::get($allInput, 'phone_number');
            } else if ($type == 'in_person_guest') {
                $userInput['user_location_input'] = Arr::get($allInput, 'address');
            }
        }

        $keyedLocations = [];
        foreach ($locations as $location) {
            $keyedLocations[$location['type']] = $location;
        }

        $driver = Arr::get($userInput, 'driver');

        if (empty($keyedLocations[$driver])) {
            return [
                'type'        => 'custom',
                'description' => ''
            ];
        }

        // custom user input location types
        $userInputTypes = ['in_person_guest', 'phone_guest'];

        if (in_array($driver, $userInputTypes)) {
            return [
                'type'        => $driver,
                'description' => Arr::get($userInput, 'user_location_input')
            ];
        }

        // Check provided description location type to store as description
        $customTypes = ['custom', 'phone_organizer', 'in_person_organizer'];
        if (in_array($driver, $customTypes)) {
            $fieldMaps = [
                'custom'              => 'description',
                'in_person_organizer' => 'description',
                'phone_organizer'     => 'host_phone_number'
            ];

            $key = $fieldMaps[$driver];

            return [
                'type'        => $driver,
                'description' => Arr::get($keyedLocations, $driver . '.' . $key)
            ];
        }

        if ($driver == 'online_meeting') {
            return [
                'type'                 => $driver,
                'online_platform_link' => Arr::get($keyedLocations, $driver . '.meeting_link')
            ];
        }

        return [
            'type'        => $driver,
            'description' => ''
        ];
    }

    public static function getLocationsConfig()
    {
        return [];
    }

    public static function getLocationOptions($calendarSlot)
    {
        $locationSettings = Arr::get($calendarSlot, 'location_settings');

       // dd($locationSettings);

        $locationOptions = [];
        foreach ($locationSettings as $location) {
            $title = Arr::get($location, 'title');
            $locationType = Arr::get($location, 'type');

            if ($locationType == 'custom') {
                if ($location['display_on_booking'] == 'yes') {
                    $title = Arr::get($location, 'description');
                } else {
                    $title = Arr::get($location, 'title');
                }
            }

            if (!$title) {
                $title = str_replace('_', ' ', ucfirst($locationType));
            }

            $locationOptions[] = [
                'type'  => Arr::get($location, 'type'),
                'title' => $title
            ];
        }
        return $locationOptions;
    }
}
