<?php

namespace Ttpryg\CatalogEngine\Exceptions;

class VariantNotFoundException extends CatalogEngineException
{
    public static function byId(int|string $id): self
    {
        return new self("Product variant with ID '{$id}' was not found.");
    }
}
