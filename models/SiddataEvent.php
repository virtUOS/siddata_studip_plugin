<?php


/**
 * Class SiddataEvent
 *
 * @author Niklas Dettmer <ndettmer@uos.de>
 */
class SiddataEvent extends SiddataActivityComponent
{

    /**
     * @var CourseDate
     */
    private $course_date;
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
     * @var int
     */
    private $date;
    /**
     * @var string
     */
    private $place;
    /**
     * @var string
     */
    private $origin;


    /**
     * SiddataEvent constructor.
     * @param string $id
     * @param string $studip_id
     * @param string $title
     * @param string $description
     * @param string $url
     * @param int $date
     * @param string $place
     * @param string $origin
     */
    public function __construct($id, $studip_id, $title, $description, $url, $date, $place, $origin)
    {
        $this->id = $id;

        if ($cd = CourseDate::find($studip_id)) {
            $this->course_date = $cd;
        }

        $this->title = $title;
        $this->description = $description;
        $this->url = $url;
        $this->date = $date;
        $this->place = $place;
        $this->origin = $origin;
    }

    /**
     * @return string
     */
    public function getStudipId()
    {
        return $this->course_date->getId();
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
     * @return int
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getPlace()
    {
        return $this->place;
    }

    /**
     * @return string
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * @return CourseDate
     */
    public function getCourseDate()
    {
        return $this->course_date;
    }

}
