<?php

namespace Ttpryg\CatalogEngine\Exceptions;

class CategoryNotFoundException extends CatalogEngineException
{
    public static function byId(int|string $id): self
    {
        return new self("Product category with ID '{$id}' was not found.");
    }
}
