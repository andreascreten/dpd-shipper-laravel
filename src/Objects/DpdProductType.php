<?php

namespace Flooris\DpdShipper\Objects;

enum DpdProductType: string
{
    case CLASSIC = 'CL';
    case FRESH = 'FRESH';
    case FREEZE = 'FREEZE';
    case DRY = 'DRY';

    public function hasMinMaxTemperatures(): bool
    {
        return match ($this) {
            self::FRESH, self::FREEZE => true,
            default => false,
        };
    }

    public function getMinimumStorageTemperature(): int
    {
        return match ($this) {
            self::FRESH => 0,
            self::FREEZE => -3000,
            default => throw new \Exception('This product type does not have a minimum storage temperature.'),
        };
    }

    public function getMaximumStorageTemperature(): int
    {
        return match ($this) {
            self::FRESH => 400,
            self::FREEZE => -1800,
            default => throw new \Exception('This product type does not have a maximum storage temperature.'),
        };
    }

    public function getTour(): int
    {
        return match ($this) {
            self::FRESH => 501,
            self::FREEZE => 502,
            self::DRY => 503,
            default => throw new \Exception('This product type does not have a tour.'),
        };
    }

    public function getProductCode(string $countryIso): string
    {
        if($this === self::CLASSIC && $countryIso === "FR") {
            return 'B2B';
        }

        return $this->value;
    }
}
