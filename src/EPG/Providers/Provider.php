<?php
namespace EPG\Providers;

use EPG\Providers\Sniffers\Sniffer;
use XMLTV\Xmltv;

abstract class Provider
{
    const TELERAMA_PROVIDER = "telerama";
    const TELESTAR_PROVIDER = "telestar";

    /*
     * @var Sniffer
     */
    protected $sniffer;

    /**
     * @var string
     */
    protected $channel_id_prefix;

    /**
     * @var string
     */
    protected $channel_id_suffix;

    /**
     * Constructor
     */
    public function __construct(Sniffer $sniffer)
    {
        $this->sniffer = $sniffer;

        $this->channel_id_prefix = "";
        $this->channel_id_suffix = "";
    }

    /**
     * Get the channels
     *
     * @return EPG\Entities\Channel[]
     */
    public abstract function get_channels();

    /**
     * Add all channels into the XMLTV feed
     *
     * @param XMLTV\Xmltv $xmltv: The XMLTV feed
     *
     * @return TeleramaProvider
     */
    public abstract function add_channels_to(Xmltv &$xmltv);

    /**
     * Add all programs into the XMLTV feed
     *
     * @param XMLTV\Xmltv $xmltv: The XMLTV feed
     *
     * @return TeleramaProvider
     */
    public abstract function add_programs_to(&$xmltv);
}
