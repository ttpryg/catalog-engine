<?php

namespace Ttpryg\CatalogEngine\Utilities;

use Ttpryg\CatalogEngine\Contracts\SlugGeneratorInterface;

class NativeSlugGenerator implements SlugGeneratorInterface
{
    public function generate(string $title): string
    {
        $slug = mb_strtolower($title, 'UTF-8');
        $slug = preg_replace('~[^\pL\d]+~u', '-', $slug);
        $slug = iconv('utf-8', 'us-ascii//TRANSLIT', $slug) ?: $slug;
        $slug = preg_replace('~[^\-\w]+~', '', $slug);
        $slug = trim($slug, '-');
        $slug = preg_replace('~-+~', '-', $slug);

        return empty($slug) ? 'n-a' : $slug;
    }
}
