<?php

namespace Phplc\Core\System;

use Phplc\Core\Config;

class ChangeTrackingStorage
{
    /** 
     * @var array<string, scalar> 
     */
    private array $values = [];

    private int $decimalPlaces;

    public function __construct(Config $config)
    {
        $this->decimalPlaces = $config->decimalPlacesForChangeTracking;
    }

    public function valueIsChanged(string $name, bool|int|float|string $value): bool
    {
        $newValue = is_float($value) ? round($value, $this->decimalPlaces) : $value;

        if (!isset($this->values[$name])) {
            $this->values[$name] = $newValue;
            return true;
        }

        if ($newValue === $this->values[$name]) {
            return false;
        }

        $this->values[$name] = $newValue;
        return true;
    }

}
