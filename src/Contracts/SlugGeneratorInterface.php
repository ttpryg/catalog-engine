<?php

namespace Ttpryg\CatalogEngine\Contracts;

interface SlugGeneratorInterface
{
    public function generate(string $title): string;
}
