<?php

namespace Ttpryg\CatalogEngine\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Ttpryg\CatalogEngine\Utilities\NativeSlugGenerator;

class NativeSlugGeneratorTest extends TestCase
{
    private NativeSlugGenerator $nativeSlugGenerator;

    protected function setUp(): void
    {
        $this->nativeSlugGenerator = new NativeSlugGenerator;
    }

    // POSITIVE CASE
    public function test_generate_slug_from_product_name(): void
    {
        $name = 'Sepatu Lari Nike Air Zoom 2026';
        $slug = $this->nativeSlugGenerator->generate($name);

        $this->assertEquals('sepatu-lari-nike-air-zoom-2026', $slug);
    }

    // NEGATIVE CASE: Fallback on empty/invalid characters
    public function test_generate_slug_fallback(): void
    {
        $slug = $this->nativeSlugGenerator->generate('???');
        $this->assertEquals('n-a', $slug);
    }
}
