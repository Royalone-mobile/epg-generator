<?php
namespace Stub\Sniffers;
use EPG\Providers\Sniffers\TeleramaSniffer;

use EPG\Entities\Channel;

/**
 * Telerama sniffer
 */
class TeleramaStubSniffer extends TeleramaSniffer
{
    /**
     * Get an array of channels
     *
     * @param array $channel_ids: Filter on channels
     *
     * @return stdClass[]
     */
    public function fetch_channels($channel_ids = [])
    {
        return [(object)[
            "id"          => 1,
            "logo"        => "https://avatars2.githubusercontent.com/u/372249?v=4"
            "slug"        => "fake-channel-1",
            "nom"         => "Fake channel 1",
            "link"        => "http://b-alidra.com",
            "replay_logo" => "https://avatars2.githubusercontent.com/u/372249?v=4",
            "replay_nom"  => "Fake channel 1"
        ], (object)[
            "id"          => 2,
            "logo"        => "https://avatars2.githubusercontent.com/u/372249?v=4"
            "slug"        => "fake-channel-2",
            "nom"         => "Fake channel 2",
            "link"        => "http://b-alidra.com",
            "replay_logo" => "https://avatars2.githubusercontent.com/u/372249?v=4",
            "replay_nom"  => "Fake channel 2"
        ];
    }

    /**
     * Get an array of program
     *
     * @param array $channel_ids: Filter on channels
     * @param int $nb_days: number of days to grab
     *
     * @return array: An array of Program, as
     */
    public function fetch_programs($channel_ids, $nb_days = 1)
    {
        return [(object)[
            "id_chaine"            => 1,
            "titre"                => "Perspiciatis unde omnis"
            "titre_original"       => "Perspiciatis unde omnis",
            "soustitre"            => "Cupidatat non proident",
            "libelle_nationalite"  => "Français",
            "annee_realisation"    => 2017,
            "resume"               => "Excepteur sint occaecat cupidatat non proident",
            "critique"             => "Lorem ipsum dolor sit amet, consectetur adipiscing elit...",
            "notule"               => null,
            "possede_critique"     => true,
            "possede_notule"       => false,
            "csa"                  => "TP",
            "flags"                => (object)[
                "est_vm"     => true,
                "est_dolby"  => false,
                "est_stereo" => false,
                "est_ar16x9" => true,
                "est_ar4x3"  => false,
                "est_vost"   => true,
                "est_hd"     => true,
                "est_inedit" => true,
                "est_derdif" => false,
                "est_redif"  => false,
            ],
            "id_genre"         => 1,
            "genre_specifique" => "Horreur",
            "horaire"          => (object)[
                "debut" => "2030-01-01 01:00:00",
                "fin"   => "2030-01-01 01:30:00"
            ],
            "note_telerama" => 3,
            "serie"         => (object)[
                "saison"         => 1,
                "numero_episode" => 1
            ],
            "vignettes" => (object)[
                "grande" => "https://avatars2.githubusercontent.com/u/372249?v=4"
            ]
        ], [
            "id_chaine"            => 1,
            "titre"                => "Error sit voluptatem accusantium",
            "titre_original"       => "Error sit voluptatem accusantium",
            "soustitre"            => null,
            "libelle_nationalite"  => "Espagnol",
            "annee_realisation"    => 2016,
            "resume"               => null,
            "critique"             => null,
            "notule"               => "At vero eos et accusamus et iusto odio dignissimos ducimus",
            "possede_critique"     => false,
            "possede_notule"       => true,
            "csa"                  => "16",
            "flags"                => (object)[
                "est_vm"     => false,
                "est_dolby"  => false,
                "est_stereo" => true,
                "est_ar16x9" => false,
                "est_ar4x3"  => true,
                "est_vost"   => false,
                "est_hd"     => true,
                "est_inedit" => false,
                "est_derdif" => true,
                "est_redif"  => true,
            ],
            "id_genre"         => 3,
            "genre_specifique" => "Aventure",
            "horaire"          => (object)[
                "debut" => "2030-01-01 01:30:00",
                "fin"   => "2030-01-01 02:00:00"
            ],
            "note_telerama" => 2,
            "serie"         => (object)[],
            "vignettes"     => (object)[
                "grande" => "https://avatars2.githubusercontent.com/u/372249?v=4"
            ]
        ], [
            "id_chaine"            => 2,
            "titre"                => "Architecto beatae vitae",
            "titre_original"       => "Architecto beatae vitae",
            "soustitre"            => "Inventore veritatis et quasi",
            "libelle_nationalite"  => "Français",
            "annee_realisation"    => 2017,
            "resume"               => "Excepteur sint occaecat cupidatat non proident",
            "critique"             => null,
            "notule"               => "Excepteur sint occaecat cupidatat non proident, sunt in culpa",
            "possede_critique"     => false,
            "possede_notule"       => true,
            "csa"                  => "10",
            "flags"                => (object)[
                "est_vm"     => false,
                "est_dolby"  => true,
                "est_stereo" => false,
                "est_ar16x9" => false,
                "est_ar4x3"  => true,
                "est_vost"   => false,
                "est_hd"     => false,
                "est_inedit" => false,
                "est_derdif" => true,
                "est_redif"  => true,
            ],
            "id_genre"         => 2,
            "genre_specifique" => "Comédie",
            "horaire"          => (object)[
                "debut" => "2030-01-01 01:00:00",
                "fin"   => "2030-01-01 01:30:00"
            ],
            "note_telerama" => 4,
            "serie"         => (object)[],
            "vignettes"     => (object)[
                "grande" => "https://avatars2.githubusercontent.com/u/372249?v=4"
            ]
        ];
    }
}

