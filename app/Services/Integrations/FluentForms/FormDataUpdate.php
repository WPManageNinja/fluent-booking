<?php

namespace FluentBooking\App\Services\Integrations\FluentForms;

use FluentForm\App\Models\Form;
use FluentForm\App\Models\Submission;
use FluentBooking\Framework\Support\Arr;
use FluentForm\App\Services\FormBuilder\ShortCodeParser;


class FormDataUpdate
{
    private $form       = [];
    private $submission = [];

    public function __construct()
    {
        add_action('fluent_booking/booking_schedule', [$this, 'updateFormData'], 10, 1);
    }

    public function updateFormData(&$booking)
    {
        try{
            $submissionId = Arr::get($booking, 'source_id');

            if ('fluentform' != $booking->source || !$submissionId) {
                return;
            }

            $this->submission = Submission::findOrFail($submissionId);

            $this->form = Form::findOrFail($this->submission->form_id);

            $this->updateSourceLink($booking);

            $this->updateEntryData($booking);

        } catch (\Exception $e) {
            wp_send_json([
                $e->getMessage()
            ], $e->getCode());
        }
    }

    public function updateSourceLink(&$booking)
    {
        $url = admin_url('admin.php?page=fluent_forms&route=entries&form_id=' . $this->submission->form_id . '#/entries/' . $this->submission->id);

        $link = '<a target="_blank" href="' . esc_url($url) . '">' . 'view entry details' . '</a>';

        $booking->source = $link;
    }

    public function updateEntryData(&$booking)
    {
        $response = json_decode($this->submission->response);

        $entryHtmlData = ShortCodeParser::parse(
            '{all_data}',
            $this->submission->id,
            $response,
            $this->form,
            false,
            true
        );

        $booking->sourceDetails = [
            'title'   => 'Fluent From',
            'content' =>  $entryHtmlData
        ];
    }
}
