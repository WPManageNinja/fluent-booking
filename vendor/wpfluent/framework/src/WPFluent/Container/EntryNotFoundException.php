<?php

namespace FluentCalendar\Framework\Container;

use Exception;
use FluentCalendar\Framework\Container\Contracts\Psr\NotFoundExceptionInterface;

class EntryNotFoundException extends Exception implements NotFoundExceptionInterface
{
    //
}
