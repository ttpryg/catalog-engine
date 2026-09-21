<?php

declare(strict_types=1);

namespace Ttpryg\CatalogEngine\Exceptions;

class InsufficientStockException extends CatalogEngineException
{
    public function __construct(int $requested, int $available)
    {
        parent::__construct("Insufficient stock. Requested {$requested}, but only {$available} available.");
    }
}
