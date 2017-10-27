<?php
namespace EPG\Providers\Sniffers;

/**
 * Telerama sniffer
 */
class TeleramaSniffer extends Sniffer
{
    const USER_AGENT        = "okhttp/3.2.0";
    const BASE_URI          = "https://api.telerama.fr";
    const CHANNEL_GRID      = '/v1/application/initialisation';
    const CHANNEL_GRID_PAGE = "/v1/programmes/telechargement";

    const SIGNATURE_PARAM   = "api_signature";
    const API_KEY_PARAM     = "api_cle";
    const API_KEY_VALUE     = "apitel-5304b49c90511";
    const DEVICE_PARAM      = "appareil";
    const DEVICE_VALUE      = "android_tablette";
    const SIGNATURE_SALT    = "Eufea9cuweuHeif";
    const HASH_ALGO         = "sha1";

    const GRID_DATE_PARAM   = "dates";
    const GRID_IDS_PARAM    = "id_chaines";
    const GRID_PAGE_PARAM   = "nb_par_page";
    const GRID_PAGE_VALUE   = 100;

    /**
     * Array of genre objects
     *
     * @var array
     */
    protected static $genres;

    /**
     * Get an array of channels
     *
     * Remote channels have this form:
     *
     *
     * @param array $channel_ids: Filter on channels
     *
     * @return stdClass[]: an array of channels, as
     *
     * class stdClass {
     *     public $id           => int
     *     public $logo         => string
     *     public $slug         => string
     *     public $nom          => string
     *     public $link         => string
     *     public $replay_logo  => string
     *     public $replay_nom   => string
     * }
     */
    public function fetch_channels($channel_ids = [])
    {
        if (!empty($this->channels)) {
            return $this->channels;
        }

        $url = $this->build_url(static::CHANNEL_GRID);

        $json_response = $this->fetch_json($url);
        $data          = json_decode($json_response);
        if (json_last_error() !== JSON_ERROR_NONE ||
            !isset($data->donnees) ||
            !is_array($data->donnees->chaines)) {
            return ($this->channels = []);
        }

        // Set the channels
        $this->channels = $data->donnees->chaines;

        // Filter on selected channels, if any
        if (!empty($channel_ids)) {
            $this->channels = array_filter($this->channels,
                function ($channel) use ($channel_ids) {
                    return in_array($channel->id, $channel_ids);
                }
            );
            $this->channels = array_values($this->channels);
        }

        // Grab the genres by the way
        static::$genres = $data->donnees->genres;

        return $this->channels;
    }

    /**
     * Get an array of program
     *
     * @param array $channel_ids: Filter on channels
     * @param int $nb_days: number of days to grab
     *
     * @return stdClass[]: An array of Program, as
     *
     * class stdClass {
     *   annee_realisation => int
     *   bandes_annonces => array {
     *   commentaires => object {
     *     note => int
     *     nb_notes => int
     *     nb_commentaires => int
     *   }
     *   critique => string
     *   csa => string
     *   csa_full => array {
     *     object {
     *       id_csa => string
     *       nom_court => string
     *       nom_long => string
     *     }
     *   }
     *   flags => object {
     *     est_clair => bool
     *     est_dolby => bool
     *     est_stereo => bool
     *     est_stereoar16x9 => bool
     *     est_ar16x9 => bool
     *     est_ar4x3 => bool
     *     est_stm => bool
     *     est_vost => bool
     *     est_vm => bool
     *     est_hd => bool
     *     est_inedit => bool
     *     est_inedit_en_clair => bool
     *     est_inedit_crypte => bool
     *     est_direct => bool
     *     est_differe => bool
     *     est_resume => bool
     *     est_premdif => bool
     *     est_derdif => bool
     *     est_redif => bool
     *     est_nouveaute => bool
     *     est_tempsfort => bool
     *     est_audiovision => bool
     *     est_senat => bool
     *     est_3d => bool
     *  }
     *  genre_specifique => string
     *  horaire => object {
     *    debut => string
     *    fin => string
     *  }
     *  id_chaine => int
     *  id_emission => int
     *  id_genre => int
     *  id_programme => int
     *  imdb => int
     *  libelle_nationalite => string
     *  note_telerama => int
     *  notule => string
     *  parties => NULL
     *  possede_bande_annonce => bool
     *  possede_critique => bool
     *  possede_notule => bool
     *  prime_time => int
     *  replay => NULL
     *  resume => string
     *  selections => object {
     *    films => bool
     *    series => bool
     *    replay_du_jour => bool
     *    plateau_tele => bool
     *  }
     *  serie => string
     *  showview => string
     *  signature => string
     *  signature_initiales => string
     *  soustitre => string
     *  titre => string
     *  titre_original => string
     *  url => string
     *  vignettes => object {
     *    petite => string
     *    grande => string
     *    petite169 => string
     *    grande169 => string
     *  }
     *}
     */
    public function fetch_programs($channel_ids, $nb_days = 1)
    {
        if (!empty($this->programs)) {
            return $this->programs;
        }

        $this->fetch_channels($channel_ids);

        $day = new \Datetime();
        for ($i = 0; $i < $nb_days; $i++) {
            foreach ($this->channels as $channel) {
                $url = $this->build_url(static::CHANNEL_GRID_PAGE, [
                    static::GRID_DATE_PARAM => $day->format("Y-m-d"),
                    static::GRID_IDS_PARAM  => $channel->id,
                    static::GRID_PAGE_PARAM => static::GRID_PAGE_VALUE
                ]);

                $json_response = $this->fetch_json($url);
                $data          = json_decode($json_response);

                if (json_last_error() !== JSON_ERROR_NONE ||
                    !isset($data->donnees) ||
                    !is_array($data->donnees)) {
                    continue;
                }

                $this->programs = array_merge(
                    $this->programs,
                    $data->donnees
                );
            }
            $day->add(new \DateInterval('P1D'));
        }

        return $this->programs;
    }

    /**
     * Build a signed url
     *
     * @param string $path
     * @param array $params
     *
     * @return string
     */
    protected function build_url($path, $params = [])
    {
        $params[static::DEVICE_PARAM] = static::DEVICE_VALUE;
        ksort($params);

        $string_to_hash = $path;
        foreach ($params as $key => $value) {
            $string_to_hash .= sprintf("%s%s", $key, $value);
        }

        $params[static::SIGNATURE_PARAM] = hash_hmac(static::HASH_ALGO, $string_to_hash, static::SIGNATURE_SALT);
        $params[static::API_KEY_PARAM]   = static::API_KEY_VALUE;

        return sprintf("%s%s?%s",
            static::BASE_URI,
            $path,
            http_build_query($params)
        );
    }

    /**
     * Get a genre by his id
     *
     * @return object as {
     *   public id      => int,
     *   public slug    => string,
     *   public libelle => string,
     * }
     */
    public function genre($genre_id)
    {
        if (empty(static::$genres)) {
            $this->fetch_channels();
        }

        foreach (static::$genres as $genre) {
            if ($genre->id == $genre_id) {
                return $genre;
            }
        }

        return null;
    }
}
