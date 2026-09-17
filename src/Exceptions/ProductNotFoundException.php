<?php

namespace Ttpryg\CatalogEngine\Exceptions;

class ProductNotFoundException extends CatalogEngineException
{
    public static function byId(int|string $id): self
    {
        return new self("Product with ID '{$id}' was not found.");
    }

    public static function bySlug(string $slug): self
    {
        return new self("Product with slug '{$slug}' was not found.");
    }

    public static function bySku(string $sku): self
    {
        return new self("Product with SKU '{$sku}' was not found.");
    }
}
