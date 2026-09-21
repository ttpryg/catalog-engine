<?php

declare(strict_types=1);

namespace Ttpryg\CatalogEngine\ValueObjects;

enum ProductStatus: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case ARCHIVED = 'archived';

    public static function isValid(string $status): bool
    {
        return self::tryFrom($status) !== null;
    }
}
