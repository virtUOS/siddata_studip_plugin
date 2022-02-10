<?php


/**
 * Class SiddataResource
 *
 * @author Niklas Dettmer <ndettmer@uos.de>
 */
class SiddataResource extends SiddataActivityComponent
{

    /**
     * @var string
     */
    private $title;
    /**
     * @var string
     */
    private $description;
    /**
     * @var string
     */
    private $url;
    /**
     * @var boolean
     */
    private $iframe;
    /**
     * @var string
     */
    private $origin;
    /**
     * @var string
     */
     private $creator;
    /**
     * @var string
     */
     private $format;

    /**
     * SiddataResource constructor.
     * @param string $id
     * @param string $title
     * @param string $description
     * @param string $url
     * @param boolean $iframe
     * @param string $origin
     * @param string $creator
     * @param string $format
     */
    public function __construct($id, $title, $description, $url, $iframe, $origin, $creator, $format)
    {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;

        if (isset($url) and $url != '' and !filter_var($url, FILTER_VALIDATE_URL)) {
            // url may be a navigation path
            if (Navigation::hasItem($url)) {
                // url is a navigation path
                $url = Navigation::getItem($url)->getURL();
            } else {
                // url is not valid
            }
        }
        $this->url = $url;
        $this->iframe = $iframe;

        $this->origin = $origin;
        $this->creator = $creator;
        $this->format = $format;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return bool
     */
    public function isIframe()
    {
        return $this->iframe;
    }

    /**
     * @return string
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * @return string
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

}
