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
                $html .= '<img class="fcal_loc_icon" src="' . $app['url.assets'] . 'images/physical_location.svg" alt="Zoom Icon" />';
                $html .= '<span class="fcal_loc_text">' . __('In Person (Attendee Address)', 'fluent-booking-pro') . '</span>';
            } else if ($location['type'] == 'custom') {
                $html .= '<img class="fcal_loc_icon" src="' . $app['url.assets'] . 'images/physical_location.svg" alt="Zoom Icon" />';
                if ($displayOnBooking == 'yes') {
                    $html .= '<span class="fcal_loc_text">' . $location['description'] . '</span>';
                } else {
                    $html .= '<span class="fcal_loc_text">' . $location['title'] . '</span>';
                }
            } else if ($location['type'] == 'in_person_organizer') {
                $html .= '<img class="fcal_loc_icon" src="' . $app['url.assets'] . 'images/physical_location.svg" alt="Zoom Icon" />';
                if ($displayOnBooking == 'yes') {
                    $html .= '<span class="fcal_loc_text">' . $location['description'] . '</span>';
                } else {
                    $html .= '<span class="fcal_loc_text"> In Person (Organizer Address) </span>';
                }
            } else if ($location['type'] == 'phone_guest') {
                $html .= '<img class="fcal_loc_icon" src="' . $app['url.assets'] . 'images/phone_call.svg" alt="Phone" />';
                $html .= '<span class="fcal_loc_text">' . __('Attendee Phone Number', 'fluent-booking-pro') . '</span>';
            } else if ($location['type'] == 'phone_organizer') {
                $html .= '<img class="fcal_loc_icon" src="' . $app['url.assets'] . 'images/phone_call.svg" alt="Phone" />';

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

    public static function updateSingleLocationDetails($locationFields, $address)
    {
        $locationType = Arr::get($locationFields, '0.type');

        $locationData['type'] = $locationType;

        if ($$locationType == 'custom') {
            $locationData['title'] = Arr::get($locationFields, '0.custom_title');
            $locationData['description'] = Arr::get($locationFields, '0.description');
        } else if ($locationType == 'in_person_organizer') {
            $locationData['title'] = Arr::get($locationFields, '0.title');
            $locationData['description'] = Arr::get($locationFields, '0.description');
        } elseif ($locationType == 'phone_organizer') {
            $locationData['title'] = Arr::get($locationFields, '0.title');
            $locationData['host_phone_number'] = Arr::get($locationFields, '0.host_phone_number');
        } elseif ($locationType == 'in_person_guest') {
            $locationData['title'] = Arr::get($locationFields, '0.title');
            $locationData['guest_address'] = $address;
        } elseif ($locationType == 'online_meeting') {
            $locationData['title'] = Arr::get($locationFields, '0.title');
            $locationData['meeting_link'] = Arr::get($locationFields, '0.meeting_link');
        }
        return $locationData;
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

    public static function updateMultipleLocationDetails($locationFields, $location, $details)
    {
        $locationData['type'] = $location;

        $locationDetails = array_filter($locationFields, function ($field) {
            return $field['type'] == $location;
        });

        $locationDetails = reset($filteredArray);

        if ($location == 'in_person_organizer') {
            $locationData['description'] = Arr::get($locationDetails, 'description');
        } elseif ($location == 'in_person_guest') {
            $locationData['guest_address'] = $details;
        } elseif ($location == 'phone_organizer') {
            $locationData['host_phone_number'] = Arr::get($locationDetails, 'host_phone_number');
        } elseif ($location == 'phone_guest') {
            $locationData['guest_address'] = $details;
        } elseif ($location == 'online_meeting') {
            $locationData['meeting_link'] = Arr::get($locationDetails, 'meeting_link');
        } elseif ($location == 'custom') {
            $locationData['description'] = $details;
        }
        return $locationData;
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
        return [
            ''
        ];
    }

    public static function getLocationOptions($calendarSlot)
    {
        $locationSettings = Arr::get($calendarSlot, 'location_settings');

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

            $locationOptions[] = [
                'type'  => Arr::get($location, 'type'),
                'title' => $title
            ];
        }
        return $locationOptions;
    }
}
