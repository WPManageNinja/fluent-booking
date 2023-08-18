<?php

namespace FluentCalendar\App\Services;

use FluentCalendar\App\Models\Calendar;
use FluentCalendar\App\Models\CalendarSlot;
use FluentCalendar\App\Models\Meta;
use FluentCalendar\Framework\Support\Arr;

class Helper
{
    public static function isCalendarSlugAvailable($slug, $checkDb = true, $exceptId = false)
    {
        $reserved = [
            '0',
            'about',
            'access',
            'account',
            'accounts',
            'activate',
            'activities',
            'activity',
            'ad',
            'add',
            'address',
            'adm',
            'admin',
            'administration',
            'administrator',
            'ads',
            'adult',
            'advertising',
            'affiliate',
            'affiliates',
            'ajax',
            'all',
            'alpha',
            'analysis',
            'analytics',
            'android',
            'anon',
            'anonymous',
            'api',
            'app',
            'apps',
            'archive',
            'archives',
            'article',
            'asct',
            'asset',
            'atom',
            'auth',
            'authentication',
            'avatar',
            'backup',
            'balancer-manager',
            'banner',
            'banners',
            'beta',
            'billing',
            'bin',
            'blog',
            'blogs',
            'board',
            'book',
            'bookmark',
            'bot',
            'bots',
            'bug',
            'business',
            'cache',
            'cadastro',
            'calendar',
            'call',
            'campaign',
            'cancel',
            'captcha',
            'career',
            'careers',
            'cart',
            'categories',
            'category',
            'cgi',
            'cgi-bin',
            'changelog',
            'chat',
            'check',
            'checking',
            'checkout',
            'client',
            'cliente',
            'clients',
            'code',
            'codereview',
            'comercial',
            'comment',
            'comments',
            'communities',
            'community',
            'company',
            'compare',
            'compras',
            'config',
            'configuration',
            'connect',
            'contact',
            'contact-us',
            'contact_us',
            'contactus',
            'contest',
            'contribute',
            'corp',
            'create',
            'css',
            'dashboard',
            'data',
            'db',
            'default',
            'delete',
            'demo',
            'design',
            'designer',
            'destroy',
            'dev',
            'devel',
            'developer',
            'developers',
            'diagram',
            'diary',
            'dict',
            'dictionary',
            'die',
            'dir',
            'direct_messages',
            'directory',
            'dist',
            'doc',
            'docs',
            'documentation',
            'domain',
            'download',
            'downloads',
            'ecommerce',
            'edit',
            'editor',
            'edu',
            'education',
            'email',
            'employment',
            'empty',
            'end',
            'enterprise',
            'entries',
            'entry',
            'error',
            'errors',
            'eval',
            'event',
            'exit',
            'explore',
            'facebook',
            'faq',
            'favorite',
            'favorites',
            'feature',
            'features',
            'feed',
            'feedback',
            'feeds',
            'file',
            'files',
            'first',
            'flash',
            'fleet',
            'fleets',
            'flog',
            'follow',
            'followers',
            'following',
            'forgot',
            'form',
            'forum',
            'forums',
            'founder',
            'free',
            'friend',
            'friends',
            'ftp',
            'gadget',
            'gadgets',
            'game',
            'games',
            'get',
            'ghost',
            'gift',
            'gifts',
            'gist',
            'github',
            'graph',
            'group',
            'groups',
            'guest',
            'guests',
            'help',
            'home',
            'homepage',
            'host',
            'hosting',
            'hostmaster',
            'hostname',
            'howto',
            'hpg',
            'html',
            'http',
            'httpd',
            'https',
            'i',
            'iamges',
            'icon',
            'icons',
            'id',
            'idea',
            'ideas',
            'image',
            'images',
            'imap',
            'img',
            'index',
            'indice',
            'info',
            'information',
            'inquiry',
            'instagram',
            'intranet',
            'invitations',
            'invite',
            'ipad',
            'iphone',
            'irc',
            'is',
            'issue',
            'issues',
            'it',
            'item',
            'items',
            'java',
            'javascript',
            'job',
            'jobs',
            'join',
            'js',
            'json',
            'jump',
            'knowledgebase',
            'language',
            'languages',
            'last',
            'ldap-status',
            'legal',
            'license',
            'link',
            'links',
            'linux',
            'list',
            'lists',
            'log',
            'log-in',
            'log-out',
            'log_in',
            'log_out',
            'login',
            'logout',
            'logs',
            'm',
            'mac',
            'mail',
            'mail1',
            'mail2',
            'mail3',
            'mail4',
            'mail5',
            'mailer',
            'mailing',
            'maintenance',
            'manager',
            'manual',
            'map',
            'maps',
            'marketing',
            'master',
            'me',
            'media',
            'member',
            'members',
            'message',
            'messages',
            'messenger',
            'microblog',
            'microblogs',
            'mine',
            'mis',
            'mob',
            'mobile',
            'movie',
            'movies',
            'mp3',
            'msg',
            'msn',
            'music',
            'musicas',
            'mx',
            'my',
            'mysql',
            'name',
            'named',
            'nan',
            'navi',
            'navigation',
            'net',
            'network',
            'new',
            'news',
            'newsletter',
            'nick',
            'nickname',
            'notes',
            'noticias',
            'notification',
            'notifications',
            'notify',
            'ns',
            'ns1',
            'ns10',
            'ns2',
            'ns3',
            'ns4',
            'ns5',
            'ns6',
            'ns7',
            'ns8',
            'ns9',
            'null',
            'oauth',
            'oauth_clients',
            'offer',
            'offers',
            'official',
            'old',
            'online',
            'openid',
            'operator',
            'order',
            'orders',
            'organization',
            'organizations',
            'overview',
            'owner',
            'owners',
            'page',
            'pager',
            'pages',
            'panel',
            'password',
            'payment',
            'perl',
            'phone',
            'photo',
            'photoalbum',
            'photos',
            'php',
            'phpmyadmin',
            'phppgadmin',
            'phpredisadmin',
            'pic',
            'pics',
            'ping',
            'plan',
            'plans',
            'plugin',
            'plugins',
            'policy',
            'pop',
            'pop3',
            'popular',
            'portal',
            'post',
            'postfix',
            'postmaster',
            'posts',
            'pr',
            'premium',
            'press',
            'price',
            'pricing',
            'privacy',
            'privacy-policy',
            'privacy_policy',
            'privacypolicy',
            'private',
            'product',
            'products',
            'profile',
            'project',
            'projects',
            'promo',
            'pub',
            'public',
            'purpose',
            'put',
            'python',
            'query',
            'random',
            'ranking',
            'read',
            'readme',
            'recent',
            'recruit',
            'recruitment',
            'register',
            'registration',
            'release',
            'remove',
            'replies',
            'report',
            'reports',
            'repositories',
            'repository',
            'req',
            'request',
            'requests',
            'reset',
            'roc',
            'root',
            'rss',
            'ruby',
            'rule',
            'sag',
            'sale',
            'sales',
            'sample',
            'samples',
            'save',
            'school',
            'script',
            'scripts',
            'search',
            'secure',
            'security',
            'self',
            'send',
            'server',
            'server-info',
            'server-status',
            'service',
            'services',
            'session',
            'sessions',
            'setting',
            'settings',
            'setup',
            'share',
            'shop',
            'show',
            'sign-in',
            'sign-up',
            'sign_in',
            'sign_up',
            'signin',
            'signout',
            'signup',
            'site',
            'sitemap',
            'sites',
            'smartphone',
            'smtp',
            'soporte',
            'source',
            'spec',
            'special',
            'sql',
            'src',
            'ssh',
            'ssl',
            'ssladmin',
            'ssladministrator',
            'sslwebmaster',
            'staff',
            'stage',
            'staging',
            'start',
            'stat',
            'state',
            'static',
            'stats',
            'status',
            'store',
            'stores',
            'stories',
            'style',
            'styleguide',
            'stylesheet',
            'stylesheets',
            'subdomain',
            'subscribe',
            'subscriptions',
            'suporte',
            'support',
            'svn',
            'swf',
            'sys',
            'sysadmin',
            'sysadministrator',
            'system',
            'tablet',
            'tablets',
            'tag',
            'talk',
            'task',
            'tasks',
            'team',
            'teams',
            'tech',
            'telnet',
            'term',
            'terms',
            'terms-of-service',
            'terms_of_service',
            'termsofservice',
            'test',
            'test1',
            'test2',
            'test3',
            'teste',
            'testing',
            'tests',
            'theme',
            'themes',
            'thread',
            'threads',
            'tmp',
            'todo',
            'tool',
            'tools',
            'top',
            'topic',
            'topics',
            'tos',
            'tour',
            'translations',
            'trends',
            'tutorial',
            'tux',
            'tv',
            'twitter',
            'undef',
            'unfollow',
            'unsubscribe',
            'update',
            'upload',
            'uploads',
            'url',
            'usage',
            'user',
            'username',
            'users',
            'usuario',
            'vendas',
            'ver',
            'version',
            'video',
            'videos',
            'visitor',
            'watch',
            'weather',
            'web',
            'webhook',
            'webhooks',
            'webmail',
            'webmaster',
            'website',
            'websites',
            'welcome',
            'widget',
            'widgets',
            'wiki',
            'win',
            'windows',
            'word',
            'work',
            'works',
            'workshop',
            'ww',
            'wws',
            'www',
            'www1',
            'www2',
            'www3',
            'www4',
            'www5',
            'www6',
            'www7',
            'wwws',
            'wwww',
            'xfn',
            'xml',
            'xmpp',
            'xpg',
            'xxx',
            'yaml',
            'year',
            'yml',
            'you',
            'yourdomain',
            'yourname',
            'yoursite',
            'yourusername'
        ];

        if (in_array($slug, $reserved)) {
            return false;
        }

        if (strlen($slug) < 4) {
            return false;
        }

        if ($checkDb) {

            if ($exceptId) {
                $exist = Calendar::where('slug', $slug)->where('id', '!=', $exceptId)->first();
            } else {
                $exist = Calendar::where('slug', $slug)->first();
            }

            if ($exist) {
                return false;
            }
        }


        if (is_numeric($slug)) {
            return false;
        }

        // check if $slug has any special characters or any space. We will only allow alpha-numeric values
        return preg_match('/^[a-zA-Z0-9_-]+$/', $slug);
    }

