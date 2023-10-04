<?php

namespace FluentBooking\App\Services\Integrations\GoogleCalendar;

use Exception;
use FluentBooking\App\Models\Booking;
use FluentBooking\Framework\Support\Arr;
use FluentBooking\App\Services\Helper;
use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\App\Services\DateTimeHelper;
use FluentBooking\App\Services\Integrations\IntegrationManager;
use FluentBooking\App\Services\Integrations\GoogleCalendar\Client;
use FluentBooking\Framework\Validator\ValidationException;

class GoogleCalendar extends IntegrationManager
{
    private $client;

    public function __construct()
    {
        parent::__construct(
            'google_calendar',
            'google_calendar_auth',
            'google_calendar_settings',
            '_fcal_google_calendar_client_details'
        );

        $credentials  = $this->getClientDetails();

        $this->clientId     = Arr::get($credentials, 'client_id');
        $this->clientSecret = Arr::get($credentials, 'client_secret');
        $this->redirectUrl  = Arr::get($credentials, 'redirect_url');
        
        $this->initClient();
        $this->initHooks();
    }

    public function initClient()
    {
        $this->client = new Client(
            $this->clientId,
            $this->clientSecret,
            $this->redirectUrl
        );
    }

    public function initHooks()
    {
        add_filter('fluent_booking/settings_menu_items', [$this, 'addMenu'], 10, 1);
        add_action('fluent_booking/after_booking_scheduled', [$this, 'updateEvent'], 10, 2);
        add_action('fluent_booking/after_patch_booking_schedule', [$this, 'updateEvent'], 10, 1);
        add_filter('fluent_booking/booked_events', [$this, 'getBookedEvents'], 10, 4);
        add_action('wp_ajax_fluent_booking_g_auth', [$this, 'handleAuthCallback'] );
    }

    public function handleAuthCallback()
    {
        if (!isset($_GET['code'], $_GET['scope'])) {
            return;
        }
        
        $code = sanitize_text_field($_GET['code']);

        $scope = sanitize_text_field($_GET['scope']);

        $this->authenticate($code);

        do_action('fluent_booking/google_calendar_integration', $code, $scope);

        wp_redirect(admin_url('admin.php?page=fluent-booking#/settings/integrations/google_calendar'));

        exit;
    }

    public function authenticate($code)
    {
        $authData = $this->client->generateAccessToken($code, 'authorization_code');

        if (is_wp_error($authData)) {
            return;
        }

        $authData['code'] = $code;

        $authData['expires_in'] = $authData['expires_in'] + time();

        $this->updateAuthDetails($authData);

        do_action('fluent_booking/google_calendar_authenticated', $authData);
    }

    public function getAccessToken($hostId = null)
    {
        $tokens = $this->getAuthDetails($hostId);

        if (!$tokens) {
            return false;
        }

        $expiresIn    = Arr::get($tokens, 'expires_in');
        $refreshToken = Arr::get($tokens, 'refresh_token');

        if (($expiresIn - 30) < time()){
            $tokens = $this->client->reGenerateToken($refreshToken);

            if (!$tokens) {
                return false;
            }
            
            $this->updateAuthDetails($tokens, $hostId);
        }

        return $tokens['access_token'];
    }

    private function isConnected()
    {
        $accessToken = $this->getAccessToken();

        return $accessToken ? true : false;
    }

    private function getClientAuthUrl()
    {
        if (!$this->clientId || !$this->clientSecret) {
            return '';
        }
        return $this->client->getAuthUrl();
    }

    protected function getHostEmail($hostId)
    {
        $user = get_user_by('id', $hostId);
        if (!$user) {
            return '';
        }
        return $user->user_email;
    }

    protected function getEventLocation($booking)
    {
        $locationType = Arr::get($booking, 'location_details.location_type');

        $location = Arr::get($booking, 'location_details.location_heading');

        if ('phone' == $locationType) {
            $location = 'Phone Call: ' . $booking->phone;
        } elseif ('google_meet' == $locationType) {
            $location = 'Google Meet';
        }

        return $location;
    }

