<?php

namespace Ttpryg\CatalogEngine\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Ttpryg\CatalogEngine\ValueObjects\Price;

class PriceValueObjectTest extends TestCase
{
    // POSITIVE CASE: Normal price calculation & discount percentage
    public function testPriceCalculationAndDiscount(): void
    {
        $price = new Price(amount: 100000.0, saleAmount: 80000.0);

        $this->assertEquals(100000.0, $price->amount);
        $this->assertEquals(80000.0, $price->getEffectivePrice());
        $this->assertTrue($price->isOnSale());
        $this->assertEquals(20.0, $price->getDiscountPercentage());
        $this->assertEquals("Rp 80.000", $price->format());
    }

    // NEGATIVE CASE: Negative Price Throws InvalidArgumentException
    public function testNegativePriceThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Price(amount: -50000.0);
    }
}
