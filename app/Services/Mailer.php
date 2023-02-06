<?php

namespace FluentCalendar\App\Services;

class Mailer
{
    public static function send($to, $subject, $body, $fromName = '')
    {
        $headers = array('Content-Type: text/html; charset=UTF-8');
        if ($fromName) {
            $headers[] = 'From: ' . $fromName . ' <' . get_option('admin_email') . '>';
        }

        return wp_mail($to, $subject, $body, $headers);
    }
}
