<?php

namespace Potager\Limpid\Attributes;

use Attribute;
use Potager\Limpid\Contracts\LimpidAttribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Hook implements LimpidAttribute
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

    public function getEvents()
    {
        return $this->events;
    }

    public function getPriority()
    {
        return $this->priority;
    }
}

#[Attribute(Attribute::TARGET_METHOD)]
class BeforeCreate extends Hook
{
    public function __construct(int $priority = 0)
    {
        parent::__construct('beforeCreate', $priority);
    }
}

#[Attribute(Attribute::TARGET_METHOD)]
class BeforeUpdate extends Hook
{
    public function __construct(int $priority = 0)
    {
        parent::__construct('beforeUpdate', $priority);
    }
}

#[Attribute(Attribute::TARGET_METHOD)]
class BeforeSave extends Hook
{
    public function __construct(int $priority = 0)
    {
        parent::__construct('beforeSave', $priority);
    }
}

#[Attribute(Attribute::TARGET_METHOD)]
class AfterCreate extends Hook
{
    public function __construct(int $priority = 0)
    {
        parent::__construct('afterCreate', $priority);
    }
}

#[Attribute(Attribute::TARGET_METHOD)]
class AfterUpdate extends Hook
{
    public function __construct(int $priority = 0)
    {
        parent::__construct('afterUpdate', $priority);
    }
}

#[Attribute(Attribute::TARGET_METHOD)]
class AfterSave extends Hook
{
    public function __construct(int $priority = 0)
    {
        parent::__construct('afterSave', $priority);
    }
}