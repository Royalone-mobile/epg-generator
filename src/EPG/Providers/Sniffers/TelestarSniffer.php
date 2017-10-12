<?php
namespace EPG\Providers\Sniffers;

use EPG\Entities\Channel;
use GuzzleHttp\Client;

/**
 * Telestar sniffer
 */
class TelestarSniffer extends Sniffer
{
    const USER_AGENT    = "Telestar/2.9.6 (iPhone; iOS 11.0.1; Scale/3.00)";
    const BASE_URI      = "http://telestar.webwag.com";
    const PACKAGES_URI  = "/packages/index.json";
    const CHANNELS_URI  = "/channels/index.json?package_id=%d";
    const CHANNEL_URI   = "/timeline/channel.json?channel_id=%d&day=%s&package_id=%d";

    /**
     * @var array
     */
    protected $packages;

     /**
      * Get an array of packages, as:
      *
      * class stdClass {
      *    public $id            => int
      *    public $name          => string
      *    public $activated     => bool
      *    public $default       => bool
      *    public $position      => int
      *    public $recordable    => bool
      *    public $third_part_id => int
      * }
      */
    protected function fetch_packages()
     {
        if (!empty($this->channels)) {
            return $this->channels;
        }

         if (empty($this->packages)) {
            $url = sprintf('%s%s', static::BASE_URI, static::PACKAGES_URI);

            $json_response = $this->fetch_json($url);
            $packages      = json_decode($json_response);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($packages)) {
                return [];
            }

            $this->packages = $packages;
         }

        return $this->packages;
     }

    /**
     * Get an array of channels
     *
     * Remote channels have this form:
     *
     * class stdClass {
     *    public $id            => int
     *    public $label         => string
     *    public $recordable    => bool
     *    public $third_part_id => int
     *    public $digimondo_id  => int
     * }
     */
    public function fetch_channels()
    {
        $packages = $this->fetch_packages();

        $this->channels = [];

        foreach ($packages as $package) {
            $url = sprintf(
                sprintf('%s%s', static::BASE_URI, static::CHANNELS_URI),
                $package->id
            );

            $json_response = $this->fetch_json($url);
            $channels      = json_decode($json_response);
            if (json_last_error() !== JSON_ERROR_NONE ||
                empty($channels) ||
                !is_array($channels)) {
                continue;
            }

            // Set the channels
            foreach ($channels as $channel) {
                $channel->package_id          = $package->id;
                $this->channels[$channel->id] = $channel;
            }
        }

        // Filter on selected channels, if any
        if (!empty($this->channel_ids)) {
            $ids = $this->channel_ids;
            $this->channels = array_filter($this->channels, function ($channel) use ($ids) {
                return in_array($channel->id, $ids);
            });
            $this->channels = array_values($this->channels);
        }

        return $this->channels;
    }

    /**
     * Get an array of program
     *
     * @return array: An array of Program, as
     *
     * class stdClass {
     *   public $id                   => int
     *   public $third_part_id        => int
     *   public $title                => string
     *   public $start_at             => string
     *   public $ending_at            => string
     *   public $release_year         => string
     *   public $duration             => int
     *   public $subtitle             => string
     *   public $intro                => string
     *   public $summary              => string
     *   public $category             => string
     *   public $subcategory          => string
     *   public $rating               => int
     *   public $review               => string
     *   public $csa                  => string
     *   public $pt1                  => bool
     *   public $pt2                  => bool
     *   public $pt3                  => bool
     *   public $hd                   => bool
     *   public $vost                 => bool
     *   public $vm                   => bool
     *   public $in_clear             => bool
     *   public $unseen               => bool
     *   public $unseen_in_clear      => bool
     *   public $unseen_encrypted     => bool
     *   public $live                 => bool
     *   public $audiovision          => bool
     *   public $stm                  => bool
     *   public $serie_season         => int
     *   public $serie_episode_number => int
     *   public $serie_episode_name   => string
     *   public $serie_episode_total  => int
     *   public $last_update          => string
     *   public $thematic_id          => int
     *   public $day_id               => int
     *   public $recordable           => bool
     *   public $package_id           => int
     *   public $channel              => class stdClass (see Channel)
     *   public $video => class stdClass {
     *     public $id              => int
     *     public $thumbnail_small => string
     *     public $thumbnail_big   => string
     *     public $url             => string
     *     public $script          => string
     *     public $program_id      => int
     *   }
     *   public $people => array {
     *      class stdClass {
     *          public $id            => int
     *          public $firstname     => string
     *          public $lastname      => string
     *          public $role          => string
     *          public $profession    => string
     *          public $position      => string
     *          public $third_part_id => int
     *          public $program_id    => int
     *     }
     *   }
     *   public $photos => array {
     *     class stdClass {
     *       public $id => int
     *       public $name => string
     *       public $caption => string
     *       public $copyright => string
     *       public $program_id => int
     *     }
     *   }
     *   public $replays => array {
     *     class stdClass {
     *       public $name       => string
     *       public $url        => string
     *       public $catalog    => string
     *       public $publish_at => string
     *       public $expire_at  => string
     *     }
     *  }
     */
    public function fetch_programs()
    {
        if (!empty($this->programs)) {
            return $this->programs;
        }

        $this->fetch_channels();

        $day = new \Datetime();
        for ($i = 0; $i < $this->nb_days; $i++) {
            foreach ($this->channels as $channel) {
                $url = sprintf(
                    sprintf('%s%s', static::BASE_URI, static::CHANNEL_URI),
                    $channel->id,
                    date('Y-m-d'),
                    $channel->package_id
                );

                $json_response = $this->fetch_json($url);
                $timeline      = json_decode($json_response);
                if (json_last_error() !== JSON_ERROR_NONE || !is_array($timeline)) {
                    continue;
                }

                $this->programs = array_merge(
                    $this->programs,
                    $timeline
                );
            }
            $day->add(new \DateInterval('P1D'));
        }

        return $this->programs;
    }
}