    public static function getAppBaseUrl($extension = '')
    {
        return apply_filters('fluent_calendar/admin_base_url', admin_url('admin.php?page=fluent-calendar#/' . $extension), $extension);
    }

    public static function getMeta($group, $objectId, $key, $withModel = false)
    {
        $meta = Meta::where('object_type', $group)
            ->where('object_id', $objectId)
            ->where('key', $key)
            ->first();

        if ($meta) {
            if ($withModel) {
                return $meta;
            }

            return $meta->value;
        }

        return null;
    }

    public static function updateMeta($group, $objectId, $key, $value)
    {
        $meta = self::getMeta($group, $objectId, $key, true);

        if ($meta) {
            $meta->value = $value;
            $meta->save();
            return $meta;
        }

        return Meta::create([
            'object_type' => $group,
            'key'         => $key,
            'object_id'   => $objectId,
            'value'       => $value
        ]);
    }

    public static function deleteMeta($group, $objectId, $key)
    {
        return Meta::where('object_type', $group)
            ->where('object_id', $objectId)
            ->where('key', $key)
            ->delete();
    }

    public static function getUserDisplayName($userId = null)
    {
        if (!$userId) {
            $userId = get_current_user_id();
        }

        if (!$userId) {
            return '';
        }

        $user = get_user_by('ID', $userId);


        $name = trim($user->first_name . ' ' . $user->last_name);

        if ($name) {
            return $name;
        }

        return $user->display_name;
    }

