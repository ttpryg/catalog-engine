<?php

namespace Ttpryg\CatalogEngine\ValueObjects;

use InvalidArgumentException;

class Price
{
    public function __construct(
        public readonly float $amount,
        public readonly ?float $saleAmount = null,
        public readonly ?float $costAmount = null,
        public readonly string $currency = 'IDR'
    ) {
        if ($amount < 0) {
            throw new InvalidArgumentException('Product price cannot be negative.');
        }
        if ($saleAmount !== null && $saleAmount < 0) {
            throw new InvalidArgumentException('Sale price cannot be negative.');
        }
    }

    public function getEffectivePrice(): float
    {
        return ($this->saleAmount !== null && $this->saleAmount < $this->amount)
            ? $this->saleAmount
            : $this->amount;
    }

    public function isOnSale(): bool
    {
        return $this->saleAmount !== null && $this->saleAmount < $this->amount;
    }

    public function getDiscountPercentage(): float
    {
        if (! $this->isOnSale() || $this->amount == 0) {
            return 0.0;
        }

        return round((($this->amount - $this->saleAmount) / $this->amount) * 100, 2);
    }

    public function format(string $symbol = 'Rp ', int $decimals = 0): string
    {
        return $symbol.number_format($this->getEffectivePrice(), $decimals, ',', '.');
    }
}
