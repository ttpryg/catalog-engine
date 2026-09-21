<?php

declare(strict_types=1);

namespace Ttpryg\CatalogEngine\Contracts;

interface EventDispatcherInterface
{
    public function dispatch(object $event): void;
}
