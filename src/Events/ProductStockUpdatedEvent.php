<?php

declare(strict_types=1);

namespace Ttpryg\CatalogEngine\Events;

use Ttpryg\CatalogEngine\Entities\Product;

class ProductStockUpdatedEvent
{
    public function __construct(
        public readonly Product $product,
        public readonly int $previousStock,
        public readonly int $newStock
    ) {}
}
