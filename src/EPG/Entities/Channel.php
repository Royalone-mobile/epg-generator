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
}
