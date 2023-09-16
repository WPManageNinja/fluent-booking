<?php

namespace FluentCalendar\Framework\Container\Contracts;

use Exception;
use FluentCalendar\Framework\Container\Contracts\Psr\ContainerExceptionInterface;

class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
