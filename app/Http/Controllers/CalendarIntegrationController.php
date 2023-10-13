<?php

namespace FluentBooking\App\Http\Controllers;

use Exception;
use FluentBooking\App\Services\Integrations\CalendarIntegrationService;

class CalendarIntegrationController extends Controller
{
    public function index(CalendarIntegrationService $integrationService, $calendarId, $slotId)
    {
        try {
            return $this->sendSuccess(
                $integrationService->get($calendarId)
            );
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function find(CalendarIntegrationService $integrationService, $calendarId, $slotId, $integrationId)
    {
        try {
            $integration = $integrationService->find($this->request->all());

            return $this->sendSuccess($integration);
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function update(CalendarIntegrationService $integrationService, $calendarId, $slotId, $integrationId)
    {
        try {
            $integration = $integrationService->update($this->request->all());

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
                'message' => __('Successfully deleted the Integration.', 'fluent_booking'),
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