    public static function excerpt($text, $max_length = 160)
    {
        // Strip HTML tags and convert entities to their corresponding characters
        $text = html_entity_decode(strip_tags($text));

        // Remove any line breaks, tabs, or extra whitespace
        $text = preg_replace('/\s+/', ' ', trim($text));

        if (mb_strlen($text) > $max_length) {
            $text = mb_substr($text, 0, $max_length);
            $text = preg_replace('/\s+\S+$/', '', $text) . '...';
        }

        return $text;
    }

    public static function generateSlotSlug($default, $calendar)
    {
        $original = sanitize_title($default, $default, 'display');

        $default = $original;

        $counter = 1;

        while (CalendarSlot::where('calendar_id', $calendar->id)->where('slug', $default)->first()) {
            $default = $original . '-' . $counter;
        }

        return apply_filters('fluent_calendar/slot_slug', $default, $original);
    }

    public static function getIp()
    {
        $server = $_SERVER;
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }

        return sanitize_text_field($ip);
    }

    public static function fcal_sanitize_html($html)
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
    public static function fcal_backend_sanitizer($inputs, $sanitizeMap = [])
    {
        $originalValues = $inputs;
        foreach ($inputs as $key => &$value) {
            if (is_array($value)) {
                $value = self::fcal_backend_sanitizer($value, $sanitizeMap);
            } else {
                $method = Arr::get($sanitizeMap, $key);
                if (is_callable($method)) {
                    $value = call_user_func($method, $value);
                } elseif (method_exists(self::class, $method)) {
                    $value = call_user_func([self::class, $method], $value);
                }
            }
        }

        return apply_filters('fluent_calendar/backend_sanitized_values', $inputs, $originalValues);
    }

    public static function getEventTypesSchema()
    {
        return apply_filters('fluent_calendar/event_types_schema', [
            'single' => [
                'title'     => 'One-on-One', 
                'subtitle'  => 'Meeting with a single person'
            ],
            'group' => [
                'title'     => 'Group Meeting',
                'subtitle'  => 'Meeting with multiple guests'
            ]
        ]);
    }

    public static function getDefaultNotificationSettings()
    {
        $defaults = apply_filters('fluent_calendar/default_notification_settings', [
            'booking_conf_attendee' => [
                'enabled' => true,
                'title'   => 'Booking Confirmation to Attendee',
                'email'   => [
                    'subject' => 'Booking Confirmation with {host.name} {event.datetime}',
                    'body'    => '<h2 class="p1" style="text-align: center;">Booking Confirmation</h2><h3><strong>Event Name</strong></h3><p>{event.name} with {host.name}</p><h3><strong>When</strong></h3><p>{event.full_datetime}, ({guest.timezone})</p><h3><strong>Location</strong></h3><ul><li>{event.location}</li></ul><h3><strong>Your Note</strong></h3><p>{guest.notes}</p><h3><strong>Guests</strong></h3><ul><li>{host.email} - host</li><li>{guest.email} - you</li></ul>'
                ],
            ],
            'booking_conf_host' => [
                'enabled' => true,
                'title'   => 'Booking Confirmation to Organizer (You)',
                'email'   => [
                    'subject' => 'New Booking: {guest.first_name} {guest.last_name} @ {event.datetime} ({guest.email})',
                    'body'    => '<h2 class="p1" style="text-align: center;">New Booking Confirmed</h2><h3><strong>Event Name</strong></h3><p>{event.name} with {guest.full_name}</p><h3><strong>Guest Details</strong></h3><ul><li><strong>Name</strong>: {guest.full_name}</li><li><strong>Email</strong>: {guest.email}</li></ul><h3><strong>When</strong></h3><p>{event.full_datetime}, ({host.timezone})</p><h3><strong>Location</strong></h3><ul><li>{event.location}</li></ul><h3><strong>Guests</strong></h3><ul><li>{host.email} - host</li><li>{guest.email} - guest</li></ul>'
                ],
            ],
            'reminder_to_attendee' => [
                'enabled' => true,
                'title'   => 'Reminder Before Meeting to Attendee',
                'email'   => [
                    'subject' => 'Meeting Reminder: {host.name} {event.datetime}',
                    'body'    => '<div><h2 style="text-align: center;">Reminder: Meeting will start in {event.reminder_time}</h2></div><h3><strong>Event Name</strong></h3><p>{event.name} with {host.name}</p><h3><strong>When</strong></h3><p>{event.full_datetime}, ({host.timezone})</p><h3><strong>Location</strong></h3><ul><li>{event.location}</li></ul><h3><strong>Your Note</strong></h3><p>{guest.notes}</p><h3><strong>Guests</strong></h3><ul><li>{host.email} - host</li><li>{guest.email} - you</li></ul>',
                    'times'   => [
                        [
                            'unit' => 'minutes',
                            'value'=> 15,
                        ]
                    ]
                ],
            ],
            'reminder_to_host' => [
                'enabled' => true,
                'title'   => 'Reminder Before Meeting to Organizer (You)',
                'email'   => [
                    'subject' => 'Meeting Reminder: {guest.first_name} {guest.last_name} @ {event.datetime} ({guest.email})',
                    'body'    => '<div><h2 style="text-align: center;">Reminder: Meeting will start in {event.reminder_time}</h2></div><h3><strong>Event Name</strong></h3><p>{event.name} with {guest.full_name}</p><h3><strong>Guest Details</strong></h3><ul><li><strong>Name</strong>: {guest.full_name}</li><li><strong>Email</strong>: {guest.email}</li></ul><h3><strong>When</strong></h3><p>{event.full_datetime}, ({host.timezone})</p><h3><strong>Location</strong></h3><ul><li>{event.location}</li></ul><h3><strong>Guests</strong></h3><ul><li>{host.email} - host</li><li>{guest.email} - guest</li></ul>',
                    'times'   => [
                        [
                            'unit' => 'minutes',
                            'value'=> 15,
                        ]
                    ]
                ],
            ],
            'cancelled_by_attendee' => [
                'enabled' => true,
                'title'   => 'Booking Cancelled by Attendee (email to Organizer)',
                'email'   => [
                    'subject' => 'Your booking was cancelled with {guest.first_name} {guest.last_name}',
                    'body'    => '<h2 style="text-align: center;">Booking Cancellation</h2><p>Your booking has been cancelled.</p><h3><strong>Event Name</strong></h3><p>{event.name} with {guest.first_name} {guest.last_name}</p><h3><strong>Date &amp; Time</strong></h3><p>{event.full_datetime} ({host.timezone})</p><h3>Cancellation Reason</h3><p>{event.cancel_reason}</p>'
                ],
            ],
            'cancelled_by_host' => [
                'enabled' => true,
                'title'   => 'Booking Cancelled by Organizer (email to Attendee)',
                'email'   => [
                    'subject' => 'Your booking was cancelled with {host.name}',
                    'body'    => '<h2 style="text-align: center;">Booking Cancellation</h2><p>Your booking has been cancelled.</p><h3><strong>Event Name</strong></h3><p>{event.name} with {host.name}</p><h3><strong>Date &amp; Time</strong></h3><p>{event.full_datetime} ({guest.timezone})</p><h3>Cancellation Reason</h3><p>{event.cancel_reason}</p>'
                ],
            ]
        ]);

        return $defaults;
    }

    public static function getEditorShortCodes()
    {
        $shortcodes = apply_filters('fluent_calendar/editor_shortcodes', [
            '{event.name}'                => 'Event Name',
            '{event.datetime}'            => 'Event Date',
            '{event.full_datetime}'       => 'Event Full Date',
            '{event.location}'            => 'Event Location',
            '{event.description}'         => 'Event Description',
            '{event.reminder_time}'       => 'Event Reminder Time',
            '{even.cancel_reason}'        => 'Event Cancel Reason',
            '{host.timezone}'             => 'Host Timezone',
            '{host.name}'                 => 'Host Name',
            '{host.email}'                => 'Host Email',
            '{guest.timezone}'            => 'Guest Timezone',
            '{guest.first_name}'          => 'Guest First Name',
            '{guest.last_name}'           => 'Guest Last Name',
            '{guest.full_name}'           => 'Guest Full Name',
            '{guest.email}'               => 'Guest Email',
            '{guest.note}'                => 'Guest Note',
            '{wp.admin_email}'            => 'Admin Email',
            '{wp.site_url}'               => 'Site URL',
            '{wp.site_title}'             => 'Site Title',
            '{date.m/d/Y}'                => 'Date (mm/dd/yyyy)',
            '{date.d/m/Y}'                => 'Date (dd/mm/yyyy)',
            '{user.display_name}'         => 'User Display Name',
            '{user.user_email}'           => 'User Email',
            '{user.user_login}'           => 'User Username',
        ]);

        return $shortcodes;
    }
}
