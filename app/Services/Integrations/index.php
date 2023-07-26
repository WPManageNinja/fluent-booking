<?php

add_action('init', function () {

// Load Integrations
    require_once FLUENT_CALENDAR_DIR . 'app/Services/Integrations/FluentForms/init.php';
});
