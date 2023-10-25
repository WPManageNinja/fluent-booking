<!DOCTYPE html>
<html lang='en'>
<head>
    <title><?php echo esc_attr($title); ?></title>
    <meta charset='utf-8'>

    <meta content='width=device-width, initial-scale=1' name='viewport'>
    <meta content='yes' name='apple-mobile-web-app-capable'>
    <meta name="description" content="<?php echo esc_attr($description); ?>">
    <meta name="robots" content="noindex">

    <link rel="icon" type="image/x-icon" href="<?php echo esc_url($author['avatar']); ?>" />

    <meta property="og:title" content="<?php echo esc_attr($title); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo esc_url($url); ?>">
    <meta property="og:description" content="<?php echo esc_attr($description); ?>">
    <meta property="og:author" content="<?php echo esc_attr($author['name']); ?>">

    <?php foreach ($css_files as $css_file): ?>
    <link rel="stylesheet" href="<?php echo esc_url($css_file); ?>?version=<?php echo esc_attr(FLUENT_BOOKING_ASSETS_VERSION); ?>" media="all" />
    <?php endforeach; ?>
</head>
<body>

<div class="confirmation_page">
    <div class="fcal_conf_wrap">
        <?php echo $body; ?>
    </div>
</div>

<script>
    <?php foreach ($js_vars as $varKey => $values): ?>
    var <?php echo esc_attr($varKey); ?> = <?php echo wp_json_encode($values); ?>;
    <?php endforeach; ?>
</script>

<?php foreach ($js_files as $file): ?>
    <script src="<?php echo esc_url($file); ?>" defer="defer"></script>
<?php endforeach; ?>
</body>
</html>
