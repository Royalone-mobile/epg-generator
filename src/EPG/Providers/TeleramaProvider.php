<?php
namespace EPG\Providers;

use EPG\Entities\Channel;
use EPG\Providers\Sniffers\Sniffer;
use XMLTV\Xmltv;

/**
 * Telerama provider
 */
class TeleramaProvider extends Provider
{
    /**
     * Constructor
     */
    public function __construct(Sniffer $sniffer)
    {
        parent::__construct($sniffer);

        $this->channel_id_prefix = "C";
        $this->channel_id_suffix = ".telerama.fr";
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
            $channel->displayName = $epg_channel->nom;
            $channel->icon        = $epg_channel->logo;
            $channel->url         = $epg_channel->link;

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
                    ->addDisplayname(['lang' => 'fr'], $epg_channel->displayName)
                    ->addIcon(['src' => $epg_channel->icon]);
            });
        }

        return $this;
    }

    /**
     * Add all programs into the XMLTV feed
     *
     * @param XMLTV\Xmltv $xmltv: The XMLTV feed
     *
     * @return TeleramaProvider
     */
    public function add_programs_to(&$xmltv)
    {
        $programs = $this->sniffer->fetch_programs();

        foreach ($programs as $epg_program) {
            $xmltv->addProgramme([
                'channel'          => sprintf(
                    "%s%s%s",
                    $this->channel_id_prefix,
                    $epg_program->id_chaine,
                    $this->channel_id_suffix
                ),
                'start'            => gmdate("YmdHis", strtotime($epg_program->horaire->debut)),
                'stop'             => gmdate("YmdHis", strtotime($epg_program->horaire->fin)),
                'pdc-start'        => gmdate("YmdHis", strtotime($epg_program->horaire->debut)),
                'vps-start'        => gmdate("YmdHis", strtotime($epg_program->horaire->debut))
            ], function (&$program) use ($epg_program) {
                $description = $epg_program->resume;

                $critic = $epg_program->possede_critique ? $epg_program->critique :
                         ($epg_program->possede_notule ? $epg_program->notule : "");

                if (!empty($critic)) {
                    $description .= ' - Critique: '.$critic;
                }

                $season  = isset($epg_program->serie->saison) ? $epg_program->serie->saison : "";
                $episode = isset($epg_program->serie->numero_episode) ? $epg_program->serie->numero_episode : "";
                if (!empty($episode)) {
                    $description = "Épisode: ".$episode." ".$description;
                }
                if (!empty($season)) {
                    $description = "Saison: ".$season." ".$description;
                }

                $genre = $epg_program->id_genre ? $this->sniffer->genre($epg_program->id_genre)->libelle : "";
                if (empty($genre)) {
                    $genre = $epg_program->genre_specifique;
                }

                $start = new \Datetime($epg_program->horaire->debut);
                $end   = new \Datetime($epg_program->horaire->fin);
                $duration_in_minutes = $start->diff($end)->i;

                $date = $epg_program->annee_realisation;

                $program
                    ->addTitle(['lang' => 'fr'], $epg_program->titre)
                    ->addSubtitle(['lang' => 'fr'], $epg_program->soustitre)
                    ->addDesc(['lang' => 'fr'], $description)
                    ->addCategory(['lang' => 'fr'], $genre)
                    ->addLength(['units' => 'minutes'],$duration_in_minutes);

                if (isset($epg_program->vignettes) && !empty($epg_program->vignettes->grande)) {
                    $program->addIcon(['src' => $epg_program->vignettes->grande]);
                }

                if (isset($epg_program->title->titre_original)) {
                    $program->addTitleOrig($epg_program->title->titre_original);
                }

                if (isset($epg_program->annee_realisation)) {
                    $program->addDate($epg_program->annee_realisation);
                }

                if (isset($epg_program->libelle_nationalite)) {
                    $program->addCountry($epg_program->libelle_nationalite);
                }

                if ((isset($epg_program->flags->est_ar16x9) && $epg_program->flags->est_ar16x9) ||
                    (isset($epg_program->flags->est_ar4x3) && $epg_program->flags->est_ar4x3)) {
                    $program->addVideo(function (&$video) use ($epg_program) {
                        if (isset($epg_program->flags->est_ar4x3) && $epg_program->flags->est_ar4x3) {
                            $video->addAspect('4:3');
                        } else {
                            $video->addAspect('16:9');
                        }
                        if (isset($epg_program->flags->est_hd) && $epg_program->flags->est_hd) {
                            $video->addQuality('HDTV');
                        }
                    });
                }

                if ((isset($epg_program->flags->est_vm) && $epg_program->flags->est_vm) ||
                    (isset($epg_program->flags->est_stereo) && $epg_program->flags->est_stereo)||
                    (isset($epg_program->flags->est_dolby) && $epg_program->flags->est_dolby)) {

                    $program->addAudio(function (&$audio) use ($epg_program) {
                        if (isset($epg_program->flags->est_vm) && $epg_program->flags->est_vm) {
                            $audio->addStereo('bilingual');
                        } elseif (isset($epg_program->flags->est_stereo) && $epg_program->flags->est_stereo) {
                            $audio->addStereo('stereo');
                        } elseif (isset($epg_program->flags->est_dolby) && $epg_program->flags->est_dolby) {
                            $audio->addStereo('dolby');
                        }
                    });
                }

                if (isset($epg_program->flags->est_vost) && $epg_program->flags->est_vost) {
                    $program->addSubtitles(['type' => 'onscreen'], function (&$subtitles) {
                        $subtitles->addLanguage(['lang' => 'fr']);
                    });
                }

                if (isset($epg_program->flags->est_redif) && $epg_program->flags->est_redif) {
                    $program->addPreviouslyshown();
                }

                if (isset($epg_program->flags->est_inedit) && $epg_program->flags->est_inedit) {
                    $program->addNew();
                }

                if (isset($epg_program->flags->est_derdif) && $epg_program->flags->est_derdif) {
                    $program->addLastchance();
                }

                $rating_value = 0;
                $rating_icon  = "";
                if (isset($epg_program->csa) && !empty($epg_program->csa)) {
                    switch ($epg_program->csa) {
                        case 10:
                            $rating_icon = 'http://upload.wikimedia.org/wikipedia/commons/thumb/b/bf/Moins10.svg/200px-Moins10.svg.png';
                            $rating_value = -10;
                            break;
                        case 12:
                            $rating_icon = 'http://upload.wikimedia.org/wikipedia/commons/thumb/b/bf/Moins10.svg/200px-Moins12.svg.png';
                            $rating_value = -12;
                            break;
                        case 16:
                            $rating_icon = 'http://upload.wikimedia.org/wikipedia/commons/thumb/b/bf/Moins10.svg/200px-Moins16.svg.png';
                            $rating_value = -16;
                            break;
                        case 18:
                            $rating_icon = 'http://upload.wikimedia.org/wikipedia/commons/thumb/b/bf/Moins10.svg/200px-Moins18.svg.png';
                            $rating_value = -18;
                            break;
                        case 'TP':
                            $rating_value = "Tout public";
                        default:
                    }
                    $program->addRating(
                        ['system' => 'CSA'],
                        function (&$rating) use ($rating_icon, $rating_value){
                            $rating->addValue($rating_value);
                            if (!empty($rating_icon)) {
                                $rating->addIcon(['src' => $rating_icon]);
                            }
                        }
                    );
                }

                $star_rating = $epg_program->note_telerama;
                if ($star_rating) {
                    $program->addStarrating(
                        ['system' => 'Télérama'],
                        function (&$rating) use ($star_rating){
                            $rating->addValue(sprintf("%d/5", $star_rating));
                        }
                    );
                }

                if (isset($epg_program->intervenants) && !empty($epg_program->intervenants)) {
                    $program->addCredits(function (&$credits) use ($epg_program) {
                        foreach ($epg_program->intervenants as $intervenant) {
                            $fullname = $intervenant->prenom.' '.$intervenant->nom;
                            if (!empty($intervenant->role)) {
                                $fullname .= ' ('.$intervenant->role.')';
                            }
                            $type = $intervenant->libelle;
                            if (preg_match('/Acteur/', $type) ||
                                preg_match('/Interpr.+te/', $type)) {
                                    $credits->addActor($fullname);
                            } elseif (preg_match('/R.+alisateur/', $type) ||
                                      preg_match('/Metteur en Sc.+ne/', $type)) {
                                    $credits->addDirector($fullname);
                            } elseif (preg_match('/[Pp+]r.+sentateur/', $type)) {
                                    $credits->addPresenter($fullname);
                            } elseif (preg_match('/Musique/', $type)) {
                                    $credits->addComposer($fullname);
                            } elseif (preg_match('/Cr.+ateu/', $type) ||
                                      preg_match('/Auteur/', $type) ||
                                      preg_match('/Sc.+nariste/', $type) ||
                                      preg_match('/Sc.+nario/', $type) ||
                                      preg_match('/Dialogue/', $type)) {
                                    $credits->addWriter($fullname);
                            } else {
                                    $credits->addGuest($fullname);
                            }
                        }
                    });
                }
            });
        }

        return $this;
    }
}
