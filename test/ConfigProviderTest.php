<?php

declare(strict_types=1);

namespace DotTest\ResponseHeader;

use Dot\ResponseHeader\ConfigProvider;
use PHPUnit\Framework\TestCase;

class ConfigProviderTest extends TestCase
{
    private ConfigProvider $configProvider;

    public function setUp(): void
    {
        $this->configProvider = new ConfigProvider();
    }

    public function testInvoke()
    {
        $data = $this->configProvider->__invoke();

        $this->assertIsArray($data);
    }

    public function testGetDependencies()
    {
        $data = $this->configProvider->getDependencies();

        $this->assertIsArray($data);
    }
}
