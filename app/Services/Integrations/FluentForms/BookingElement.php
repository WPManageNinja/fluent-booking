<?php

namespace FluentBooking\App\Services\Integrations\FluentForms;


use FluentBooking\App\App;
use FluentBooking\Framework\Support\Arr;
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
                'label'              => __('Fluent Calendar Field', 'fluent-calendar'),
                'admin_field_label'  => '',
                'slot_id'            => '',
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
                        'message' => __('This field is required', 'fluent-calendar'),
                    ],
                ),
            ),
            'editor_options' => array(
                'title'      => __('Calendar Booking Field', 'fluent-calendar'),
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
            'slot_id',
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
            'slot_id' => [
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

        $slot_id = (int)Arr::get($data, 'settings.slot_id');
        
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

        $slot_id   = Arr::get($field, 'raw.settings.slot_id');
        $startTime = Arr::get($data, 'start_time');
        $timezone  = Arr::get($data, 'timezone');

        if (!$startTime || !$timezone) {
            return '';
        }
        
        $startTimeUtc = DateTimeHelper::convertToUtc($startTime, $timezone);

        $booking = Booking::with('calendar')
            ->where('slot_id', $slot_id)
            ->where('start_time', $startTimeUtc)
            ->first();

        if (!$booking) {
            return '';
        }
        
        $formattedTime = DateTimeHelper::convertToTimeZone($startTimeUtc, 'utc', $booking->calendar->author_timezone, 'j M Y, g:i A');

        $url = admin_url('admin.php?page=fluent-calendar#/scheduled-events?spot_id=' . $booking->id);

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
        if (PermissionManager::hasAllCalendarAccess()) {
            $calendars = Calendar::select(['id', 'title'])->with(['slots'])->latest()->get();
        } else {
            $calendars = Calendar::select(['id', 'title'])->where('user_id', get_current_user_id())->latest()->get();
        }
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
        return apply_filters('fluent_booking/ff_editor_calendar_options', $formattedCalendars);
    }
}