    protected function getAttendees($email, $attendees)
    {
        $emails = array_column($attendees, 'email');

        if (!in_array($email, $emails)) {
            $attendees[] = ['email' => $email];
        }

        return $attendees;
    }

    public function addMenu($menu)
    {
        $menu['configurations']['submenu'][] = [
            'key'       => 'google_calendar',
            'label'     => __('Google Calendar', 'fluent-booking'),
        ];
        return $menu;
    }

    public function getClientFields()
    {
        $fields = [
            'logo'          => '<svg xmlns="http://www.w3.org/2000/svg" width="33" height="32" viewBox="0 0 33 32" fill="none"><path d="M29.5895 0H24.3871V7.538H31.9995V2.12552C32.0015 2.12552 31.6233 0.205563 29.5895 0Z" fill="#1967D2"/><path d="M24.3889 31.965V31.9835V31.9999L32.0012 24.4619H31.933L24.3889 31.965Z" fill="#1967D2"/><path d="M32.0011 24.4627V24.3945L31.9329 24.4627H32.0011Z" fill="#FBBC05"/><path d="M32.0012 7.53809H24.3889V24.3943H32.0012V7.53809Z" fill="#FBBC05"/><path d="M31.933 24.4619H24.3889V31.965L31.933 24.4619Z" fill="#EA4335"/><path d="M24.3889 24.4627H31.933L32.0012 24.3945H24.3889V24.4627Z" fill="#EA4335"/><path d="M24.3708 31.9834H24.3895V31.9648L24.3708 31.9834Z" fill="#34A853"/><path d="M7.44104 24.3945V31.9839H24.371L24.3896 24.3945H7.44104Z" fill="#34A853"/><path d="M24.3895 24.4624V24.3945L24.3708 31.9839L24.3895 31.9654V24.4624Z" fill="#34A853"/><path d="M-0.000183105 24.3945V29.6713C0.0680245 31.3837 1.91996 31.9839 1.91996 31.9839H7.44065V24.3945H-0.000183105Z" fill="#188038"/><path d="M7.44065 7.538H24.3892V0H2.13492C2.13492 0 0.136232 0.205563 -0.000183105 2.32903V24.3942H7.44065V7.538Z" fill="#4285F4"/><path d="M12.9857 20.9824C12.598 20.9824 12.2241 20.9319 11.864 20.8309C11.5132 20.7299 11.1901 20.5784 10.8946 20.3764C10.5992 20.1652 10.3361 19.9035 10.1053 19.5913C9.88371 19.2791 9.71291 18.9165 9.5929 18.5033L11.2962 17.8284C11.4162 18.2875 11.6194 18.6364 11.9055 18.8751C12.1917 19.1047 12.5518 19.2195 12.9857 19.2195C13.1796 19.2195 13.3642 19.1919 13.5396 19.1368C13.715 19.0725 13.8674 18.9853 13.9966 18.8751C14.1259 18.765 14.2274 18.6364 14.3013 18.4895C14.3844 18.3334 14.4259 18.159 14.4259 17.9661C14.4259 17.5621 14.2736 17.2454 13.9689 17.0158C13.6735 16.7863 13.2627 16.6715 12.7364 16.6715H11.9194V15.0325H12.6672C12.8518 15.0325 13.0319 15.0096 13.2073 14.9637C13.3827 14.9178 13.535 14.8489 13.6643 14.7571C13.8028 14.6561 13.9089 14.5321 13.9828 14.3852C14.0659 14.2291 14.1074 14.0501 14.1074 13.8481C14.1074 13.5359 13.9966 13.2834 13.7751 13.0906C13.5535 12.8886 13.2534 12.7876 12.8749 12.7876C12.4687 12.7876 12.1548 12.8978 11.9332 13.1181C11.7209 13.3293 11.5732 13.568 11.4901 13.8343L9.82832 13.1595C9.9114 12.9299 10.036 12.6958 10.2022 12.457C10.3684 12.2091 10.5761 11.9888 10.8254 11.796C11.0839 11.594 11.3839 11.4333 11.7255 11.3139C12.0671 11.1854 12.4595 11.1211 12.9026 11.1211C13.355 11.1211 13.7658 11.1854 14.1351 11.3139C14.5136 11.4425 14.8368 11.6215 15.1045 11.851C15.3722 12.0714 15.5799 12.3377 15.7277 12.6499C15.8754 12.9529 15.9492 13.2834 15.9492 13.6415C15.9492 13.917 15.9123 14.1649 15.8384 14.3852C15.7738 14.6056 15.6861 14.803 15.5753 14.9775C15.4645 15.1519 15.3353 15.3034 15.1876 15.432C15.0491 15.5513 14.906 15.6477 14.7583 15.7212V15.8314C15.2014 16.0058 15.5661 16.2859 15.8523 16.6715C16.1477 17.0571 16.2954 17.5438 16.2954 18.1314C16.2954 18.5446 16.217 18.9256 16.06 19.2745C15.9031 19.6143 15.6769 19.9127 15.3814 20.1698C15.0953 20.4269 14.749 20.6243 14.3428 20.762C13.9366 20.9089 13.4842 20.9824 12.9857 20.9824Z" fill="#4285F4"/><path d="M19.6024 20.762V13.4625L17.9268 14.1649L17.2621 12.6361L20.0456 11.3415H21.4166V20.762H19.6024Z" fill="#4285F4"/></svg>',
            'title'         => __('Google Calendar/Meet', 'fluent_booking'),
            'subtitle'      => __('Configure Google Calendar/Meet to sync your events', 'fluent_booking'),
            'description'   => __('<p>Login to your Google account, go to Google Cloud Console, create a project, complete OAuth Consent screen process, click on Create Credentials, and you will get your client id and secret key. If you get the ID and Keys for Google Calendar, Google Meet will be integrated automatically. For full details read the <a href="">documentation</a></p>', 'fluent_booking'),
            'save_btn_text' => __('Save', 'fluent_booking'),
            'fields'        => [
                'client_id'     => [
                    'type'        => 'text',
                    'label'       => __('Client ID', 'fluent_booking'),
                    'placeholder' => __('Enter Your Client ID', 'fluent_booking'),
                ],
                'client_secret' => [
                    'type'        => 'text',
                    'label'       => __('Secret Key', 'fluent_booking'),
                    'placeholder' => __('Enter Your Secret Key', 'fluent_booking'),
                ],
                'redirect_url'  => [
                    'type'        => 'text',
                    'label'       => __('Redirect URI', 'fluent_booking'),
                    'placeholder' => __('Enter Your Redirect URI', 'fluent_booking'),
                    'readonly'    => true,
                    'copy_btn'    => true,
                ],
            ],
        ];

        return $fields;
    }

