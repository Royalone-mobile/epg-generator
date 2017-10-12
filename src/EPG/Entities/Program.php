<?php
namespace EPG\Entities;

class Program
{
    /**
     * @var string
     */
    public $channel;

    /**
     * @var string
     */
    public $start;

    /**
     * @var string
     */
    public $stop;

    /**
     * @var string
     */
    public $pdcStart;

    /**
     * @var string
     */
    public $vpcStart;

    /**
     * @var string
     */
    public $showview;

    /**
     * @var string
     */
    public $videoplus;

    /**
     * @var string
     */
    public $clumpidx;

    /**
     * @var array with "lang" and "value" keys
     */
    public $title;

    /**
     * @var array with "lang" and "value" keys
     */
    public $subTitle;

    /**
     * @var array with "lang" and "value" keys
     */
    public $desc;

    /**
     * @var array with "name" and "value" keys
     */
    public $credits;

    /**
     * @var string
     */
    public $date;

    /**
     * @var string
     */
    public $category;

    /**
     * @var array with "lang" and "value" keys
     */
    public $keyword;

    /**
     * @var string
     */
    public $language;

    /**
     * @var string
     */
    public $origLanguage;

    /**
     * @var array with "units" and "value" keys
     */
    public $length;

    /**
     * @var array with "src", "width", and "height" keys
     */
    public $icon;

    /**
     * @var string
     */
    public $url;

    /**
     * @var string
     */
    public $country;

    /**
     * @var string
     */
    public $episodeNum;

    /**
     * @var array with "present", "aspect", "colour", "quality" and "value" keys
     */
    public $video;

    /**
     * @var array with "present", "stereo" keys
     */
    public $audio;

    /**
     * @var array with "start", "channel" keys
     */
    public $previouslyShown;

    /**
     * @var array with "lang" and "value" keys
     */
    public $premiere;

    /**
     * @var array with "lang" and "value" keys
     */
    public $lastChance;

    /**
     * @var boolean
     */
    public $new;

    /**
     * @var array with "type" and "language" keys
     */
    public $subtitles;

    /**
     * @var array with "icon" and "value" keys
     */
    public $rating;

    /**
     * @var array with "icon" and "value" keys
     */
    public $starRating;

    /**
     * @var array with "type", "source", "reviewer", lang"" and "value" keys
     */
    public $review;
}
