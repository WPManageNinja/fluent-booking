<?php

namespace FluentCalendar\Framework\Support;

trait Tappable
{
    /**
     * Call the given Closure with this instance then return the instance.
     *
     * @param  callable|null  $callback
     * @return $this|\FluentCalendar\Framework\Support\HigherOrderTapProxy
     */
    public function tap($callback = null)
    {
        return Helper::tap($this, $callback);
    }
}
