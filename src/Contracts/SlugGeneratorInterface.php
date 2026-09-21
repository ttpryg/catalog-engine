<?php

declare(strict_types=1);

namespace Ttpryg\CatalogEngine\Contracts;

interface SlugGeneratorInterface
{
    public function generate(string $title): string;
}
