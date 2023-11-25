<?php

namespace FluentBooking\App\Http\Controllers;

use Exception;
use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\App\Services\Helper;
use FluentBooking\App\Services\Integrations\CalendarIntegrationService;

class CalendarIntegrationController extends Controller
{
    public function index(CalendarIntegrationService $integrationService, $calendarId, $eventId)
    {
        try {
            $calendarEvent = CalendarSlot::findOrFail($eventId);
            $settings = $integrationService->get($eventId);

            $settings['smart_codes'] = [
                'texts' => Helper::getEditorShortCodes($calendarEvent),
                'html'  => Helper::getEditorShortCodes($calendarEvent, true)
            ];

            return $this->sendSuccess($settings);
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function find(CalendarIntegrationService $integrationService, $calendarId, $slotId, $integrationId)
    {
        try {
            $data = $this->request->all();
            $data['slot_id'] = $slotId;
            $integration = $integrationService->find($data);
            return $this->sendSuccess($integration);
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function update(CalendarIntegrationService $integrationService, $calendarId, $slotId, $integrationId)
    {

        $data = $this->request->all();
        $data['slot_id'] = $slotId;
        $data['integration_id'] = $integrationId;

        try {
            $integration = $integrationService->update($data);
            return $this->sendSuccess($integration);
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
                'errors'  => $e->errors(),
            ], 422);
        }
    }

    public function delete(CalendarIntegrationService $integrationService, $calendarId, $slotId, $integrationId)
    {
        try {
            $id = $this->request->get('integration_id');
            $integrationService->delete($id);

            return $this->sendSuccess([
                'message' => __('Successfully deleted the Integration.', 'fluent-booking-pro'),
            ]);
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function integrationListComponent($calendarId, $slotId, $integrationId)
    {
        try {
            $integrationName = $this->request->get('integration_name');
            $listId = $this->request->get('list_id');
            $merge_fields = false;

            $merge_fields = apply_filters('fluent_booking/get_integration_merge_fields_' . $integrationName, $merge_fields, $listId, $slotId);

            return $this->sendSuccess([
                'merge_fields' => $merge_fields,
            ]);
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
