<?php

namespace FluentCalendar\App\Http\Controllers;

use FluentCalendar\App\Hooks\Handlers\FrontEndHandler;
use FluentCalendar\App\Models\CalendarSlot;
use FluentCalendar\App\Services\BookingService;
use FluentCalendar\Framework\Request\Request;

class WidgetController extends Controller
{
    public function getPublicVars(Request $request)
    {
        $slotId = (int)$request->get('slot_id');
        $slot = CalendarSlot::findOrFail($slotId);
        $formFields = BookingService::getBookingFields($slot);

        $calendarVars = [
            'slot'           => $slot,
            'calendar'       => $slot->calendar,
            'author_profile' => $slot->getAuthorProfile(true),
            'form_fields'    => $formFields,
            'disable_author' => false
        ];

        $globalVars = (new FrontEndHandler())->getGlobalVars();

        return [
            'global_vars' => $globalVars,
            'app_vars'    => $calendarVars
        ];
    }
}
