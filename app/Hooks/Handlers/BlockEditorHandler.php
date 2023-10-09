<?php
namespace FluentBooking\App\Hooks\Handlers;
use FluentBooking\App\App;

class BlockEditorHandler
{
    public function init()
    {
        $app    = App::getInstance();
        $assets = $app['url.assets'];
        $slug   = $app->config->get('app.slug');

        wp_enqueue_script(
            'fluent-booking/calendar',
            $assets . 'admin/fluent-booking-index.js',
            array('wp-blocks', 'wp-components', 'wp-block-editor', 'wp-element')
        );

        wp_localize_script('fluent-booking/calendar', 'fluent_booking_block', [
            'assets_url' => $assets
        ]);

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
                ]
            ]
        ));
    }

    public function fcal_render_block($attributes)
    {
        $slotId = $attributes['slotId'];
        return do_shortcode("[fluent_booking id=$slotId]");
    }
}