    public function getClientSettings()
    {
        $defaults = [
            'client_id'     => '',
            'client_secret' => '',
            'redirect_url'  => admin_url('/admin-ajax.php?action=fluent_booking_g_auth'),
        ];

        $clientDetails = $this->getClientDetails();

        $settings = wp_parse_args($clientDetails, $defaults);

        return $settings;
    }

    public function saveClientSettings($settings)
    {
        $rules = [
            'client_id'     => 'required',
            'client_secret' => 'required',
            'redirect_url'  => 'required'
        ];

        $validator = $this->app->validator->make($settings, $rules, []);

        if ($validator->validate()->fails()) {
            throw new ValidationException(
                'Unprocessable Entity!', 422, null, $validator->errors()
            );
        }

        $data = [
            'client_id'     => sanitize_text_field(Arr::get($settings, 'client_id')),
            'client_secret' => sanitize_text_field(Arr::get($settings, 'client_secret')),
            'redirect_url'  => sanitize_url(Arr::get($settings, 'redirect_url'))
        ];

        $this->updateClientDetails($data);

        wp_send_json([
            'message' => __('Your Google Calendar api key has been successfully set'),
            'status'  => true
        ], 200);
    }

    public function getIntegrationFields()
    {
        $fields = [
            'logo'          => '<svg xmlns="http://www.w3.org/2000/svg" width="33" height="32" viewBox="0 0 33 32" fill="none"><path d="M29.5895 0H24.3871V7.538H31.9995V2.12552C32.0015 2.12552 31.6233 0.205563 29.5895 0Z" fill="#1967D2"/><path d="M24.3889 31.965V31.9835V31.9999L32.0012 24.4619H31.933L24.3889 31.965Z" fill="#1967D2"/><path d="M32.0011 24.4627V24.3945L31.9329 24.4627H32.0011Z" fill="#FBBC05"/><path d="M32.0012 7.53809H24.3889V24.3943H32.0012V7.53809Z" fill="#FBBC05"/><path d="M31.933 24.4619H24.3889V31.965L31.933 24.4619Z" fill="#EA4335"/><path d="M24.3889 24.4627H31.933L32.0012 24.3945H24.3889V24.4627Z" fill="#EA4335"/><path d="M24.3708 31.9834H24.3895V31.9648L24.3708 31.9834Z" fill="#34A853"/><path d="M7.44104 24.3945V31.9839H24.371L24.3896 24.3945H7.44104Z" fill="#34A853"/><path d="M24.3895 24.4624V24.3945L24.3708 31.9839L24.3895 31.9654V24.4624Z" fill="#34A853"/><path d="M-0.000183105 24.3945V29.6713C0.0680245 31.3837 1.91996 31.9839 1.91996 31.9839H7.44065V24.3945H-0.000183105Z" fill="#188038"/><path d="M7.44065 7.538H24.3892V0H2.13492C2.13492 0 0.136232 0.205563 -0.000183105 2.32903V24.3942H7.44065V7.538Z" fill="#4285F4"/><path d="M12.9857 20.9824C12.598 20.9824 12.2241 20.9319 11.864 20.8309C11.5132 20.7299 11.1901 20.5784 10.8946 20.3764C10.5992 20.1652 10.3361 19.9035 10.1053 19.5913C9.88371 19.2791 9.71291 18.9165 9.5929 18.5033L11.2962 17.8284C11.4162 18.2875 11.6194 18.6364 11.9055 18.8751C12.1917 19.1047 12.5518 19.2195 12.9857 19.2195C13.1796 19.2195 13.3642 19.1919 13.5396 19.1368C13.715 19.0725 13.8674 18.9853 13.9966 18.8751C14.1259 18.765 14.2274 18.6364 14.3013 18.4895C14.3844 18.3334 14.4259 18.159 14.4259 17.9661C14.4259 17.5621 14.2736 17.2454 13.9689 17.0158C13.6735 16.7863 13.2627 16.6715 12.7364 16.6715H11.9194V15.0325H12.6672C12.8518 15.0325 13.0319 15.0096 13.2073 14.9637C13.3827 14.9178 13.535 14.8489 13.6643 14.7571C13.8028 14.6561 13.9089 14.5321 13.9828 14.3852C14.0659 14.2291 14.1074 14.0501 14.1074 13.8481C14.1074 13.5359 13.9966 13.2834 13.7751 13.0906C13.5535 12.8886 13.2534 12.7876 12.8749 12.7876C12.4687 12.7876 12.1548 12.8978 11.9332 13.1181C11.7209 13.3293 11.5732 13.568 11.4901 13.8343L9.82832 13.1595C9.9114 12.9299 10.036 12.6958 10.2022 12.457C10.3684 12.2091 10.5761 11.9888 10.8254 11.796C11.0839 11.594 11.3839 11.4333 11.7255 11.3139C12.0671 11.1854 12.4595 11.1211 12.9026 11.1211C13.355 11.1211 13.7658 11.1854 14.1351 11.3139C14.5136 11.4425 14.8368 11.6215 15.1045 11.851C15.3722 12.0714 15.5799 12.3377 15.7277 12.6499C15.8754 12.9529 15.9492 13.2834 15.9492 13.6415C15.9492 13.917 15.9123 14.1649 15.8384 14.3852C15.7738 14.6056 15.6861 14.803 15.5753 14.9775C15.4645 15.1519 15.3353 15.3034 15.1876 15.432C15.0491 15.5513 14.906 15.6477 14.7583 15.7212V15.8314C15.2014 16.0058 15.5661 16.2859 15.8523 16.6715C16.1477 17.0571 16.2954 17.5438 16.2954 18.1314C16.2954 18.5446 16.217 18.9256 16.06 19.2745C15.9031 19.6143 15.6769 19.9127 15.3814 20.1698C15.0953 20.4269 14.749 20.6243 14.3428 20.762C13.9366 20.9089 13.4842 20.9824 12.9857 20.9824Z" fill="#4285F4"/><path d="M19.6024 20.762V13.4625L17.9268 14.1649L17.2621 12.6361L20.0456 11.3415H21.4166V20.762H19.6024Z" fill="#4285F4"/></svg>',
            'title'         => __('Google Calendar/Meet', 'fluent_booking'),
            'subtitle'      => __('Configure Google Calendar/Meet to sync your events', 'fluent_booking'),
            'save_btn_text' => __('Save', 'fluent_booking'),
            'auth_url'      => $this->getClientAuthUrl(),
            'is_connected'  => $this->isConnected(),
            'fields'        => [
                'add_to_calendar' => [
                    'logo'     => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M8 2V5" stroke="#1B2533" stroke-width="1.25" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M16 2V5" stroke="#1B2533" stroke-width="1.25" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M3.5 9.08984H20.5" stroke="#1B2533" stroke-width="1.25" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M22 19C22 19.75 21.79 20.46 21.42 21.06C20.73 22.22 19.46 23 18 23C16.99 23 16.07 22.63 15.37 22C15.06 21.74 14.79 21.42 14.58 21.06C14.21 20.46 14 19.75 14 19C14 16.79 15.79 15 18 15C19.2 15 20.27 15.53 21 16.36C21.62 17.07 22 17.99 22 19Z" stroke="#1B2533" stroke-width="1.25" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M16.4399 18.9995L17.4299 19.9895L19.5599 18.0195" stroke="#1B2533" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/><path d="M21 8.5V16.36C20.27 15.53 19.2 15 18 15C15.79 15 14 16.79 14 19C14 19.75 14.21 20.46 14.58 21.06C14.79 21.42 15.06 21.74 15.37 22H8C4.5 22 3 20 3 17V8.5C3 5.5 4.5 3.5 8 3.5H16C19.5 3.5 21 5.5 21 8.5Z" stroke="#1B2533" stroke-width="1.25" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M11.9955 13.7002H12.0045" stroke="#1B2533" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/><path d="M8.29431 13.7002H8.30329" stroke="#1B2533" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/><path d="M8.29431 16.7002H8.30329" stroke="#1B2533" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                    'type'     => 'checkbox',
                    'title'    => __('Add To Calendar', 'fluent_booking'),
                    'subtitle' => __('Set the calendar you would like to add new events to as they’re scheduled', 'fluent_booking'),
                ],
                'check_conflict'  => [
                    'logo'     => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M8 2V5" stroke="#1B2533" stroke-width="1.25" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M16 2V5" stroke="#1B2533" stroke-width="1.25" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M3.5 9.08984H20.5" stroke="#1B2533" stroke-width="1.25" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M22 19C22 19.75 21.79 20.46 21.42 21.06C20.73 22.22 19.46 23 18 23C16.99 23 16.07 22.63 15.37 22C15.06 21.74 14.79 21.42 14.58 21.06C14.21 20.46 14 19.75 14 19C14 16.79 15.79 15 18 15C19.2 15 20.27 15.53 21 16.36C21.62 17.07 22 17.99 22 19Z" stroke="#1B2533" stroke-width="1.25" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M16.4399 18.9995L17.4299 19.9895L19.5599 18.0195" stroke="#1B2533" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/><path d="M21 8.5V16.36C20.27 15.53 19.2 15 18 15C15.79 15 14 16.79 14 19C14 19.75 14.21 20.46 14.58 21.06C14.79 21.42 15.06 21.74 15.37 22H8C4.5 22 3 20 3 17V8.5C3 5.5 4.5 3.5 8 3.5H16C19.5 3.5 21 5.5 21 8.5Z" stroke="#1B2533" stroke-width="1.25" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M11.9955 13.7002H12.0045" stroke="#1B2533" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/><path d="M8.29431 13.7002H8.30329" stroke="#1B2533" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/><path d="M8.29431 16.7002H8.30329" stroke="#1B2533" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                    'type'     => 'checkbox',
                    'title'    => __('Check For Conflict', 'fluent_booking'),
                    'subtitle' => __('Set the calendar(s) to check for conflicts to prevent double bookings', 'fluent_booking'),
                ]
            ]
        ];

        return $fields;
    }

