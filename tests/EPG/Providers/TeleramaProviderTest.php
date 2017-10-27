<?php

use PHPUnit\Framework\TestCase;

use EPG\Providers\TeleramaProvider;
use Stub\Sniffers\TeleramaStubSniffer;
use PHPUnit\Util\Xml;

class TeleramaProviderTest extends TestCase
{
    /**
     * @var TeleramaProvider
     */
    protected $provider;

    protected function setUp()
    {
        $this->provider = new TeleramaProvider(new TeleramaStubSniffer());
    }

    public function testGetChannels()
    {
        $channels = $this->provider->get_channels();
        var_dump($channels);
    }
}

