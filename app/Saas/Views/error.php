<!DOCTYPE html>
<html lang='en'>
<head>
    <title><?php echo esc_attr($title); ?></title>
    <meta charset='utf-8'>

    <meta content='width=device-width, initial-scale=1' name='viewport'>
    <meta content='yes' name='apple-mobile-web-app-capable'>
    <meta name="robots" content="noindex">

    <meta property="og:title" content="<?php echo esc_attr($title); ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="ConvertLeap">

    <?php foreach ($css_files as $css_file): ?>
        <link rel="stylesheet" href="<?php echo $css_file; ?>" media="screen" />
    <?php endforeach; ?>
</head>
<body>

<div class="calendar_wrap calendar_wrap_error">
    <div class="fcal_cal_wrap">
        <div style="text-align: center; padding: 40px;" class="fluent_calendar_app">
            <h1 style="font-size: 24px; margin: 15px 0;"><?php echo esc_attr($title) ?></h1>
            <div>
                <?php echo $description; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>
