<?php

namespace Ttpryg\CatalogEngine\Contracts;

interface EventDispatcherInterface
{
    public function dispatch(object $event): void;
}
