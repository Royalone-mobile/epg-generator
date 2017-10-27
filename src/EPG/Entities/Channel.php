<?php
namespace EPG\Entities;

class Channel extends EpgEntity
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $displayName;

    /**
     * @var array with "src", "width" and "height"  keys
     */
    public $icon;

    /**
     * @var string
     */
    public $url;

    /**
     * Constructor to initialize data
     */
    public function __construct($id, $displayName, $icon = null, $url = null)
    {
        $this->id          = $id;
        $this->displayName = $displayName;
        $this->icon        = $icon;
        $this->url         = $url;
    }
}
