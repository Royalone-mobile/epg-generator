<?php
namespace EPG\Providers;

use EPG\Entities\Channel;
use EPG\Providers\Sniffers\Sniffer;
use XMLTV\Xmltv;

/**
 * Telestar provider
 */
class TelestarProvider extends Provider
{
    /**
     * Constructor
     */
    public function __construct(Sniffer $sniffer)
    {
        parent::__construct($sniffer);

        $this->channel_id_prefix = "C";
        $this->channel_id_suffix = ".telestar.fr";
    }

    /**
     * Get the channels
     *
     * @return EPG\Entities\Channel[]
     */
    public function get_channels()
    {
        $channels = $this->sniffer->fetch_channels();

        return array_map(function ($epg_channel) {
            $channel = new Channel();

            $channel->id = sprintf(
                "%s%s%s",
                $this->channel_id_prefix,
                $epg_channel->id,
                $this->channel_id_suffix
            );
            $channel->displayName = $epg_channel->label;

            return $channel;
        }, $channels);
    }

    /**
     * Add all channels into the XMLTV feed
     *
     * @param XMLTV\Xmltv $xmltv: The XMLTV feed
     *
     * @return TeleramaProvider
     */
    public function add_channels_to(Xmltv &$xmltv)
    {
        $channels = $this->get_channels();

        foreach ($channels as $epg_channel) {
            $xmltv->addChannel(function (&$channel) use ($epg_channel) {
                $channel
                    ->setId($epg_channel->id)
                    ->addDisplayname(['lang' => 'fr'], $epg_channel->displayName);
            });
        }

        return $this;
    }

    /**
     * Add all programs into the XMLTV feed
     *
     * @param XMLTV\Xmltv $xmltv: The XMLTV feed
     *
     * @return TelestarProvider
     */
    public function add_programs_to(&$xmltv)
    {
        $programs = $this->sniffer->fetch_programs();

        foreach ($programs as $epg_program) {
            $xmltv->addProgramme([
                'channel'          => sprintf(
                    "%s%s%s",
                    $this->channel_id_prefix,
                    $epg_program->channel->id,
                    $this->channel_id_suffix
                ),
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

        return $this;
    }
}
