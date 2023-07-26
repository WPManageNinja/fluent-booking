<?php
namespace FluentCalendar\App\Services\Integrations\FluentForms;


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
            'fcal_booking_field',
            'Calendar Booking',
            ['booking', 'calendar'],
            'advanced'
        );

        add_filter('fluentform/response_render_fcal_booking_field', array($this, 'renderResponse'), 10, 3);

    }

    function getComponent()
    {
        return [
            'index' => 20,
            'element' => 'fcal_booking_field',
            'attributes' => array(
                'name' => 'fcal_booking_field',
                'data-type' => 'fcal_booking_field'
            ),
            'settings' => array(
                'label' => __('Calendar Booking Field', 'fluentformpro'),
                'admin_field_label' => '',
                'slot_id' => '',
                'container_class' => '',
                'validation_rules' => array(),
                'conditional_logics' => array(),
            ),
            'fields' => array(
                array(
                    'element' => 'input_text',
                    'attributes' => array(
                        'type' => 'text',
                        'value' => '',
                        'placeholder' => '',
                    ),
                    'editor_options' => array(),
                )
            ),
            'editor_options' => array(
                'title' => __('Calendar Booking Field', 'fluent-calendar'),
                'icon_class' => 'ff-edit-repeat',
                'template' => 'fieldsRepeatSettings'
            ),
        ];
    }

    public function getGeneralEditorElements()
    {
        return [
            'label',
            'label_placement',
            'admin_field_label',
            'slot_id',
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
            'fields_repeat_settings' => array(
                'template' => 'fieldsRepeatSettings',
                'label' => __('Repeat Field Columns', 'fluentformpro'),
                'help_text' => __('Field Columns which a user will be able to repeat.', 'fluentformpro'),
            )
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
        print_r($data);
        echo 'Booking Form';
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
}
