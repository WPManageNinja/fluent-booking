<!DOCTYPE html>
<html lang='en'>
<head>
    <title><?php echo esc_attr($title); ?></title>
    <meta charset='utf-8'>

    <meta content='width=device-width, initial-scale=1' name='viewport'>
    <meta content='yes' name='apple-mobile-web-app-capable'>
    <meta name="description" content="<?php echo esc_attr($description); ?>">
    <meta name="robots" content="noindex">

    <link rel="icon" type="image/x-icon" href="<?php echo $author['avatar']; ?>" />

    <meta property="og:title" content="<?php echo esc_attr($title); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo esc_url($url); ?>">
    <meta property="og:site_name" content="ConvertLeap">
    <meta property="og:description" content="<?php echo esc_attr($description); ?>">
    <meta property="og:author" content="<?php echo $author['name']; ?>">

    <meta property="og:image" content="" />

    <meta property="og:image" content="<?php echo FLUENT_CALENDAR_URL; ?>assets/images/default-featured.png" />

    <?php foreach ($css_files as $css_file): ?>
    <link rel="stylesheet" href="<?php echo $css_file; ?>" media="screen" />
    <?php endforeach; ?>
</head>
<body>

<div class="calendar_wrap">
    <div class="fcal_cal_wrap">
        <div class="fluent_calendar_app" data-calendar_id="<?php echo (int) $calendar->id; ?>" data-slot_id="<?php echo (int) $slot->id; ?>">
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
    <script src="<?php echo $file; ?>" defer="defer"></script>
<?php endforeach; ?>
</body>
</html>
