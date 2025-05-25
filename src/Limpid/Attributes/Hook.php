<?php

namespace Potager\Limpid\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Hook
{
    /**
     * @param string[]|string $events The event(s) you want to hook on
     * @param int $priority The priority of execution of this hook
     */
    public function __construct(public array|string $events, public int $priority = 0)
    {
        if (is_string($this->events))
            $this->events = [$events];
    }
}