<?php
namespace EPG\Providers;

use GuzzleHttp\Client;
use XMLTV\Xmltv;

/**
 * Telestar provider
 */
class TelestarProvider
{
    const USER_AGENT    = "Telestar/2.9.6 (iPhone; iOS 11.0.1; Scale/3.00)";
    const BASE_URI      = "http://telestar.webwag.com";
    const PACKAGES_URI  = "/packages/index.json";
    const CHANNELS_URI  = "/channels/index.json?package_id=%d";
    const CHANNEL_URI   = "/timeline/channel.json?channel_id=%d&day=%s&package_id=%d";

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
    public function fetch_packages()
    {
        $url = sprintf('%s%s', static::BASE_URI, static::PACKAGES_URI);

        $json_response = $this->fetch_json($url);
        $packages      = json_decode($json_response);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($packages)) {
            return [];
        }

        return $packages;
    }

    /**
     * Get an array of channels, as:
     *
     * @param int $package_id
     *
     * class stdClass {
     *    public $id            => int
     *    public $label         => string
     *    public $recordable    => bool
     *    public $third_part_id => int
     *    public $digimondo_id  => int
     * }
     */
    public function fetch_channels($package_id)
    {
        $url = sprintf(
            sprintf('%s%s', static::BASE_URI, static::CHANNELS_URI),
            $package_id
        );

        $json_response = $this->fetch_json($url);
        $channels      = json_decode($json_response);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($channels)) {
            return [];
        }

        return $channels;
    }

    /**
     * Get an array of program
     *
     * @param int $package_id
     * @param int $channel_id
     * @param int $nb_days
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
     $     public $url             => string
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
    public function fetch_channel_programs($package_id, $channel_id, $nb_days = 1)
    {
        $url = sprintf(
            sprintf('%s%s', static::BASE_URI, static::CHANNEL_URI),
            $channel_id,
            date('Y-m-d'),
            $package_id
        );

        $json_response = $this->fetch_json($url);
        $timeline      = json_decode($json_response);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($timeline)) {
            return [];
        }

        return $timeline;
    }

    /**
     * Add a channel into the XMLTV feed
     *
     * @param XMLTV\Xmltv $xmltv: The XMLTV feed
     * @param object $epg_channel: A channel, as provided by fetch_channels()
     */
    public function add_xmltv_channel(Xmltv &$xmltv, $epg_channel)
    {
        $xmltv->addChannel(function (&$channel) use ($epg_channel) {
            $channel
                ->setId($epg_channel->label)
                ->addDisplayname(['lang' => 'fr'], $epg_channel->label);
        });
    }

    /**
     * Add a program into the XMLTV feed
     *
     * @param XMLTV\Xmltv $xmltv: The XMLTV feed
     * @param object $epg_program: A program, as provided by fetch_channel_programs()
     */
    public function add_xmltv_program(&$xmltv, $epg_channel, $epg_program)
    {
        $xmltv->addProgramme([
            'channel'          => $epg_channel->label,
            'start'            => gmdate("YmdHis", strtotime($epg_program->start_at)),
            'stop'             => gmdate("YmdHis", strtotime($epg_program->ending_at)),
            'pdc-start'        => gmdate("YmdHis", strtotime($epg_program->start_at)),
            'vps-start'        => gmdate("YmdHis", strtotime($epg_program->start_at))
        ], function (&$program) use ($epg_program) {
            $program
                ->addTitle(['lang' => 'fr'], $epg_program->title)
                ->addSubtitle(['lang' => 'fr'], $epg_program->subtitle)
                ->addDesc(['lang' => 'fr'], $epg_program->summary)
                ->addCategory(['lang' => 'fr'], $epg_program->category)
                ->addLength(['units' => 'minutes'],$epg_program->duration)
                ->addDate(gmdate("Ymd", strtotime($epg_program->start_at)));

            if (!empty($epg_program->people)) {
                $program->addCredits(function (&$credits) use ($epg_program) {
                    foreach ($epg_program->people as $p) {
                        $fullname = $p->firstname.' '.$p->lastname;
                        switch ($p->profession) {
                            case 'Acteur':
                            case 'Soliste':
                            case 'Interprète':
                                $credits->addActor($fullname);
                                break;
                            case 'Scénarite':
                            case 'Scénario':
                            case 'Scénariste':
                            case 'Origine Scénario':
                            case 'Dialogue':
                                $credits->addWriter($fullname);
                                break;
                            case 'Auteur':
                                $credits->addEditor($fullname);
                                break;
                            case 'Musique':
                            case 'Compositeur':
                                $credits->addComposer($fullname);
                                break;
                            case 'Invité':
                            case 'Guest star':
                            case 'Invité vedette':
                            case 'Autre Invité':
                                $credits->addGuest($fullname);
                                break;
                            case 'Présentateur':
                            case 'Présentateur vedette':
                            case 'Autre présentateur':
                                $credits->addPresenter($fullname);
                                break;
                            case 'Producteur':
                                $credits->addProducer($fullname);
                                break;
                            case 'Réalisateur':
                            case 'Metteur en scène':
                                $credits->addDirector($fullname);
                                break;
                            case 'Commentateur':
                            case 'Voix Off VF':
                            case 'Voix Off VO':
                                $credits->addCommentator($fullname);
                                break;
                            case 'Chef d\'orchestre':
                            case 'Chorégraphe':
                            case 'Compagnie':
                            case 'Danseur':
                            case 'Image':
                            case 'Décors':
                                break;
                            default:
                                No2_Logger::info(sprintf("Unhandled profession %s", $p->profession));
                        }
                    }
                });
            }
        });
    }

    protected function fetch_json($url)
    {
        $client = new Client([
            'base_uri' => static::BASE_URI,
            'verify'   => false,
            'headers'  => [
                'User-Agent'      => static::USER_AGENT,
                'Accept'          => '*/*',
                'Accept-Language' => 'fr-fr'
            ]
        ]);

        $response = $client->request('GET', $url);

        return $response->getBody();
    }
}
