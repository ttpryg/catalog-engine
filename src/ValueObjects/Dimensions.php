<?php

namespace Ttpryg\CatalogEngine\ValueObjects;

class Dimensions
{
    public function __construct(
        public readonly float $lengthCm = 0.0,
        public readonly float $widthCm = 0.0,
        public readonly float $heightCm = 0.0
    ) {}

    public static function fromArray(?array $data): ?self
    {
        if (empty($data)) {
            return null;
        }

        return new self(
            lengthCm: (float) ($data['length'] ?? 0.0),
            widthCm: (float) ($data['width'] ?? 0.0),
            heightCm: (float) ($data['height'] ?? 0.0)
        );
    }

    public function toArray(): array
    {
        return [
            'length' => $this->lengthCm,
            'width' => $this->widthCm,
            'height' => $this->heightCm,
        ];
    }

    public function getVolumeCubicCm(): float
    {
        return $this->lengthCm * $this->widthCm * $this->heightCm;
    }
}
