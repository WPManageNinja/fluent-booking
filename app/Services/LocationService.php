<?php

namespace FluentBooking\App\Services;

use FluentBooking\App\App;
use FluentBooking\Framework\Support\Arr;

class LocationService
{
    public static function getLocationIconHeadingHtml($driver, $details = [], $calendarEvent = null)
    {
        $html = '';
        $app = App::getInstance();
        if ($driver == 'google_meet') {
            $html = '<div class="slot_location fcal_icon_item fcal_img_item fcal_loc_google_meet">';
            $html .= '<img class="fcal_loc_icon" src="' . $app['url.assets'] . 'images/google-meet.svg" alt="Google Meet" />';
            $html .= '<span class="fcal_loc_text">' . __('Google Meet', 'fluent-booking') . '</span>';
            $html .= '</div>';
        } else if($driver == 'zoom_meeting') {
            $html = '<div class="slot_location fcal_icon_item fcal_img_item fcal_loc_zoom_meeting">';
            $html .= '<img class="fcal_loc_icon" src="' . $app['url.assets'] . 'images/zoom.svg" alt="Zoom Icon" />';
            $html .= '<span class="fcal_loc_text">' . __('Zoom Video', 'fluent-booking') . '</span>';
            $html .= '</div>';
        } else if($driver == 'in_person_guest') {
            $html = '<div class="slot_location fcal_icon_item fcal_img_item fcal_loc_in_person_guest">';
            $html .= '<img class="fcal_loc_icon" src="' . $app['url.assets'] . 'images/physical_location.svg" alt="Zoom Icon" />';
            $html .= '<span class="fcal_loc_text">' . __('Your Provided Address', 'fluent-booking') . '</span>';
            $html .= '</div>';
        } else if($driver == 'in_person_organizer' || $driver == 'custom') {
            $html = '<div class="slot_location fcal_icon_item fcal_img_item fcal_loc_'.esc_attr($driver).'">';
            $html .= '<img class="fcal_loc_icon" src="' . $app['url.assets'] . 'images/physical_location.svg" alt="Zoom Icon" />';
            $html .= '<span class="fcal_loc_text">' . esc_html($details['title']) . '</span>';
            $html .= '</div>';
            return $html;
        } else if($driver == 'phone_guest') {
            $html = '<div class="slot_location fcal_icon_item fcal_img_item fcal_loc_phone_guest">';
            $html .= '<img class="fcal_loc_icon" src="' . $app['url.assets'] . 'images/phone_call.svg" alt="Phone" />';
            $html .= '<span class="fcal_loc_text">' . __('Phone Call', 'fluent-booking') . '</span>';
            $html .= '</div>';
            return $html;
        } else if($driver == 'phone_organizer') {
            $html = '<div class="slot_location fcal_icon_item fcal_img_item fcal_loc_phone_organizer">';
            $html .= '<img class="fcal_loc_icon" src="' . $app['url.assets'] . 'images/phone_call.svg" alt="Phone" />';
            $html .= '<span class="fcal_loc_text">' . __('Phone Call', 'fluent-booking') . '</span>';
            $html .= '</div>';
            return $html;
        }

        return apply_filters('fluent_booking/location_icon_heading_html', $html, $driver, $details, $calendarEvent);

    }

    public static function getLocationDetails($locations, $address = '')
    {
        $locationType = Arr::get($locations, '0.type');

        $locationData['type'] = $locationType;

        if ($locationType == 'in_person_organizer' || $locationType == 'custom') {
            $locationData['title'] = Arr::get($locations, '0.title');
            $locationData['description'] = Arr::get($locations, '0.description');
        } elseif ($locationType == 'phone_organizer') {
            $locationData['host_phone_number'] = Arr::get($locations, '0.host_phone_number');
        } elseif ($locationType == 'in_person_guest') {
            $locationData['guest_address'] = $address;
        }

        return $locationData;
    }
}
