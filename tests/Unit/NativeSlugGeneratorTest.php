<?php

namespace Ttpryg\CatalogEngine\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Ttpryg\CatalogEngine\Utilities\NativeSlugGenerator;

class NativeSlugGeneratorTest extends TestCase
{
    private NativeSlugGenerator $generator;

    protected function setUp(): void
    {
        $this->generator = new NativeSlugGenerator;
    }

    // POSITIVE CASE
    public function test_generate_slug_from_product_name(): void
    {
        $name = 'Sepatu Lari Nike Air Zoom 2026';
        $slug = $this->generator->generate($name);

        $this->assertEquals('sepatu-lari-nike-air-zoom-2026', $slug);
    }

    // NEGATIVE CASE: Fallback on empty/invalid characters
    public function test_generate_slug_fallback(): void
    {
        $slug = $this->generator->generate('???');
        $this->assertEquals('n-a', $slug);
    }
}
