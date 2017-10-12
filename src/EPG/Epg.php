<?php
namespace EPG;

use XMLTV\Xmltv;

class Epg
{
    /**
     * @var XMLTV\Xmltv
     */
    protected $xmltv;

    /**
     * @var Provider[]
     */
    protected $providers;

    /**
     * Constructor
     *
     * @param Provider $provider
     */
    public function __construct($providers)
    {
        $this->providers = is_array($providers) ? $providers : [$providers];

        $this->xmltv    = new Xmltv();
        $this->xmltv
            ->setDate(date('Y-m-d'))
            ->setSourceinfourl('https://b-alidra.com/xmltv')
            ->setSourceinfoname('b-alidra.com')
            ->setSourcedataurl('https://b-alidra.com/xmltv')
            ->setGeneratorinfoname('XMLTV')
            ->setGeneratorinfourl('https://b-alidra.com/xmltv');
    }

    /**
     * Get the EPG XML stream after validating it.
     *
     * @return string
     */
    public function get_xml()
    {
        $this->build_xml();
        $this->xmltv->validate();

        return $this->xmltv->toXml();
    }

    /**
     * Add the providers channels and programs
     * to the EPG XML stream.
     *
     * @return string
     */
    protected function build_xml()
    {
        foreach ($this->providers as $provider) {
            $provider
                ->add_channels_to($this->xmltv)
                ->add_programs_to($this->xmltv);
        }
    }
}
