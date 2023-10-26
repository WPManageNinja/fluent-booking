<?php

namespace FluentBooking\App\Services\Integrations\FluentForms;

use FluentBooking\App\App;
use FluentBooking\App\Models\Booking;
use FluentBooking\App\Services\Helper;
use FluentBooking\Framework\Support\Arr;
use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\App\Services\DateTimeHelper;
use FluentBooking\App\Services\BookingService;
use FluentBooking\App\Services\TimeSlotService;
use FluentBooking\App\Hooks\Handlers\FrontEndHandler;
use FluentBooking\App\Services\Integrations\FluentForms\BookingElement;
use FluentBooking\App\Services\Integrations\FluentForms\FormDataUpdate;
use FluentForm\App\Modules\Form\FormFieldsParser;


class FluentFormInit
{
    public function init()
    {
        if (defined('FLUENTFORM')) {
            $this->registerHooks();
            $this->registerIntegrations();
        }
    }

    public function registerHooks()
    {
        add_action('fluentform/validate_input_item_fcal_booking', [$this, 'handleValidations'], 10, 3);
        add_action('fluentform/notify_on_form_submit', [$this, 'handleFormSubmitted'], 10, 3);
        add_action('fluentform/conversational_question', [$this, 'loadConversationalAsset'], 10, 3);

        add_filter('fluentform/conversational_field_types', function ($fieldTypes) {
            $fieldTypes['fcal_booking'] = 'FlowFormCustomType';
            return $fieldTypes;
        });

        add_filter('fluentform/conversational_accepted_field_elements', function ($elements) {
            $elements[] = 'fcal_booking';
            return $elements;
        });
    }

    public function registerIntegrations()
    {
        new BookingElement();
        new FormDataUpdate();
    }

    public function handleValidations($error, $field, $formData)
    {
        if ($error) {
            return $error;
        }

        $name = Arr::get($field, 'name');

        if(!isset($formData[$name])) {
            return $error;
        }

        $isRequired = Arr::get($field, 'rules.required.value');

        $bookingData = Arr::get($formData, $name);

        if ($bookingData) {
            $bookingData = json_decode($bookingData, true);
        } else {
            $bookingData = [];
        }

        if ($isRequired) {
            if (empty($bookingData['start_time']) || empty($bookingData['timezone'])) {
                $error = Arr::get($field, 'rules.required.message');
                if (!$error) {
                    $error = sprintf(__('%s field is required', 'fluent-booking-pro'), Arr::get($field, 'raw.settings.label'));
                }

                return $error;
            }
        }

        $eventId = Arr::get($field, 'raw.settings.event_id');
        $event = CalendarSlot::find($eventId);

        if (!$event || $event->status != 'active') {
            return __('Sorry, This host is not accepting any new bookings at the moment.', 'fluent-booking-pro');
        }

        $startTime = $bookingData['start_time'];
        $timeZone = $bookingData['timezone'];

        $startDateTime = DateTimeHelper::convertToUtc($startTime, $timeZone);
        $endDateTime = date('Y-m-d H:i:s', strtotime($startDateTime) + ($event->duration * 60));

        $timeSlotService = new TimeSlotService($event->calendar, $event);
        $isSpotAvailable = $timeSlotService->isSpotAvailable($startDateTime, $endDateTime);

        if (!$isSpotAvailable) {
            $message = __('This selected time slot is not available. Maybe someone booked the spot just a few seconds ago.', 'fluent-booking-pro');
            wp_send_json(['errors' => [$message]], 422);
        }

        if (!is_user_logged_in()) {
            $fieldError = '';

            // Now check if the email field is given or not
            $emailFieldKey = Arr::get($field, 'raw.settings.cal_guest_fields.email_field');
            if (!$emailFieldKey) {
                $fieldError = __('Email is required for this appointment. Looks like this field does not have email field selected.', 'fluent-booking-pro');
            } else {
                $email = Arr::get($formData, $emailFieldKey);
                if (!$email || is_email($email)) {
                    $fieldError = __('Email is required for this appointment. Please provide a valid email', 'fluent-booking-pro');
                }
            }

            if ($fieldError) {
                return $fieldError;
            }
        }

        /*
         * We are decoding the data with valid array
         */
        add_filter('fluentform/insert_response_data', function ($data) use ($name, $event) {
            if (isset($data[$name]) && is_string($data[$name])) {
                $bookingArr = json_decode($data[$name], true);
                if ($bookingArr) {
                    unset($bookingArr['id']);
                    $bookingArr['end_time'] = date('Y-m-d H:i:s', strtotime($bookingArr['start_time']) + ($event->duration * 60));
                }
                $data[$name] = (array)$bookingArr;
            }

            return $data;
        });


        return '';
    }

