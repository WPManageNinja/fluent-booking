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
        add_action('wp_head', function () use ($attributes) {
            ?>
            <style>
                .fcal_calendar_inner .fcal_side .fcal_author_avatar img {
                    border-radius: 50% !important;
                }
                :root {
                    --fcal_primary_color: <?php echo esc_attr($attributes['primary_color']); ?> !important;
                    --fcal_date_radius: <?php echo esc_attr($attributes['date_round']); ?> !important;
                    --fcal_avatar_radius: <?php echo esc_attr($attributes['avatarStyle']); ?> !important;
                }
            </style>
            <?php
        });
        $slotId = $attributes['slotId'];
        return do_shortcode("[fluent_booking id=$slotId]");
    }
}
