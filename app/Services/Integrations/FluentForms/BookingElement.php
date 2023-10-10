<?php

namespace FluentBooking\App\Services\Integrations\FluentForms;


use FluentBooking\App\App;
use FluentBooking\Framework\Support\Arr;
use FluentBooking\App\Services\Helper;
use FluentBooking\App\Models\Booking;
use FluentBooking\App\Models\Calendar;
use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\App\Services\DateTimeHelper;
use FluentBooking\App\Services\PermissionManager;
use FluentBooking\App\Hooks\Handlers\FrontEndHandler;
use FluentForm\App\Services\FormBuilder\BaseFieldManager;

class BookingElement extends BaseFieldManager
{
    /**
     * Wrapper class for repeat element
     * @var string
     */
    protected $wrapperClass = 'fcal_booking_elem';
    
    public function __construct()
    {
        parent::__construct(
            'fcal_booking',
            'Calendar Booking',
            ['booking', 'calendar'],
            'advanced'
        );
        add_filter('fluentform/response_render_fcal_booking', array($this, 'renderResponse'), 10, 3);
        add_filter('fluentform/select_group_component_ajax_options', array($this, 'getCalendarOptions'));

        add_action('fluentform/loading_editor_assets', function () {
            wp_enqueue_script('fluentcal_ff_editor_extended', FLUENT_BOOKING_URL . 'assets/admin/fluentform.js', [], '1.0.0', true);
        });
    }
    
    function getComponent()
    {
        return [
            'index'          => 20,
            'element'        => 'fcal_booking',
            'attributes'     => array(
                'name'      => 'fcal_booking',
                'data-type' => 'fcal_booking'
            ),
            'settings'       => array(
                'label'              => __('Fluent Booking Field', 'fluent-booking'),
                'admin_field_label'  => '',
                'event_id'            => '',
                'booking_calendar'   => '',
                'conditional_logics' => array(),
                'container_class'    => '',
                'cal_guest_fields' => [
                    'email_field' => '',
                    'name_field' => ''
                ],
                'validation_rules'   => array(
                    'required' => [
                        'value'   => false,
                        'message' => __('This field is required', 'fluent-booking'),
                    ],
                ),
            ),
            'editor_options' => array(
                'title'      => __('Calendar Booking Field', 'fluent-booking'),
                'icon_class' => 'ff-edit-repeat',
                'template'   => 'inputCalendar'
            ),
        ];
    }
    
    public function getGeneralEditorElements()
    {
        return [
            'booking_calendar',
            'label',
            'label_placement',
            'admin_field_label',
            'event_id',
            'cal_guest_fields',
            'validation_rules',
        ];
    }
    
    public function getAdvancedEditorElements()
    {
        return [
            'container_class',
            'name',
            'conditional_logics'
        ];
    }
    
    public function getEditorCustomizationSettings()
    {
        return [
            'event_id' => [
                'template' => 'selectGroup',
                'label'    => __('Select Calendar', 'fluentform'),
            ],
            'cal_guest_fields' => [
                'template' => 'CustomSettingsField',
                'label'    => __('Guest Fields', 'fluentform'),
                'componentName' => 'FluentCalNameEmailChoiceComponent'
            ],
        ];
    }
    
    /**
     * Compile and echo the html element
     * @param array $data [element data]
     * @param stdClass $form [Form Object]
     * @return void
     */
    public function render($data, $form)
    {
        $element_id = $this->makeElementId($data, $form);

        $slot_id = (int)Arr::get($data, 'settings.event_id');
        
        $slot = CalendarSlot::find($slot_id);
        
        if (!$slot) {
            return 'Slot Not Found';
        }
        
        $calendar = $slot->calendar;

        if (!$slot->calendar) {
            return 'Calendar Not Found';
        }

        $slot->max_lookup_date = $slot->getMaxLookUpDate();

        $slot->min_lookup_date = $slot->getMinLookUpDate();

        $slot->location_settings = (object)[];

        $slot->description = wpautop($slot->description);

        $settings = Arr::get($data, 'settings');

        $name = Arr::get($data, 'attributes.name');

        wp_enqueue_script(
            'fluentform-calendar-public',
            App::getInstance('url.assets') . 'public/js/fluentform.js', [],
            App::getInstance('config')->get('app.version'), true
        );

        wp_localize_script('fluentform-calendar-public', 'fcal_public_vars_' . $element_id, [
            'name'           => $name,
            'slot'           => $slot,
            'calendar'       => $calendar,
            'settings'       => $settings,
            'form_instance'  => $form->instance_css_class,
            'author_profile' => $slot->getAuthorProfile(true),
            'disable_author' => true,
        ]);

        wp_localize_script('fluentform-calendar-public', 'fluentCalendarPublicVars',
            (new FrontEndHandler())->getGlobalVars()
        );

        App::make('view')->render('public.fluentform.calendar', [
            'element_id'    => $element_id,
            'calendar_app'  => 'fluentform_calendar_app'
        ]);
    }
    
    public function renderResponse($response, $field, $form_id)
    {
        $data = json_decode($response, true);

        $slot_id   = Arr::get($field, 'raw.settings.event_id');
        $startTime = Arr::get($data, 'start_time');
        $timezone  = Arr::get($data, 'timezone');

        if (!$startTime || !$timezone) {
            return '';
        }
        
        $startTimeUtc = DateTimeHelper::convertToUtc($startTime, $timezone);

        $booking = Booking::with('calendar')
            ->where('event_id', $slot_id)
            ->where('start_time', $startTimeUtc)
            ->first();
            
        $eventId      = Arr::get($booking, 'group_id');
        $hostTimezone = Arr::get($booking, 'calendar.author_timezone');

        if (!$eventId || !$hostTimezone) {
            return '';
        }

        $formattedTime = DateTimeHelper::convertToTimeZone($startTimeUtc, 'utc', $hostTimezone, 'j M Y, g:i A');

        $url = admin_url('admin.php?page=fluent-booking#/scheduled-events?spot_id=' . $eventId);

        $link = '<a target="_blank" href="' . esc_url($url) . '">' . esc_html($formattedTime) . '</a>';
        
        return $link;
    }
    
    protected function getResponseHtml($response, $fields, $columns)
    {
        return 'HTML Response';
    }
    
    protected function getResponseAsText($response, $fields, $columns)
    {
        return 'Text Response';
    }
    
    public function getCalendarOptions()
    {
        $calendarOptions = Helper::getCalendarOptionsByHost();

        return apply_filters('fluent_booking/ff_editor_calendar_options', $calendarOptions);
    }
}
