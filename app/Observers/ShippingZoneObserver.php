<?php

namespace App\Observers;

use App\Models\ShippingZone;
use App\Services\ShippingCalculator;

class ShippingZoneObserver
{
    public function saved(ShippingZone $zone): void
    {
        ShippingCalculator::forget();
    }

    public function deleted(ShippingZone $zone): void
    {
        ShippingCalculator::forget();
    }
}
