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
     * Constructor
     */
    public function __construct()
    {
        $this->channels          = [];
        $this->programs          = [];
    }

    /**
     * Get an array of channels
     *
     * @param array $channel_ids: Filter on channels
     *
     * @return stdClass[]
     */
    public abstract function fetch_channels($channel_ids);

    /**
     * Get an array of program
     *
     * @param array $channel_ids: Filter on channels
     * @param int $nb_days: number of days to grab
     *
     * @return stdClass[]
     */
    public abstract function fetch_programs($channel_ids, $nb_days);

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