    public function getIntegrationSettings()
    {
        $defaults = [
            'check_conflict'  => false,
            'add_to_calendar' => false
        ];

        $integrationSettings = $this->getIntegrationDetails();

        $settings = wp_parse_args($integrationSettings, $defaults);

        return $settings;
    }

    public function saveIntegrationSettings($settings)
    {
        $data = [
            'check_conflict'  => Arr::isTrue($settings, 'check_conflict'),
            'add_to_calendar' => Arr::isTrue($settings, 'add_to_calendar')
        ];

        $this->updateIntegrationDetails($data);

        wp_send_json([
            'message' => __('Your Google Calendar settings has been successfully set'),
            'status'  => true
        ], 200);
    }

    public function disconnectIntegration()
    {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            throw new \Exception('You are already disconnected', 423);
        }

        $body = [
            'token' => $accessToken,
        ];

        $response = static::makeRequest($this->client->revokeUrl, $body, 'POST');

        if (is_wp_error($response)) {
            throw new \Exception('Something went wrong. Please try again', 423);
        }

        $this->deleteAuthDetails();

        wp_send_json([
            'message' => __('Your Google Calendar integration has been successfully removed'),
            'status'  => true
        ], 200);
    }

    protected function getUpdatedResponse($booking, $header)
    {
        $eventDetails = $this->getResponse($booking->event_id);

        $eventId = Arr::get($eventDetails, 'id');

        if (!$eventId) {
            return [];
        }

        $url = $this->client->calendarEvent . $eventId;

        $response = static::makeRequest($url, '', 'GET', $header);

        if (is_wp_error($response)) {
            return $eventDetails;
        }

        return $response;
    }

    public function updateEvent($booking, $calendarSlot = null)
    {
        if (!$calendarSlot) {
            $calendarSlot = CalendarSlot::findOrFail($booking->slot_id);
        }

        $integrationSettings = $this->getIntegrationDetails($calendarSlot->user_id);
        if (!Arr::isTrue($integrationSettings, 'add_to_calendar')) {
            return;
        }

        $accessToken = $this->getAccessToken($calendarSlot->user_id);
        if (!$accessToken) {
            return;
        }
        
        $header = static::getStandardHeader($accessToken);

        // If event is already created
        $eventDetails = $this->getUpdatedResponse($booking, $header);
        $eventId      = Arr::get($eventDetails, 'id');
        $meetingLink  = Arr::get($eventDetails, 'hangoutLink');
        $attendees    = Arr::get($eventDetails, 'attendees');

        $isNewEvent = !$eventId;

        $method = $isNewEvent ? 'POST' : 'PATCH';
        
        $url = $this->client->calendarEvent . $eventId . '?sendUpdates=all';

        $hostEmail = $this->getHostEmail($calendarSlot->user_id);

        $location = $this->getEventLocation($booking);

        $events = [
            'summary'     => $calendarSlot->title,
            'description' => $booking->message,
            'attendees'   => [
                ['email' => $hostEmail],
                ['email' => $booking->email]
            ],
            'start' => [
                'dateTime' => DateTimeHelper::convertToIso($booking->start_time),
                'timeZone' => $booking->person_time_zone,
            ],
            'end' => [
                'dateTime' => DateTimeHelper::convertToIso($booking->end_time),
                'timeZone' => $booking->person_time_zone,
            ],
            'location' => $location,
            'status' => ('cancelled' == $booking->status) ? 'cancelled' : 'confirmed',
            'extendedProperties' => [
                'shared' => [
                    'created_by' => 'fluent_booking',
                ],
            ],
        ];

        if (!$isNewEvent) {
            $events['attendees'] = $this->getAttendees($booking->email, $attendees);
        }

        if ('Google Meet' == $location && !$meetingLink) {
            $events['conferenceData'] = [
                'createRequest' => [
                    'requestId' => $booking->hash,
                    'conferenceSolutionKey' => [
                        'type' => 'hangoutsMeet'
                    ],
                ],
            ];
            $url .= '&conferenceDataVersion=1';
        }

        $response = static::makeRequest($url, $events, $method, $header);

        if (is_wp_error($response)) {
            return;
        }

        $this->updateResponse($booking->event_id, $response);

        $this->updateEventLink($booking->id, $response);

        $this->logBookingActivity($isNewEvent, $booking->id, $response);

        do_action('fluent_booking/google_calendar_event_updated', $booking, $calendarSlot, $response);
    }

    public function getBookedEvents($books, $calendarSlot, $dateRanges, $timeZone)
    {
        $integrationSettings = $this->getIntegrationDetails($calendarSlot->user_id);
        if (!Arr::isTrue($integrationSettings, 'check_conflict')) {
            return $books;
        }

        $accessToken = $this->getAccessToken($calendarSlot->user_id);
        if (!$accessToken) {
            return $books;
        }
        
        $header = static::getStandardHeader($accessToken);
        
        $startRange = DateTimeHelper::convertToIso($dateRanges[0]);
        $endRange   = DateTimeHelper::convertToIso($dateRanges[1]);

        $query = [
            'timeMin' => $startRange,
            'timeMax' => $endRange,
        ];

        $url = $this->client->calendarEvent . '?' . http_build_query($query);

        $response = static::makeRequest($url, '', 'GET', $header);

        if (is_wp_error($response)) {
            return $books;
        }

        $bookedEvents = Arr::get($response, 'items');

        foreach ($bookedEvents as $event)
        {    
            if ('fluent_booking' == Arr::get($event, 'extendedProperties.shared.created_by')) {
                continue;
            }

            $startTime = Arr::get($event, 'start.dateTime');
            $endTime   = Arr::get($event, 'end.dateTime');

            $start = DateTimeHelper::convertFromIso($startTime, $timeZone);
            $end   = DateTimeHelper::convertFromIso($endTime, $timeZone);
            
            $date = date('Y-m-d', strtotime($start));

            $books[$date] = $books[$date] ?? [];

            $books[$date][] = [
                'start'     => $start,
                'end'       => $end,
                'remaining' => 0
            ];
        }

        return $books;
    }

    public function updateEventLink($bookingId, $response)
    {
        $meetingLink  = Arr::get($response, 'hangoutLink');
        if (!$meetingLink) {
            return;
        }

        $locationSettings = [
            'location_type' => 'google_meet',
            'location_heading' => 'Google Meet',
            'location_settings' => [
                'meeting_link'  => $meetingLink,
            ]
        ];

        $booking = Booking::findOrFail($bookingId);
        $booking->location_details = $locationSettings;
        $booking->save();

        do_action('fluent_booking/after_event_link_updated', $booking, $response);
    }

    public function logBookingActivity($isNewEvent, $bookingId, $response)
    {
        $htmlLink = Arr::get($response, 'htmlLink');
        if (!$isNewEvent || !$htmlLink) {
            return;
        }

        $eventLink = '<a target="_blank" href="' . esc_url($htmlLink) . '">' . esc_html('here') . '</a>';

        do_action('fluent_booking/log_booking_note', [
            'title'       => sprintf(__('Event Created in Google Calendar')),
            'type'        => 'activity',
            'description' => sprintf(__('Find Event in Google Calendar: %s'), $eventLink),
            'booking_id'  => $bookingId
        ]);
    }
}
