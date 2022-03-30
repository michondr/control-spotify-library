<?php

declare(strict_types = 1);

namespace App\Tests\Spotify\Devices;

use App\Spotify\Devices\PreferredDeviceProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class PreferredDeviceProviderTest extends TestCase
{

    private PreferredDeviceProvider $preferredDeviceProvider;

    protected function setUp(): void
    {
        $this->preferredDeviceProvider = new PreferredDeviceProvider();
    }

    /**
     * @dataProvider provideDataForGetPreferredDevice
     */
    public function testGetPreferredDevice(array $allDevicesAvailable, ?object $expectedPreferredDevice)
    {
        self::assertSame(
            $expectedPreferredDevice,
            $this->preferredDeviceProvider->getPreferredDevice($allDevicesAvailable)
        );
    }

    public function provideDataForGetPreferredDevice()
    {
        $activeTablet = $this->createDevice('tablet', true);

        $inactivePhone = $this->createDevice('phone', false);
        $inactiveTablet = $this->createDevice('tablet', false);
        $inactiveComputer = $this->createDevice('computer', false);

        return [
            'no device available, returns null' => [
                [],
                null,
            ],
            'single inactive device, it is returned' => [
                [$inactivePhone],
                $inactivePhone,
            ],
            'single active device, it is returned' => [
                [$activeTablet],
                $activeTablet,
            ],
            'multiple inactive devices, first is returned' => [
                [$inactiveComputer, $inactivePhone, $inactiveTablet],
                $inactiveComputer,
            ],
            'inactive and active devices, first active is returned' => [
                [$inactiveComputer, $activeTablet, $inactivePhone, $inactiveTablet],
                $activeTablet,
            ],
        ];
    }

    private function createDevice(string $name, bool $isActive): object
    {
        $device = new \StdClass();

        $device->id = Uuid::v4();
        $device->is_active = $isActive;
        $device->is_private_session = false;
        $device->is_restricted = false;
        $device->name = $name;
        $device->type = $name;
        $device->volume_percent = 100;

        return $device;
    }

}