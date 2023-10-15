<?php

use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\App\Models\Calendar;
use FluentBooking\App\Models\Booking;

/**
 * @var ?Booking $existing_booking
 * @var Calendar $calendar
 * @var CalendarSlot $calendar_event
 * @var array $author
 * @var array $css_files
 * @var array $js_files
 * @var array $js_vars
 * @var boolean $on_rescheduling
 * @var string $description
 * @var string $title
 * @var string $url
 */
?>
<!DOCTYPE html>
<html lang='en'>
<head>
    <title><?php echo esc_attr($title); ?></title>
    <meta charset='utf-8'>

    <meta content='width=device-width, initial-scale=1' name='viewport'>
    <meta content='yes' name='apple-mobile-web-app-capable'>
    <meta name="description" content="<?php echo esc_attr($description); ?>">
    <meta name="robots" content="noindex">

    <link rel="icon" type="image/x-icon" href="<?php echo $author['avatar']; ?>"/>

    <meta property="og:title" content="<?php echo esc_attr($title); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo esc_url($url); ?>">
    <meta property="og:site_name" content="<?php echo get_bloginfo('name'); ?>">
    <meta property="og:description" content="<?php echo esc_attr($description); ?>">
    <meta property="og:author" content="<?php echo $author['name']; ?>">

    <meta property="og:image" content=""/>

    <?php
    //    $feature_image = '';
    //    if ($author['featured_image']) {
    //        $feature_image = $author['featured_image'];
    //    } else {
    //        $feature_image = FLUENT_BOOKING_URL .'assets/images/default-featured.png';
    //    }
    ?>
    <meta property="og:image" content="<?php echo FLUENT_BOOKING_URL . 'assets/images/default-featured.png'; ?>"/>

    <?php foreach ($css_files as $css_file): ?>
        <link rel="stylesheet" href="<?php echo $css_file; ?>?version=<?php echo FLUENT_BOOKING_ASSETS_VERSION; ?>"
              media="screen"/>
    <?php endforeach; ?>
</head>
<body>


<div class="calendar_wrap">
    <?php do_action('fluent_booking/before_calendar_event_landing_page', $calendar_event); ?>
    <div class="fluent_booking_app fcal_loading" data-calendar_id="<?php echo (int)$calendar->id; ?>"
         data-event_id="<?php echo (int)$calendar_event->id; ?>">
        <h3>Loading...</h3>
    </div>
</div>
</div>

<script>
    <?php foreach ($js_vars as $varKey => $values): ?>
    var <?php echo $varKey; ?> = <?php echo json_encode($values); ?>;
    <?php endforeach; ?>
</script>

<?php foreach ($js_files as $file): ?>
    <script src="<?php echo $file; ?>?version=<?php echo FLUENT_BOOKING_ASSETS_VERSION; ?>" defer="defer"></script>
<?php endforeach; ?>
</body>
</html>
