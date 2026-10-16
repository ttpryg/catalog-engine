<?php

declare(strict_types=1);

namespace Ttpryg\CatalogEngine\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Ttpryg\CatalogEngine\ValueObjects\Dimensions;

class DimensionsValueObjectTest extends TestCase
{
    public function test_dimensions_instantiation_and_volume_calculation(): void
    {
        $dimensions = new Dimensions(lengthCm: 20.0, widthCm: 15.0, heightCm: 10.0);

        $this->assertEquals(20.0, $dimensions->lengthCm);
        $this->assertEquals(15.0, $dimensions->widthCm);
        $this->assertEquals(10.0, $dimensions->heightCm);
        $this->assertEquals(3000.0, $dimensions->getVolumeCubicCm());
    }

    public function test_from_array_and_to_array(): void
    {
        $data = ['length' => 12.5, 'width' => 8.0, 'height' => 4.0];
        $dimensions = Dimensions::fromArray($data);

        $this->assertNotNull($dimensions);
        $this->assertEquals(12.5, $dimensions->lengthCm);
        $this->assertEquals($data, $dimensions->toArray());

        $this->assertNull(Dimensions::fromArray(data: null));
        $this->assertNull(Dimensions::fromArray([]));
    }

    public function test_negative_dimensions_throw_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Dimensions(lengthCm: -5.0, widthCm: 10.0, heightCm: 10.0);
    }
}
