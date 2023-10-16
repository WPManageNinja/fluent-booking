<?php

namespace FluentBooking\App\Hooks\Handlers\CleanupHandlers;

class CleanupHandler
{
    public function register()
    {
        (new CalenderCleaner())->register();
        (new CalenderEventCleaner())->register();
        (new BookingCleaner())->register();
        (new OrderCleaner())->register();
        (new UserCleaner())->register();
    }
}
