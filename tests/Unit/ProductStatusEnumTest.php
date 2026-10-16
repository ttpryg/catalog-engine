<?php

declare(strict_types=1);

namespace Ttpryg\CatalogEngine\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Ttpryg\CatalogEngine\ValueObjects\ProductStatus;

class ProductStatusEnumTest extends TestCase
{
    public function test_enum_cases(): void
    {
        $this->assertEquals('draft', ProductStatus::DRAFT->value);
        $this->assertEquals('active', ProductStatus::ACTIVE->value);
        $this->assertEquals('inactive', ProductStatus::INACTIVE->value);
        $this->assertEquals('archived', ProductStatus::ARCHIVED->value);
    }

    public function test_is_valid(): void
    {
        $this->assertTrue(ProductStatus::isValid('draft'));
        $this->assertTrue(ProductStatus::isValid('active'));
        $this->assertTrue(ProductStatus::isValid('inactive'));
        $this->assertTrue(ProductStatus::isValid('archived'));
        $this->assertFalse(ProductStatus::isValid('invalid_status'));
    }
}
