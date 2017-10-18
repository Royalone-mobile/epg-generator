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
     * @var int[]
     */
    protected $channel_ids;

    /**
     * @var int
     */
    protected $nb_days;

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

        $this->channel_ids       = [];
        $this->nb_days           = 1;
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

    /**
     * Filter the channels to fetch by their id
     *
     * @param array channel_ids: An array of channel ids
     *
     * @return Sniffer
     */
    public function filter_on_channels(array $channel_ids)
    {
        $mask = sprintf('/%s(.*)%s/', $this->channel_id_prefix, $this->channel_id_suffix);
        $channel_ids = array_map(function ($id) use ($mask) {
            if (preg_match($mask, $id, $matches)) {
                $id = $matches[1];
            }
            return $id;
        }, $channel_ids);

        $this->channel_ids = $channel_ids;

        return $this;
    }

    /**
     * Set the number of days to grab
     *
     * @param int $nb_days
     *
     * @return Sniffer
     */
    public function set_days_to_grab($nb_days)
    {
        $this->nb_days = $nb_days;

        return $this;
    }
}
