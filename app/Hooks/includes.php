<?php

/*
 * Require any extra files here. For example::
 * require_once "shortcodes.php";
 * require_once "crontasks.php";
 */

/**
 * @var $app FluentBooking\Framework\Foundation\Application
 */

if (defined('FLUENT_BOOKING_PRO_DIR_FILE')) {
    require_once FLUENT_BOOKING_DIR . 'app/Services/Integrations/pro_integrations.php';
}