    public function handleFormSubmitted($entryId, $formData, $form)
    {
        $fields = FormFieldsParser::getInputs($form, ['rules', 'raw', 'name']);

        $bookingFields = array_filter($fields, function ($field) {
            return $field['element'] == 'fcal_booking';
        });

        if (!$bookingFields) {
            return;
        }

        if (\FluentForm\App\Helpers\Helper::getSubmissionMeta($entryId, 'fluent_booking_id')) {
            return; // Already processed
        }

        foreach ($bookingFields as $bookingField) {
            $fieldName = Arr::get($bookingField, 'raw.attributes.name');
            $ffFieldData = Arr::get($formData, $fieldName);

            if (!$ffFieldData) {
                continue;
            }

            if (is_string($ffFieldData)) {
                $ffFieldData = json_decode($ffFieldData, true);
            }

            if (empty($ffFieldData['timezone']) || empty($ffFieldData['start_time'])) {
                continue;
            }

            $eventId = Arr::get($bookingField, 'raw.settings.event_id');
            $event = CalendarSlot::find($eventId);

            if (!$event || $event->status != 'active') {
                continue;
            }

            $entry = wpFluent()->table('fluentform_submissions')
                ->where('id', $entryId)
                ->first();

            if (!$entry) {
                continue;
            }

            $submittedData = json_decode($entry->response, true);

            $emailFieldKey = Arr::get($bookingField, 'raw.settings.cal_guest_fields.email_field');
            $guestEmail = '';
            $guestName = '';
            if ($emailFieldKey) {
                $guestEmail = Arr::get($submittedData, $emailFieldKey);
                $nameFieldKey = Arr::get($bookingField, 'raw.settings.cal_guest_fields.name_field');

                $guestName = Arr::get($submittedData, $nameFieldKey);
                if (is_array($guestName)) {
                    $guestName = implode(' ', $guestName);
                }
            }

            if (!$guestEmail) {
                $guestEmail = Arr::get($submittedData, 'email');
                $guestName = Arr::get($submittedData, 'names');
                if (is_array($guestName)) {
                    $guestName = implode(' ', $guestName);
                }
            }

            if (!$guestEmail && $entry->user_id) {
                $user = get_user_by('id', $entry->user_id);
                if ($user) {
                    $guestEmail = $user->user_email;
                    $guestName = trim($user->first_name . ' ' . $user->last_name);
                    if (!$guestName) {
                        $guestName = $user->display_name;
                    }
                }
            }

            if (!$guestEmail || !is_email($guestEmail)) {
                do_action('fluentform/log_data', [
                    'parent_source_id' => $form->id,
                    'source_type'      => 'submission_item',
                    'source_id'        => $entry->id,
                    'component'        => 'FluentBooking',
                    'status'           => 'error',
                    'title'            => 'Appointment could not be created',
                    'description'      => 'Appointment could not be created because email is not given or invalid',
                ]);
                continue;
            }

            $startTime = $ffFieldData['start_time'];
            $timeZone = $ffFieldData['timezone'];

            $startDateTime = DateTimeHelper::convertToUtc($startTime, $timeZone);

            $bookingData = [
                'start_time'       => $startDateTime,
                'name'             => $guestName,
                'email'            => $guestEmail,
                'person_time_zone' => sanitize_text_field($ffFieldData['timezone']),
                'source'           => 'fluentform',
                'source_id'        => $entry->id,
                'status'           => 'scheduled',
                'source_url'       => $entry->source_url,
                'ip_address'       => $entry->ip
            ];

            if ($entry->user_id) {
                $bookingData['person_user_id'] = $entry->user_id;
            }

            try {
                $booking = BookingService::createBooking($bookingData, $event);

                \FluentForm\App\Helpers\Helper::getSubmissionMeta($entry->id, 'fluent_booking_id', $booking->id);

                $fieldData = $submittedData[$fieldName];
                $fieldData['booking_id'] = $booking->id;

                $submittedData[$fieldName] = (array)$fieldData;

                wpFluent()->table('fluentform_submissions')
                    ->where('id', $entryId)
                    ->update([
                        'response' => json_encode($submittedData, JSON_UNESCAPED_UNICODE)
                    ]);

                do_action('fluentform/log_data', [
                    'parent_source_id' => $form->id,
                    'source_type'      => 'submission_item',
                    'source_id'        => $entry->id,
                    'component'        => 'FluentBooking',
                    'status'           => 'info',
                    'title'            => 'Booking has been created on FluentBooking',
                    'description'      => sprintf('A new appointment has been created on FluentBooking. %1sView Booking Details%2s', '<a rel="noopener" href="' . $booking->getAdminViewUrl() . '" target="_blank">', '</a>'),
                ]);

            } catch (\Exception $exception) {
                do_action('fluentform/log_data', [
                    'parent_source_id' => $form->id,
                    'source_type'      => 'submission_item',
                    'source_id'        => $entry->id,
                    'component'        => 'FluentBooking',
                    'status'           => 'error',
                    'title'            => 'Failed to create booking',
                    'description'      => $exception->getMessage(),
                ]);
            }
        }
    }

    public function loadConversationalAsset($question, $field, $form)
    {
        if ('fcal_booking' === $field['element']) {
            [$localizeData] = (new BookingElement)->getLocalizedData($field, $form);

            wp_enqueue_script(
                'fluent_booking',
                FLUENT_BOOKING_URL . 'assets/public/js/fluentform-conversational.js',
                [],
                FLUENT_BOOKING_ASSETS_VERSION,
                true
            );

            wp_localize_script('fluent_booking', 'fcal_public_vars_' . $question['id'], $localizeData);

            wp_localize_script('fluent_booking', 'fluentCalendarPublicVars', (new FrontEndHandler())->getGlobalVars());
        }
    }
}
