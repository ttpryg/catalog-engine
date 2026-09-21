<?php

declare(strict_types=1);

namespace Ttpryg\CatalogEngine\Events;

use Ttpryg\CatalogEngine\Entities\Product;

class ProductPriceChangedEvent
{
    public function __construct(
        public readonly Product $product,
        public readonly float $oldPrice,
        public readonly float $newPrice
    ) {}
}
