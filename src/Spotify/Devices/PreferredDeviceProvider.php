<?php

declare(strict_types = 1);

namespace App\Spotify\Devices;

class PreferredDeviceProvider
{
    public function getPreferredDevice(array $devices): ?object
    {
        if (count($devices) === 0) {
            return null;
        }

        usort(
            $devices,
            function (object $a, object $b) {
                return $b->is_active <=> $a->is_active;
            }
        );

        return $devices[0];
    }
}