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

    <?php
        foreach ($css_files as $css_file) {
            wp_enqueue_style('fluent-booking-'.md5($css_file),esc_url($css_file),array(),FLUENT_BOOKING_ASSETS_VERSION,'all');
        }
    ?>
</head>
<body class="booking-confirmation-page">

    <div class="confirmation_page">
        <div class="fcal_conf_wrap">
            <?php echo wp_kses_post($body); ?>
        </div>
    </div>

    <script>
        <?php foreach ($js_vars as $varKey => $values): ?>
            var <?php echo esc_attr($varKey); ?> = <?php echo wp_json_encode($values); ?>;
        <?php endforeach; ?>
    </script>

    <?php
        foreach ($js_files as $handle => $url) {
            wp_enqueue_script(esc_attr($handle), esc_url($url), array(), FLUENT_BOOKING_ASSETS_VERSION, array('strategy'=>'defer'));
        }
    ?>

    <?php wp_footer(); ?>

    <script>
        const theme = '<?php echo esc_attr($theme); ?>';

        const confirmationPage = document.querySelector('.confirmation_page');
        function applyModeClasses(element, darkMode) {
            const darkClass  = 'fcal-dark-mode';
            const lightClass = 'fcal-light-mode';

            if (element) {
                if (darkMode) {
                    element.classList.add(darkClass);
                    element.classList.remove(lightClass);
                } else {
                    element.classList.add(lightClass);
                    element.classList.remove(darkClass);
                }
            }
        }

        if (confirmationPage) {
            if (theme === 'system-default') {
                const runColorMode = (fn) => {
                    if (!window.matchMedia) {
                        return;
                    }
                    const query = window.matchMedia('(prefers-color-scheme: dark)');
                    fn(query.matches);
                    query.addEventListener('change', (event) => fn(event.matches));
                };

                runColorMode((isDarkMode) => {
                    applyModeClasses(confirmationPage, isDarkMode);
                });
            } else if (theme === 'dark-mode') {
                applyModeClasses(confirmationPage, true);
            } else {
                applyModeClasses(confirmationPage, false);
            }
        }

    </script>
</body>
</html>
