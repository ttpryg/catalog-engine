<?php

declare(strict_types=1);

namespace Ttpryg\CatalogEngine\Exceptions;

class VariantNotFoundException extends CatalogEngineException
{
    public static function byId(int|string $id): self
    {
        return new self("Product variant with ID '{$id}' was not found.");
    }

    public static function bySku(string $sku): self
    {
        return new self("Product variant with SKU '{$sku}' was not found.");
    }
}
