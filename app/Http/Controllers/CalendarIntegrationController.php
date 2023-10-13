<?php

namespace FluentBooking\App\Http\Controllers;

use Exception;
use FluentBooking\App\Services\Integrations\CalendarIntegrationService;

class CalendarIntegrationController extends Controller
{
    public function index(CalendarIntegrationService $integrationService, $calendarId, $slotId)
    {
        try {
            $formId = (int) $this->request->get('form_id');

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
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function delete(CalendarIntegrationService $integrationService)
    {
        dd('delete');
        try {
            $id = $this->request->get('integration_id');
            $integrationService->delete($id);

            return $this->sendSuccess([
                'message' => __('Successfully deleted the Integration.', 'fluentform'),
            ], 200);
        } catch (Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function integrationListComponent()
    {
        try {
            $integrationName = $this->request->get('integration_name');
            $formId = intval($this->request->get('form_id'));
            $listId = $this->request->get('list_id');
            $merge_fields = false;
            $merge_fields = apply_filters_deprecated(
                'fluentform_get_integration_merge_fields_' . $integrationName,
                [
                    $merge_fields,
                    $listId,
                    $formId,
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/get_integration_merge_fields_' . $integrationName,
                'Use fluentform/get_integration_merge_fields_' . $integrationName . ' instead of fluentform_get_integration_merge_fields_' . $integrationName
            );

            $merge_fields = apply_filters('fluentform/get_integration_merge_fields_' . $integrationName, $merge_fields, $listId, $formId);

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
