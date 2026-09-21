<?php

declare(strict_types=1);

namespace Ttpryg\CatalogEngine\Events;

use Ttpryg\CatalogEngine\Entities\Product;

class ProductCreatedEvent
{
    public function __construct(
        public readonly Product $product
    ) {}
}
