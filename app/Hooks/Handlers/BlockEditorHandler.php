<?php
namespace FluentBooking\App\Hooks\Handlers;
use FluentBooking\App\App;

class BlockEditorHandler
{
    public function init()
    {
        add_action('enqueue_block_editor_assets', function () {
            $app    = App::getInstance();
            $assets = $app['url.assets'];
    
            wp_enqueue_script(
                'fluent-booking/calendar',
                $assets . 'admin/fluent-booking-index.js',
                array('wp-blocks', 'wp-components', 'wp-block-editor', 'wp-element'),
                FLUENT_BOOKING_ASSETS_VERSION,
                true
            );
    
            wp_localize_script('fluent-booking/calendar', 'fluent_booking_block', [
                'assets_url' => $assets
            ]);
        });
        
        register_block_type( 'fluent-booking/calendar' , array(
            'editor_script'   => 'fluent-booking/calendar',
            'render_callback' => array($this, 'fcal_render_block'),
            'attributes'      => [
                'slotId' => [
                    'type'    => 'string',
                    'default' => '',
                ],
                'calendarId' => [
                    'type'    => 'string',
                    'default' => '',
                ],
                'avatar_rounded' => [
                    'type'      => 'boolean',
                    'default'   => false
                ],
                'primary_color' => [
                    'type'      => 'string',
                    'default'   => '#4587EC'
                ],
                'date_round' => [
                    'type'      => 'string',
                    'default'   => '4px'
                ],
                'avatarStyle' => [
                    'type'      => 'string',
                    'default'   => '8px'
                ]
            ]
        ));
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
        $output .= do_shortcode("[fluent_booking id=$slotId]");
        return $output;
    }
}
