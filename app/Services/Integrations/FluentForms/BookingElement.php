<?php

namespace FluentCalendar\App\Services\Integrations\FluentForms;


use FluentForm\App\Services\FormBuilder\BaseFieldManager;
use FluentForm\Framework\Helpers\ArrayHelper;
use FluentCalendar\App\Models\Calendar;
use FluentCalendar\App\Services\PermissionManager;

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
            'fcal_booking_field',
            'Calendar Booking',
            ['booking', 'calendar'],
            'advanced'
        );
        add_filter('fluentform/response_render_fcal_booking_field', array($this, 'renderResponse'), 10, 3);
        add_filter('fluentform/select_group_component_ajax_options', array($this, 'getCalendarOptions'));
    }
    
    function getComponent()
    {
        return [
            'index'          => 20,
            'element'        => 'fcal_booking_field',
            'attributes'     => array(
                'name'      => 'fcal_booking_field',
                'data-type' => 'fcal_booking_field'
            ),
            'settings'       => array(
                'label'              => __('Fluent Calendar Field', 'fluent-calendar'),
                'admin_field_label'  => '',
                'slot_id'            => '',
                'booking_calendar'   => '',
                'conditional_logics' => array(),
                'container_class'    => '',
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
            'booking_calendar' => [
                'template' => 'selectGroup',
                'label'    => __('Select Calendar', 'fluentform'),
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
        echo '<pre>';
        print_r($data);
        echo 'Booking Form';
        echo '</pre>';
    }
    
    public function renderResponse($response, $field, $form_id)
    {
        return 'hello There';
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
            $slots = ArrayHelper::get($calendar, 'slots');
            if (!empty($slots)) {
                $options = [];
                foreach ($slots as $slot) {
                    $options[] = [
                        'label' => ArrayHelper::get($slot, 'title'),
                        'value' => ArrayHelper::get($slot, 'id')
                    ];
                }
                if (!empty($options)) {
                    $formattedCalendars[$index] = [
                        'label'   => ArrayHelper::get($calendar, 'title'),
                        'options' => $options
                    ];
                }
            }
        }
        return apply_filters('fluent_calendar/ff_editor_calendar_options', $formattedCalendars);
    }
}
