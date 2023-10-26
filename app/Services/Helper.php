<?php

namespace FluentBooking\App\Services;

use FluentBooking\App\App;
use FluentBooking\App\Models\Calendar;
use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\App\Models\Meta;
use FluentBooking\App\Models\BookingMeta;
use FluentBooking\App\Services\Integrations\PaymentMethods\CurrenciesHelper;
use FluentBooking\Framework\Support\Arr;
use FluentBooking\App\Services\PermissionManager;

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
        return apply_filters('fluent_booking/admin_base_url', admin_url('admin.php?page=fluent-booking#/' . $extension), $extension);
    }

    public static function getGlobalPaymentSettings()
    {
        static $settings;

        if ($settings) {
            return $settings;
        }

        $settings = get_option('fluent_booking_global_payment_settings', []);

        if (!$settings) {
            $settings = [
                'currency'  => 'USD',
                'is_active' => 'no'
            ];
        }

        return $settings;
    }

    public static function isPaymentEnabled($calendarEvent = null)
    {
        $settings = self::getGlobalPaymentSettings();
        if (Arr::get($settings, 'is_active') == 'yes') {
            return true;
        }

        if ($calendarEvent) {
            if ($calendarEvent->type != 'paid') {
                return false;
            }

            $exist = Meta::where('object_type', 'calendar_slot')
                ->where('object_id', $calendarEvent->id)
                ->where('key', 'payment_settings')
                ->first();

            return $exist && $exist->value && Arr::get($exist->value, 'enabled') == 'yes';
        }

        return false;
    }

    /**
     * Sanitize form inputs recursively.
     *
     * @param $input
     *
     * @return mixed $input
     */
    public static function fluentBookingSanitizer($input, $attribute = null, $fields = [])
    {
        if (is_string($input)) {
            $element = Arr::get($fields, $attribute . '.element');

            if (in_array($element, ['post_content', 'rich_text_input'])) {
                return wp_kses_post($input);
            } elseif ('textarea' === $element) {
                $input = sanitize_textarea_field($input);
            } elseif ('input_email' === $element) {
                $input = strtolower(sanitize_text_field($input));
            } elseif ('input_url' === $element) {
                $input = sanitize_url($input);
            } else {
                $input = sanitize_text_field($input);
            }
        } elseif (is_array($input)) {
            foreach ($input as $key => &$value) {
                $attribute = $attribute ? $attribute . '[' . $key . ']' : $key;

                $value = self::fluentBookingSanitizer($value, $attribute, $fields);

                $attribute = null;
            }
        }

        return $input;
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

    public static function getBookingMeta($eventId, $metaKey, $withModel = false)
    {
        $bookingMeta = BookingMeta::where('booking_id', $eventId)
            ->where('meta_key', $metaKey)
            ->first();

        if ($bookingMeta) {
            return $withModel ? $bookingMeta : $bookingMeta->value;
        }

        return null;
    }

    public static function updateBookingMeta($eventId, $metaKey, $value)
    {
        $bookingMeta = self::getBookingMeta($eventId, $metaKey, true);

        if ($bookingMeta) {
            $bookingMeta->value = $value;
            $bookingMeta->save();
            return $bookingMeta;
        }

        return BookingMeta::create([
            'booking_id' => $eventId,
            'meta_key'   => $metaKey, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
            'value'      => $value
        ]);
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

    public static function getUserEmail($userId = null)
    {
        $userId = $userId ?: get_current_user_id();

        if (!$userId) {
            return '';
        }

        $user = get_user_by('ID', $userId);

        return $user->user_email;
    }

    public static function getCalendarOptionsByHost()
    {
        $calendars = Calendar::select(['id', 'title'])
            ->when(!PermissionManager::hasAllCalendarAccess(), function ($query) {
                return $query->where('user_id', get_current_user_id());
            })
            ->with(['slots'])
            ->latest()
            ->get();

        $formattedCalendars = [];
        foreach ($calendars as $index => $calendar) {
            $slots = Arr::get($calendar, 'slots');
            if (!empty($slots)) {
                $options = [];
                foreach ($slots as $slot) {
                    $options[] = [
                        'label' => Arr::get($slot, 'title'),
                        'value' => Arr::get($slot, 'id')
                    ];
                }
                if (!empty($options)) {
                    $formattedCalendars[$index] = [
                        'label'   => Arr::get($calendar, 'title'),
                        'options' => $options
                    ];
                }
            }
        }
        return $formattedCalendars;
    }

    public static function getCalendarOptionsByTitle()
    {
        $calendars = Calendar::select(['id', 'title'])
            ->when(!PermissionManager::hasAllCalendarAccess(), function ($query) {
                return $query->where('user_id', get_current_user_id());
            })
            ->with(['slots'])
            ->latest()
            ->get();


        $formattedCalendars = [];
        foreach ($calendars as $index => $calendar) {
            $slots = Arr::get($calendar, 'slots');
            if (!empty($slots)) {
                $options = [];
                foreach ($slots as $slot) {
                    $options[] = [
                        'id'    => Arr::get($slot, 'id'),
                        'title' => Arr::get($slot, 'title')
                    ];
                }
                if (!empty($options)) {
                    $formattedCalendars[$index] = [
                        'title'   => Arr::get($calendar, 'title'),
                        'options' => $options
                    ];
                }
            }
        }
        return apply_filters('fluent_booking/calendar_options_by_title', $formattedCalendars);
    }

    public static function excerpt($text, $max_length = 160)
    {
        // Strip HTML tags and convert entities to their corresponding characters
        $text = html_entity_decode(wp_strip_all_tags($text));

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
            $counter += 1;
        }

        return apply_filters('fluent_booking/slot_slug', $default, $original);
    }

    public static function getIp()
    {
        $server = $_SERVER;

        $clientIp = Arr::get($server, 'HTTP_CLIENT_IP');
        $xForwarded = Arr::get($server, 'HTTP_X_FORWARDED_FOR');

        if (!empty($clientIp)) {
            $ip = $clientIp;
        } elseif (!empty($xForwarded)) {
            $ip = $clientIp;
        } else {
            $ip = $clientIp;
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
                'svg'   => [
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

        $tags = apply_filters('fluent_booking/allowed_html_tags', $tags);

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
                if (!$method) {
                    continue;
                }
                if (is_callable($method)) {
                    $value = call_user_func($method, $value);
                } elseif (method_exists(self::class, $method)) {
                    $value = call_user_func([self::class, $method], $value);
                }
            }
        }

        return apply_filters('fluent_booking/backend_sanitized_values', $inputs, $originalValues);
    }

    /**
     * Recursively implode a multi-dimentional array
     *
     * @param string $glue
     * @param array $array
     *
     * @return string
     */
    public static function fcalImplodeRecursive($glue, array $array)
    {
        $fn = function ($glue, array $array) use (&$fn) {
            $result = '';
            foreach ($array as $item) {
                if (is_array($item)) {
                    $result .= $fn($glue, $item);
                } else {
                    $result .= $glue . $item;
                }
            }

            return $result;
        };

        return ltrim($fn($glue, $array), $glue);
    }

    public static function getEventColors()
    {
        return apply_filters('fluent_booking/event_colors', [
            [
                'label' => 'Red-Orange',
                'value' => '#ff4f00'
            ],
            [
                'label' => 'Deep Lilac',
                'value' => '#e55cff'
            ],
            [
                'label' => 'Purple',
                'value' => '#8247f5'
            ],
            [
                'label' => 'Vivid Blue',
                'value' => '#0099ff'
            ],
            [
                'label' => 'Cyan',
                'value' => '#0ae8f0'
            ],
            [
                'label' => 'Emerald Green',
                'value' => '#17e885'
            ],
            [
                'label' => 'Lime Green',
                'value' => '#ccf000'
            ],
            [
                'label' => 'Amber',
                'value' => '#ffa600'
            ]
        ]);
    }

    public static function getMeetingDurations()
    {
        return apply_filters('fluent_booking/meeting_durations_schema', [
            [
                'value' => '15',
                'label' => __('15 Minutes', 'fluent-booking-pro')
            ],
            [
                'value' => '30',
                'label' => __('30 Minutes', 'fluent-booking-pro')
            ],
            [
                'value' => '45',
                'label' => __('45 Minutes', 'fluent-booking-pro')
            ],
            [
                'value' => '60',
                'label' => __('60 Minutes', 'fluent-booking-pro')
            ],
            [
                'value' => 'custom',
                'label' => __('Custom', 'fluent-booking-pro')
            ]
        ]);
    }

    public static function getBufferTimes()
    {
        return apply_filters('fluent_booking/buffer_times_schema', [
            [
                'value' => '0',
                'label' => __('No buffer time', 'fluent-booking-pro')
            ],
            [
                'value' => '5',
                'label' => __('5 Minutes', 'fluent-booking-pro')
            ],
            [
                'value' => '10',
                'label' => __('10 Minutes', 'fluent-booking-pro')
            ],
            [
                'value' => '15',
                'label' => __('15 Minutes', 'fluent-booking-pro')
            ],
            [
                'value' => '20',
                'label' => __('20 Minutes', 'fluent-booking-pro')
            ],
            [
                'value' => '30',
                'label' => __('30 Minutes', 'fluent-booking-pro')
            ],
            [
                'value' => '45',
                'label' => __('45 Minutes', 'fluent-booking-pro')
            ],
            [
                'value' => '60',
                'label' => __('60 Minutes', 'fluent-booking-pro')
            ],
            [
                'value' => '90',
                'label' => __('90 Minutes', 'fluent-booking-pro')
            ],
            [
                'value' => '120',
                'label' => __('120 Minutes', 'fluent-booking-pro')
            ]
        ]);
    }

    public static function getWeeklyScheduleSchema()
    {
        return apply_filters('fluent_booking/weekly_schedule_schema', [
            'sun' => [
                'enabled' => false,
                'slots'   => []
            ],
            'mon' => [
                'enabled' => true,
                'slots'   => [
                    ['start' => '09:00', 'end' => '17:00']
                ],
            ],
            'tue' => [
                'enabled' => true,
                'slots'   => [
                    ['start' => '09:00', 'end' => '17:00']
                ],
            ],
            'wed' => [
                'enabled' => true,
                'slots'   => [
                    ['start' => '09:00', 'end' => '17:00']
                ],
            ],
            'thu' => [
                'enabled' => true,
                'slots'   => [
                    ['start' => '09:00', 'end' => '17:00']
                ],
            ],
            'fri' => [
                'enabled' => true,
                'slots'   => [
                    ['start' => '09:00', 'end' => '17:00']
                ],
            ],
            'sat' => [
                'enabled' => false,
                'slots'   => []
            ],
        ]);
    }

    public static function getCustomFieldTypes()
    {
        return apply_filters('fluent_booking/custom_fields_types', [
            [
                'value' => 'email',
                'label' => __('Email', 'fluent-booking-pro')
            ],
            [
                'value' => 'text',
                'label' => __('Text', 'fluent-booking-pro')
            ],
            [
                'value' => 'textarea',
                'label' => __('Textarea', 'fluent-booking-pro')
            ],
            [
                'value' => 'number',
                'label' => __('Number', 'fluent-booking-pro')
            ],
            [
                'value' => 'phone',
                'label' => __('Phone', 'fluent-booking-pro')
            ],
            [
                'value' => 'dropdown',
                'label' => __('Dropdown', 'fluent-booking-pro')
            ]
        ]);
    }

    public static function getDefaultEmailNotificationSettings()
    {
        $checkImage = App::getInstance()['url.assets'] . 'images/check-mark.png';

        return apply_filters('fluent_booking/default_email_notification_settings', [
            'booking_conf_attendee' => [
                'enabled' => true,
                'title'   => __('Booking Confirmation Email to Attendee', 'fluent-booking-pro'),
                'email'   => [
                    'subject' => 'Booking Confirmation between {{host.name}} & {{guest.full_name}}',
                    'body'    => '<p style="text-align: center;"><img class="alignnone  wp-image-76" src="' . $checkImage . '" alt="" width="60" height="60" /></p><h2 class="p1" style="text-align: center;">Your event has been scheduled</h2><hr /><p><strong>Event Name</strong></p><p>{{booking.event_name}} with {{host.name}}</p><p><strong>When</strong></p><p>{{booking.full_start_end_guest_timezone}}</p><p><strong>Who</strong></p><ul><li>{{host.name}} - Organizer</li><li>{{guest.full_name}} - you</li></ul><p><strong>Where</strong></p><p>{{booking.location_details_html}}</p><p><strong>Additional notes</strong></p><p>{{guest.note}}</p><hr /><p style="text-align: center;">' . __('Need to make a change?', 'fluent-booking-pro') . '<a href="##booking.reschedule_url##">' . __('Reschedule', 'fluent-booking-pro') . '</a> or <a href="##booking.cancelation_url##">' . __('Cancel', 'fluent-booking-pro') . '</a></p>'
                ],
            ],
            'booking_conf_host'     => [
                'enabled' => true,
                'is_host' => true,
                'title'   => __('Booking Confirmation Email to Organizer (You)', 'fluent-booking-pro'),
                'email'   => [
                    'additional_recipients' => '',
                    'subject'               => 'New Booking: {{guest.full_name}} @ {{booking.start_date_time_for_host}}',
                    'body'                  => '<p style="text-align: center;"><img class="alignnone  wp-image-76" src="' . $checkImage . '" alt="" width="60" height="60" /></p><h2 class="p1" style="text-align: center;">A new event has been scheduled</h2><hr /><p><strong>Event Name</strong></p><p>{{booking.event_name}} with {{guest.full_name}}</p><p><strong>When</strong></p><p>{{booking.full_start_end_host_timezone}}</p><p><strong>Who</strong></p><ul><li>{{host.name}} - Organizer</li><li>{{guest.full_name}} ({{guest.email}}) - Guest</li></ul><p><strong>Where</strong></p><p>{{booking.location_details_html}}</p><p><strong>Note</strong></p><p>{{guest.note}}</p><p><strong>Additional Data</strong></p><p>{{guest.form_data_html}}</p><hr /><p style="text-align: center;"><a href="##booking.admin_booking_url##">View on the Website</a></p>'
                ],
            ],
            'reminder_to_attendee'  => [
                'enabled' => false,
                'title'   => __('Configure Meeting Reminder to Attendee', 'fluent-booking-pro'),
                'email'   => [
                    'subject' => 'Meeting Reminder with {{host.name}} @ {{booking.start_date_time_for_attendee}}',
                    'body'    => '<h2 style="text-align: center;">Reminder: Your meeting will start in {{booking.start_time_human_format}}</h2><hr /><p><strong>Event Name</strong></p><p>{{booking.event_name}} with {{host.name}}</p><h3><strong>When</strong></h3><p>{{booking.full_start_end_guest_timezone}}</p><h3><strong>Who</strong></h3><ul><li>{{host.name}} - Organizer</li><li>{{guest.full_name}} - you</li></ul><p><strong>Where</strong></p><p>{{booking.location_details_html}}</p><p><strong>Additional notes</strong></p><p>{{guest.note}}</p><hr /><p style="text-align: center;">' . __('Need to make a change?', 'fluent-booking-pro') . '<a href="##booking.reschedule_url##">' . __('Reschedule', 'fluent-booking-pro') . '</a> or <a href="##booking.cancelation_url##">' . __('Cancel', 'fluent-booking-pro') . '</a></p>',
                    'times'   => [
                        [
                            'unit'  => 'minutes',
                            'value' => 15,
                        ]
                    ]
                ],
            ],
            'reminder_to_host'      => [
                'enabled' => false,
                'is_host' => true,
                'title'   => __('Configure Meeting Reminder to Organizer (You)', 'fluent-booking-pro'),
                'email'   => [
                    'additional_recipients' => '',
                    'subject'               => 'Meeting Reminder with {{host.name}} @ {{booking.start_date_time_for_host}}',
                    'body'                  => '<h2 style="text-align: center;">Reminder: Your meeting will start in {{booking.start_time_human_format}}</h2><hr /><p><strong>Event Name</strong></p><p>{{booking.event_name}} with {{guest.full_name}}</p><p><strong>When</strong></p><p>{{booking.full_start_end_host_timezone}}</p><p><strong>Who</strong></p><ul><li>{{host.name}} - Organizer</li><li>{{guest.full_name}} ({{guest.email}}) - Guest</li></ul><p><strong>Where</strong></p><p>{{booking.location_details_html}}</p><p><strong>Note</strong></p><p>{{guest.note}}</p><p><strong>Additional Data</strong></p><p>{{guest.form_data_html}}</p><hr /><p style="text-align: center;"><a href="##booking.admin_booking_url##">View on the Website</a></p>',
                    'times'                 => [
                        [
                            'unit'  => 'minutes',
                            'value' => 15,
                        ]
                    ]
                ],
            ],
            'cancelled_by_attendee' => [
                'enabled' => true,
                'is_host' => true,
                'title'   => __('Booking Cancelled by Attendee (email to Organizer)', 'fluent-booking-pro'),
                'email'   => [
                    'additional_recipients' => '',
                    'subject'               => 'A booking was cancelled with {{guest.full_name}}',
                    'body'                  => '<h2 style="text-align: center;">Booking Cancellation</h2><hr /><p>A scheduled meeting has been canceled. Here are the details:</p><p><strong>Event Name</strong></p><p>{{booking.event_name}} with {{guest.full_name}}</p><p><strong>When</strong></p><p>{{booking.full_start_end_host_timezone}} <span style="color: #ff0000;"><strong>(cancelled)</strong></span></p><p><strong>Cancellation Reason</strong></p><p>{{booking.cancel_reason}}</p><p><strong>Who</strong></p><ul><li>{{host.name}} - Organizer</li><li>{{guest.full_name}} ({{guest.email}}) - Guest</li></ul><p><strong>Where</strong></p><p>{{booking.location_details_html}}</p><p><strong>Note</strong></p><p>{{guest.note}}</p><p><strong>Additional Data</strong></p><p>{{guest.form_data_html}}</p><hr /><p style="text-align: center;"><a href="##booking.admin_booking_url##">View on the Website</a></p>'
                ],
            ],
            'cancelled_by_host'     => [
                'enabled' => true,
                'title'   => __('Booking Cancelled by Organizer (email to Attendee)', 'fluent-booking-pro'),
                'email'   => [
                    'subject' => 'Your booking was cancelled with {{host.name}}',
                    'body'    => '<h2 style="text-align: center;">Booking Cancellation</h2><hr /><p>Your scheduled meeting has been canceled. Here are the details:</p><p><strong>Event Name</strong></p><p>{{booking.event_name}} with {{guest.full_name}}</p><p><strong>When</strong></p><p>{{booking.full_start_end_host_timezone}} <span style="color: #ff0000;"><strong>(cancelled)</strong></span></p><p><strong>Cancellation Reason</strong></p><p>{{booking.cancel_reason}}</p>'
                ],
            ],
            'rescheduled_by_attendee' => [
                'enabled' => true,
                'is_host' => true,
                'title'   => __('Booking Rescheduled by Attendee (email to Organizer)', 'fluent-booking-pro'),
                'email'   => [
                    'additional_recipients' => '',
                    'subject'               => 'A booking was rescheduled with {{guest.full_name}}',
                    'body'                  => '<h2 style="text-align: center;">Booking Rescheduled</h2><hr /><p>A scheduled meeting has been rescheduled. Here are the details:</p><p><strong>Event Name</strong></p><p>{{booking.event_name}} with {{guest.full_name}}</p><p><strong>When</strong></p><p>New Time: {{booking.full_start_end_host_timezone}} <span style="color: #ff0000;"><strong>(new)</strong></span></p><p>Previous Time: {{booking.previous_meeting_time}}</p><p><strong>Rescheduling Reason</strong></p><p>{{booking.reschedule_reason}}</p><p><strong>Who</strong></p><ul><li>{{host.name}} - Organizer</li><li>{{guest.full_name}} ({{guest.email}}) - Guest</li></ul><p><strong>Where</strong></p><p>{{booking.location_details_html}}</p><p><strong>Note</strong></p><p>{{guest.note}}</p><p><strong>Additional Data</strong></p><p>{{guest.form_data_html}}</p><hr /><p style="text-align: center;"><a href="##booking.admin_booking_url##">View on the Website</a></p>'
                ],
            ],
            'rescheduled_by_host'     => [
                'enabled' => true,
                'title'   => __('Booking Rescheduled by Organizer (email to Attendee)', 'fluent-booking-pro'),
                'email'   => [
                    'subject' => 'Your booking was rescheduled with {{host.name}}',
                    'body'    => '<h2 style="text-align: center;">Booking Rescheduled</h2><hr /><p>Your scheduled meeting has been rescheduled. Here are the details:</p><p><strong>Event Name</strong></p><p>{{booking.event_name}} with {{guest.full_name}}</p><p><strong>When</strong></p><p>New Time: {{booking.full_start_end_host_timezone}} <span style="color: #ff0000;"><strong>(new)</strong></span></p><p>Previous Time: {{booking.previous_meeting_time}}</p><p><strong>Rescheduling Reason</strong></p><p>{{booking.reschedule_reason}}</p>'
                ],
            ]
        ]);
    }

    public static function getEditorShortCodes($calendarEvent = null, $isHtmlSupported = false)
    {
        if (!$isHtmlSupported) {
            $groups = [
                'guest'   => [
                    'title'      => 'Attendee Data',
                    'key'        => 'guest',
                    'shortcodes' => [
                        '{{guest.first_name}}' => 'Guest First Name',
                        '{{guest.last_name}}'  => 'Guest Last Name',
                        '{{guest.full_name}}'  => 'Guest Full Name',
                        '{{guest.email}}'      => 'Guest Email',
                        '{{guest.note}}'       => 'Guest Note',
                        '{{booking.phone}}'    => 'Guest Main Phone Number (if provided)',
                        '{{guest.timezone}}'   => 'Guest Timezone'
                    ]
                ],
                'booking' => [
                    'title'      => 'Booking Data',
                    'key'        => 'booking',
                    'shortcodes' => [
                        '{{booking.event_name}}'                    => 'Event Name',
                        '{{booking.description}}'                   => 'Event Description',
                        '{{booking.full_start_end_guest_timezone}}' => 'Full Start & End Time (with guest timezone)',
                        '{{booking.full_start_end_host_timezone}}'  => 'Full Start & End Time (with host timezone)',
                        '{{booking.start_date_time}}'               => 'Event Date Time (UTC)',
                        '{{booking.start_date_time_for_attendee}}'  => 'Event Date time (with attendee timezone)',
                        '{{booking.start_date_time_for_host}}'      => 'Event Date time (with host timezone)',
                        '{{booking.cancel_reason}}'                 => 'Event Cancel Reason',
                        '{{booking.start_time_human_format}}'       => 'Event Start Time (ex: 2 hours from now)',
                        '##booking.cancelation_url##'               => 'Booking Cancellation URL',
                        '##booking.reschedule_url##'                => 'Booking Reschedule URL',
                        '##booking.admin_booking_url##'             => 'Booking Details Admin URL',
                        '{{booking.booking_hash}}'                  => 'Unique Booking Hash',
                        '{{booking.reschedule_reason}}'             => 'Event Reschedule Reason'
                    ]
                ],
                'host'    => [
                    'title'      => 'Host Data',
                    'key'        => 'host',
                    'shortcodes' => [
                        '{{host.name}}'     => 'Host Name',
                        '{{host.email}}'    => 'Host Email',
                        '{{host.timezone}}' => 'Host Timezone',
                    ]
                ],
                'other'   => [
                    'title'      => 'Other',
                    'key'        => 'other',
                    'shortcodes' => [
                        '{{event.id}}'             => 'Event ID',
                        '{{calendar.id}}'          => 'Calendar ID',
                        '{{calendar.title}}'       => 'Calendar Title',
                        '{{calendar.description}}' => 'Calendar Description',
                    ]
                ]
            ];
        } else {
            $groups = [
                'guest'   => [
                    'title'      => 'Attendee Data',
                    'key'        => 'guest',
                    'shortcodes' => [
                        '{{guest.first_name}}'     => 'Guest First Name',
                        '{{guest.last_name}}'      => 'Guest Last Name',
                        '{{guest.full_name}}'      => 'Guest Full Name',
                        '{{guest.email}}'          => 'Guest Email',
                        '{{booking.phone}}'        => 'Guest Main Phone Number (if provided)',
                        '{{guest.note}}'           => 'Guest Note',
                        '{{guest.timezone}}'       => 'Guest Timezone',
                        '{{guest.form_data_html}}' => 'Guest Form Submitted Data (HTML)'
                    ]
                ],
                'booking' => [
                    'title'      => 'Booking Data',
                    'key'        => 'booking',
                    'shortcodes' => [
                        '{{booking.event_name}}'                    => 'Event Name',
                        '{{booking.description}}'                   => 'Event Description',
                        '{{booking.full_start_end_guest_timezone}}' => 'Full Start & End Time (with guest timezone)',
                        '{{booking.full_start_end_host_timezone}}'  => 'Full Start & End Time (with host timezone)',
                        '{{booking.start_date_time}}'               => 'Event Date Time (UTC)',
                        '{{booking.start_date_time_for_attendee}}'  => 'Event Date time (with guest timezone)',
                        '{{booking.start_date_time_for_host}}'      => 'Event Date time (with host timezone)',
                        '{{booking.location_details_html}}'         => 'Event Location Details (HTML)',
                        '{{booking.cancel_reason}}'                 => 'Event Cancel Reason',
                        '{{booking.start_time_human_format}}'       => 'Event Start Time (ex: 2 hours from now)',
                        '##booking.cancelation_url##'               => 'Booking Cancellation URL',
                        '##booking.reschedule_url##'                => 'Booking Reschedule URL',
                        '##booking.admin_booking_url##'             => 'Booking Details Admin URL',
                        '{{booking.booking_hash}}'                  => 'Unique Booking Hash',
                        '{{booking.reschedule_reason}}'             => 'Event Reschedule Reason'
                    ]
                ],
                'host'    => [
                    'title'      => 'Host Data',
                    'key'        => 'host',
                    'shortcodes' => [
                        '{{host.name}}'     => 'Host Name',
                        '{{host.email}}'    => 'Host Email',
                        '{{host.timezone}}' => 'Host Timezone',
                    ]
                ],
                'other'   => [
                    'title'      => 'Other',
                    'key'        => 'other',
                    'shortcodes' => [
                        '{{event.id}}'             => 'Event ID',
                        '{{event.calendar_id}}'    => 'Calendar ID',
                        '{{calendar.title}}'       => 'Calendar Title',
                        '{{calendar.description}}' => 'Calendar Description',
                    ]
                ]
            ];
        }

        if ($calendarEvent) {
            $customFields = BookingFieldService::getCustomFields($calendarEvent, false);
            foreach ($customFields as $fieldKey => $fieldLabel) {
                $groups['booking']['shortcodes']['{{booking.custom.' . $fieldKey . '}}'] = $fieldLabel;
            }

            if (Helper::isPaymentEnabled($calendarEvent)) {
                $groups['payment'] = [
                    'title'      => 'Payment Data',
                    'key'        => 'payment',
                    'shortcodes' => [
                        '{{payment.payment_total}}'  => 'Payment Total',
                        '{{payment.payment_status}}' => 'Payment Status',
                        '{{payment.payment_method}}' => 'Payment Method',
                        '{{payment.currency}}'       => 'Currency',
                        '{{payment.payment_date}}'   => 'Payment Date',
                    ]
                ];

                if ($isHtmlSupported) {
                    $groups['payment']['shortcodes']['{{payment.receipt_html}}'] = 'Payment Receipt (HTML)';
                }
            }

        }

        return apply_filters('fluent_booking/editor_shortcodes_groups', $groups, $calendarEvent, $isHtmlSupported);
    }

    public static function encryptKey($value)
    {
        if (!$value) {
            return $value;
        }

        if (!extension_loaded('openssl')) {
            return $value;
        }

        $salt = (defined('LOGGED_IN_SALT') && '' !== LOGGED_IN_SALT) ? LOGGED_IN_SALT : 'this-is-a-fallback-salt-but-not-secure';

        if (defined('FLUENT_BOOKING_ENCRYPTION_KEY')) {
            $key = FLUENT_BOOKING_ENCRYPTION_KEY;
        } else {
            $key = (defined('LOGGED_IN_KEY') && '' !== LOGGED_IN_KEY) ? LOGGED_IN_KEY : 'this-is-a-fallback-key-but-not-secure';
        }

        $method = 'aes-256-ctr';
        $ivlen = openssl_cipher_iv_length($method);
        $iv = openssl_random_pseudo_bytes($ivlen);

        $raw_value = openssl_encrypt($value . $salt, $method, $key, 0, $iv);
        if (!$raw_value) {
            return false;
        }

        return base64_encode($iv . $raw_value); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
    }

    public static function decryptKey($raw_value)
    {

        if (!$raw_value) {
            return $raw_value;
        }

        if (!extension_loaded('openssl')) {
            return $raw_value;
        }

        $raw_value = base64_decode($raw_value, true); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode

        $method = 'aes-256-ctr';
        $ivlen = openssl_cipher_iv_length($method);
        $iv = substr($raw_value, 0, $ivlen);

        $raw_value = substr($raw_value, $ivlen);

        if (defined('FLUENT_BOOKING_ENCRYPTION_KEY')) {
            $key = FLUENT_BOOKING_ENCRYPTION_KEY;
        } else {
            $key = (defined('LOGGED_IN_KEY') && '' !== LOGGED_IN_KEY) ? LOGGED_IN_KEY : 'this-is-a-fallback-key-but-not-secure';
        }

        $salt = (defined('LOGGED_IN_SALT') && '' !== LOGGED_IN_SALT) ? LOGGED_IN_SALT : 'this-is-a-fallback-salt-but-not-secure';

        $value = openssl_decrypt($raw_value, $method, $key, 0, $iv);
        if (!$value || substr($value, -strlen($salt)) !== $salt) {
            return false;
        }

        return substr($value, 0, -strlen($salt));
    }

    public static function debugLog($data)
    {
        if (defined('FLUENT_BOOKING_DEBUG') && FLUENT_BOOKING_DEBUG) {
            error_log(print_r($data, true));
        }
    }

    public static function getGlobalSettings($settingsKey = null)
    {
        $defaults = [
            'emailing'       => [
                'from_name'               => '',
                'from_email'              => '',
                'reply_to_name'           => '',
                'reply_to_email'          => '',
                'use_host_name'           => '',
                'use_host_email_on_reply' => '',
                'email_footer'            => ''
            ],
            'administration' => [
                'admin_email'            => '{{wp.admin_email}}',
                'summary_notification'   => 'no',
                'notification_frequency' => 'daily',
                'notification_day'       => 'mon',
                'start_day'              => 'sun',
            ],
            'time_format' => '24'
        ];

        $settings = get_option('_fluent_booking_settings', []);

        if (empty($settings)) {
            $settings = [];
        }

        $settings = wp_parse_args($settings, $defaults);

        $emailSettings = $settings['emailing'];

        if (empty($emailSettings['from_name']) && defined('FLUENTCRM')) {
            $crmSettings = fluentcrmGetGlobalSettings('email_settings', []);
            $emailSettings['from_name'] = Arr::get($crmSettings, 'from_name');
            if (empty($emailSettings['from_email'])) {
                $emailSettings['from_email'] = Arr::get($crmSettings, 'from_email');
            }
            if (empty($emailSettings['reply_to_name'])) {
                $emailSettings['reply_to_name'] = Arr::get($crmSettings, 'reply_to_name');
            }
            if (empty($emailSettings['reply_to_email'])) {
                $emailSettings['reply_to_email'] = Arr::get($crmSettings, 'reply_to_email');
            }

            $settings['emailing'] = $emailSettings;
        }

        if ($settingsKey) {
            return Arr::get($settings, $settingsKey, []);
        }

        return $settings;

    }

    public static function getVerifiedSenders()
    {
        $verifiedSenders = [];
        if (defined('FLUENTMAIL')) {
            $smtpSettings = get_option('fluentmail-settings', []);
            if ($smtpSettings && count($smtpSettings['mappings'])) {
                $verifiedSenders = array_keys($smtpSettings['mappings']);
            }
        }
        /**
         * Filter the verified email senders
         * @param array $verifiedSenders
         */
        return apply_filters('fluent_booking/verfied_email_senders', $verifiedSenders);
    }

    public static function getBookingReceiptLandingBaseUrl()
    {
        return apply_filters('fluent_booking/booking_receipt_landing_base_url', site_url('/'));
    }
}
