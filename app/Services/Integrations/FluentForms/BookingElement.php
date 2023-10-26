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
use FluentForm\Framework\Helpers\ArrayHelper;

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
                'label'              => __('FluentBooking Field', 'fluent-booking-pro'),
                'admin_field_label'  => '',
                'event_id'           => '',
                'booking_calendar'   => '',
                'conditional_logics' => array(),
                'container_class'    => '',
                'cal_guest_fields'   => [
                    'email_field' => '',
                    'name_field'  => ''
                ],
                'validation_rules'   => array(
                    'required' => [
                        'value'   => false,
                        'message' => __('This field is required', 'fluent-booking-pro'),
                    ],
                ),
            ),
            'editor_options' => array(
                'title'      => __('FluentBooking Field', 'fluent-booking-pro'),
                'icon_class' => 'el-icon-date',
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
            'event_id'         => [
                'template' => 'selectGroup',
                'label'    => __('Select Calendar', 'fluent-booking-pro'),
            ],
            'cal_guest_fields' => [
                'template'      => 'CustomSettingsField',
                'label'         => __('Guest Fields', 'fluent-booking-pro'),
                'componentName' => 'FluentCalNameEmailChoiceComponent'
            ],
        ];
    }

    /**
     * Compile and echo the html element
     * @param array $data [element data]
     * @param object $form [Form Object]
     * @return void
     */
    public function render($data, $form)
    {
        $elementName = $data['element'];

        $data['attributes']['class'] = @trim('ff-el-form-control ' . Arr::get($data, 'attributes.class'));
        $data['attributes']['id'] = $this->makeElementId($data, $form);
        if ($tabIndex = \FluentForm\App\Helpers\Helper::getNextTabIndex()) {
            $data['attributes']['tabindex'] = $tabIndex;
        }

        $ariaRequired = 'false';
        if (Arr::get($data, 'settings.validation_rules.required.value')) {
            $ariaRequired = 'true';
        }

        [$localizeData, $element_id] = $this->getLocalizedData($data, $form);
        $localizeData['time_format'] = (Helper::getGlobalSettings())['time_format'];

        wp_enqueue_script(
            'fluentform-calendar-public',
            App::getInstance('url.assets') . 'public/js/fluentform.js', [],
            FLUENT_BOOKING_ASSETS_VERSION, true
        );

        wp_localize_script('fluentform-calendar-public', 'fcal_public_vars_' . $element_id, $localizeData);

        wp_localize_script('fluentform-calendar-public', 'fluentCalendarPublicVars',
            (new FrontEndHandler())->getGlobalVars()
        );

        $elMarkup = '<div class="fcal_cal_wrap"><div class="fluentform_calendar_app" data-element_id="' . esc_attr($element_id) . '"></div></div>';
        $html = $this->buildElementMarkup($elMarkup, $data, $form);
        echo apply_filters('fluentform/rendering_field_html_' . $elementName, $html, $data, $form);
    }

    public function getLocalizedData($data, $form)
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

        $slot->description = wpautop($slot->description);

        $settings = Arr::get($data, 'settings');

        $name = Arr::get($data, 'attributes.name');

        $localizeData = (new FrontEndHandler())->getCalendarEventVars($calendar, $slot);

        $localizeData['name'] = $name;
        $localizeData['settings'] = $settings;
        $localizeData['disable_author'] = true;
        $localizeData['form_instance'] = $form->instance_css_class;

        return [$localizeData, $element_id];
    }

    public function renderResponse($data, $field, $form_id)
    {
        if (is_string($data)) {
            $data = json_decode($data, true);
        }

        if (!$data) {
            return '';
        }

        $data = (array)$data;

        $text = Arr::get($data, 'start_time') . ' ( ' . Arr::get($data, 'timezone') . ' )';

        if (defined('FLUENTFORM_RENDERING_ENTRY')) {

            $booking = Booking::find(Arr::get($data, 'booking_id'));

            if ($booking && $booking->calendar) {
                $calendar = $booking->calendar;
                $html = '<div class="ff_entry_table_wrapper"><table class="ff_entry_table_field ff-table">';
                $html .= '<tr>';
                $html .= '<th>Booking ID</th>';
                $html .= '<td>' . $booking->id . ' <a href="' . Helper::getAppBaseUrl('scheduled-events?period=upcoming&booking_id=' . $booking->id) . '" target="_blank">View Booking</a></td>';
                $html .= '</tr>';
                $html .= '<tr>';
                $html .= '<th>Booking Status</th>';
                $html .= '<td>' . $booking->status . '</td>';
                $html .= '</tr>';
                $html .= '<tr>';
                $html .= '<th>Date & Time</th>';
                $html .= '<td>' . $booking->getFullBookingDateTimeText($calendar->author_timezone, true) . ' (' . $calendar->author_timezone . ')</td>';
                $html .= '</tr>';
                $html .= '<tr>';
                $html .= '<th>Meeting Duration</th>';
                $html .= '<td>' . $booking->slot_minutes . ' Minutes</td>';
                $html .= '</tr>';
                $html .= '<tr>';
                $html .= '<th>Meeting Host</th>';
                $html .= '<td>' . $calendar->title . '</td>';
                $html .= '</tr>';
                $html .= '</html></div>';
                return $html;
            }
        }

        return $text;
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
