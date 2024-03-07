<?php defined( 'ABSPATH' ) || exit; ?>

<!DOCTYPE html>
<html lang='en'>
<head>
    <title><?php echo esc_attr($title); ?></title>
    <meta charset='utf-8'>

    <meta content='width=device-width, initial-scale=1' name='viewport'>
    <meta content='yes' name='apple-mobile-web-app-capable'>
    <meta name="description" content="<?php echo esc_attr($description); ?>">
    <meta name="robots" content="noindex"/>

    <?php if (!empty($author['avatar'])): ?>
        <link rel="icon" type="image/x-icon" href="<?php echo esc_url($author['avatar']); ?>">
    <?php endif; ?>

    <meta property="og:title" content="<?php echo esc_attr($title); ?>"/>
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo esc_url($url); ?>"/>
    <meta property="og:description" content="<?php echo esc_attr($description); ?>"/>
    <meta property="og:author" content="<?php echo esc_attr($author['name']); ?>"/>

    <?php if (!empty($author['featured_image'])) { ?>
        <meta property="og:image" content="<?php echo esc_url($author['featured_image']); ?>"/>
    <?php } ?>

    <style>
        :root {
            --fcal_dark: #1B2533;
            --fcal_primaryColor: #2653C7;
            --fcal_gray: #6b7280;
        }
        .fluent_booking_wrap {
            max-width: 752px;
            margin: 40px auto;
        }
        .fluent_booking_app {
            margin-top: 66px;
        }
        .fcal_author_header {
            max-width: 600px;
            margin: auto;
        }
        .fcal_slot {
            background: #fff;
        }
    </style>

    <?php
        foreach ($css_files as $css_file) {
            wp_enqueue_style('fluent-booking-'.md5($css_file),esc_url($css_file),array(),FLUENT_BOOKING_ASSETS_VERSION,'screen');
        }

        foreach ($header_js_files as $handle => $url) {
            wp_enqueue_script(esc_attr($handle), esc_url($url), array(), FLUENT_BOOKING_ASSETS_VERSION, false);
        }
    ?>

    <?php do_action('fluent_booking/main_landing'); ?>
</head>
<body>
    <?php \FluentBooking\App\App::getInstance('view')->render('landing.author_html', [
        'author' => $author,
        'calendar' => $calendar,
        'events' => $events
    ]); ?>

    <?php
        foreach ($js_vars as $varKey => $values) {
            wp_add_inline_script(esc_attr($handle), sprintf('var %s = %s;', esc_attr($varKey), wp_json_encode($values)));
        }

        foreach ($js_files as $handle => $url) {
            wp_enqueue_script(esc_attr($handle), esc_url($url), array(), FLUENT_BOOKING_ASSETS_VERSION, array('strategy'=>'defer'));
        }
    ?>

    <?php wp_footer(); ?>

</body>
</html>
