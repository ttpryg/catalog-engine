<?php

namespace Ttpryg\CatalogEngine\Events;

use Ttpryg\CatalogEngine\Entities\Product;

class LowStockDetectedEvent
{
    public function __construct(
        public readonly Product $product,
        public readonly int $currentStock,
        public readonly int $minStockThreshold
    ) {}
}
