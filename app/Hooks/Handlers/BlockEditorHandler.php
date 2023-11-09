<?php

namespace FluentBooking\App\Hooks\Handlers;

use FluentBooking\App\App;
use FluentBooking\App\Models\Calendar;
use FluentBooking\App\Services\Helper;
use FluentBooking\Framework\Support\Arr;

class BlockEditorHandler
{
    public function init()
    {
        add_action('enqueue_block_editor_assets', function () {
            $app = App::getInstance();
            $assets = $app['url.assets'];

            wp_enqueue_script(
                'fluent-booking/calendar',
                $assets . 'admin/fluent-booking-index.js',
                array('wp-blocks', 'wp-components', 'wp-block-editor', 'wp-element'),
                FLUENT_BOOKING_ASSETS_VERSION,
                true
            );
            wp_enqueue_script(
                'fluent-booking/team-management',
                $assets . 'admin/fluent-booking-team-management-index.js',
                array('wp-blocks', 'wp-components', 'wp-block-editor', 'wp-element'),
                FLUENT_BOOKING_ASSETS_VERSION,
                true
            );

            $calendars = Calendar::with(['events' => function ($query) {
                $query->where('status', 'active');
            }])
                ->get();

            $formattedCalendars = [];

            foreach ($calendars as $calendar) {
                $events = $calendar->events;
                if ($events->isEmpty()) {
                    continue;
                }

                $formattedEvents = [];

                foreach ($calendar->events as $event) {
                    $formattedEvents[] = [
                        'id'    => (string)$event->id,
                        'title' => $event->title
                    ];
                }

                $formattedCalendars[$calendar->id] = [
                    'id'          => (string)$calendar->id,
                    'title'       => $calendar->title,
                    'description' => wpautop(Helper::excerpt($calendar->description, 200)),
                    'author'      => $calendar->getAuthorProfile(),
                    'events'      => $formattedEvents
                ];
            }

            wp_localize_script('fluent-booking/calendar', 'fluent_booking_block', [
                'assets_url' => $assets,
                'hosts'      => $formattedCalendars
            ]);
        });

        register_block_type('fluent-booking/calendar', array(
            'editor_script'   => 'fluent-booking/calendar',
            'render_callback' => array($this, 'fcal_render_block'),
            'attributes'      => [
                'slotId'         => [
                    'type'    => 'string',
                    'default' => '',
                ],
                'calendarId'     => [
                    'type'    => 'string',
                    'default' => '',
                ],
                'avatar_rounded' => [
                    'type'    => 'boolean',
                    'default' => false
                ],
                'primary_color'  => [
                    'type'    => 'string',
                    'default' => '#4587EC'
                ],
                'date_round'     => [
                    'type'    => 'string',
                    'default' => '4px'
                ],
                'avatarStyle'    => [
                    'type'    => 'string',
                    'default' => '8px'
                ],
                'hideHostInfo'   => [
                    'type'    => 'string',
                    'default' => 'no'
                ]
            ]
        ));

        register_block_type('fluent-booking/team-management', array(
            'editor_script'   => 'fluent-booking/team-management',
            'render_callback' => array($this, 'fcal_render_team_management_block'),
            'attributes'      => array(
                'title'       => array(
                    'type'    => 'string',
                    'default' => 'FluentBooking Team'
                ),
                'description' => array(
                    'type'    => 'string',
                    'default' => ''
                ),
                'headerImage' => array(
                    'type'    => 'object',
                    'default' => ''
                ),
                'hosts'       => array(
                    'type'    => 'object',
                    'default' => ''
                )
            )
        ));
    }

    public function fcal_render_team_management_block($attributes)
    {

        return 'Hello';

        $hosts = [
            [
                'id' => 1,
                'event_ids' => ['all']
            ],
            [
                'id' => 2,
                'event_ids' => ['6', '9']
            ]
        ];

        return 'OK';
        $hostsConfig = Arr::get($attributes, 'hosts');

        foreach ($hostsConfig as $hostId => $config) {

        }


        dd($attributes);
    }

    public function fcal_render_block($attributes)
    {
        $output = '<style>
            :root {
                --fcal_primary_color: ' . esc_attr($attributes['primary_color']) . ' !important;
                --fcal_date_radius: ' . esc_attr($attributes['date_round']) . ' !important;
                --fcal_avatar_radius: ' . esc_attr($attributes['avatarStyle']) . ' !important;
            }
        </style>';

        $slotId = $attributes['slotId'];
        $disableHost = $attributes['hideHostInfo'];
        $output .= do_shortcode("[fluent_booking id=$slotId disable_author=$disableHost]");
        return $output;
    }
}
