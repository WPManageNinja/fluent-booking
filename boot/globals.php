<?php

use FluentCalendar\Framework\Support\Arr;

/**
 ***** DO NOT CALL ANY FUNCTIONS DIRECTLY FROM THIS FILE ******
 *
 * This file will be loaded even before the framework is loaded
 * so the $app is not available here, only declare functions here.
 */

if ($app->config->get('app.env') == 'dev') {

    $globalsDevFile = __DIR__ . '/globals_dev.php';
    
    is_readable($globalsDevFile) && include $globalsDevFile;
}

if (!function_exists('dd')) {
    function dd()
    {
        foreach (func_get_args() as $arg) {
            echo "<pre>";
            print_r($arg);
            echo "</pre>";
        }
        die();
    }
}

function fcal_sanitize_html($html)
{
    if (!$html) {
        return $html;
    }

    // Return $html if it's just a plain text
    if (!preg_match('/<[^>]*>/', $html)) {
        return $html;
    }

    $tags = wp_kses_allowed_html('post');
    $tags['style'] = [
        'types' => [],
    ];
    // iframe
    $tags['iframe'] = [
        'width'           => [],
        'height'          => [],
        'src'             => [],
        'srcdoc'          => [],
        'title'           => [],
        'frameborder'     => [],
        'allow'           => [],
        'class'           => [],
        'id'              => [],
        'allowfullscreen' => [],
        'style'           => [],
    ];
    //button
    $tags['button']['onclick'] = [];

    //svg
    if (empty($tags['svg'])) {
        $svg_args = [
            'svg' => [
                'class'           => true,
                'aria-hidden'     => true,
                'aria-labelledby' => true,
                'role'            => true,
                'xmlns'           => true,
                'width'           => true,
                'height'          => true,
                'viewbox'         => true,
            ],
            'g'     => ['fill' => true],
            'title' => ['title' => true],
            'path'  => [
                'd'         => true,
                'fill'      => true,
                'transform' => true,
            ],
        ];
        $tags = array_merge($tags, $svg_args);
    }

    $tags = apply_filters('fluent_calendar/allowed_html_tags', $tags);

    return wp_kses($html, $tags);
}

/**
 * Sanitize inputs recursively.
 *
 * @param array $input
 * @param array $sanitizeMap
 *
 * @return array $input
 */
function fcal_backend_sanitizer($inputs, $sanitizeMap = [])
{
    $originalValues = $inputs;
    foreach ($inputs as $key => &$value) {
        if (is_array($value)) {
            $value = fcal_backend_sanitizer($value, $sanitizeMap);
        } else {
            $method = Arr::get($sanitizeMap, $key);
            if (is_callable($method)) {
                $value = call_user_func($method, $value);
            }
        }
    }

    return apply_filters('fluent_calendar/backend_sanitized_values', $inputs, $originalValues);
}

