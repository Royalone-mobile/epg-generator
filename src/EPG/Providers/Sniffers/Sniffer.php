<?php
namespace EPG\Providers\Sniffers;

use GuzzleHttp\Client;

abstract class Sniffer
{
    /*
     * @var stdClass[]
     */
    protected $channels;

    /**
     * @var stdClass[]
     */
    protected $programs;

    /**
     * @var int[]
     */
    protected $channel_ids;

    /**
     * @var int
     */
    protected $nb_days;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->channels          = [];
        $this->programs          = [];
        $this->channel_ids       = [];
        $this->nb_days           = 1;
    }

    /**
     * Get an array of channels
     *
     * @return stdClass[]
     */
    public abstract function fetch_channels();

    /**
     * Get an array of program
     *
     * @return stdClass[]
     */
    public abstract function fetch_programs();

    /**
     * Filter the channels to fetch by their id
     *
     * @param array channel_ids: An array of channel ids
     *
     * @return Sniffer
     */
    public function filter_on_channels(array $channel_ids)
    {
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

    /**
     * Fetch a JSON stream
     *
     * @return string: The JSON stream on success (status 200)
     *
     * @throws Exception if status is different from 200
     */
    protected function fetch_json($url)
    {
        try {
            $client = new Client([
                'base_uri' => static::BASE_URI,
                'verify'   => false,
                'headers'  => [
                    'User-Agent'      => static::USER_AGENT,
                    'Accept'          => '*/*',
                    'Accept-Language' => 'fr-fr'
                ],
                'http_errors' => false
            ]);

            $response    = $client->request('GET', $url);
            $status_code = $response->getStatusCode();

            if (200 != $status_code) {
                throw new \Exception(
                    sprintf("Received %d status code", $status_code)
                );
            }
        } catch (\Exception $e) {
            return "{}";
        }

        return $response->getBody();
    }
}

