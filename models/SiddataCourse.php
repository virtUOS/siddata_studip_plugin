<?php


/**
 * Class SiddataCourse
 *
 * @author Niklas Dettmer <ndettmer@uos.de>
 */
class SiddataCourse extends SiddataActivityComponent
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
     * @var int
     */
    private $date;                  // timestamp
    /**
     * @var array
     */
    private $tf_idf_scores;       // JSON -> associative array
    /**
     * @var Course|SimpleORMap|NULL
     */
    private $course;

    /**
     * SiddataCourse constructor.
     * @param string $id
     * @param string $title
     * @param string $description
     * @param string $url
     * @param int $date
     * @param string $tf_idf_scores
     * @param string $course_id
     */
    public function __construct($id, $title, $description, $url, $date, $tf_idf_scores, $course_id)
    {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->url = $url;
        $this->date = $date;
        $this->tf_idf_scores = json_decode($tf_idf_scores, true);

        if ($course = Course::find($course_id)) {
            $this->course = $course;
        }

        if ($this->course and !$this->url) {
            $this->url = URLHelper::getLink('dispatch.php/course/details', ['sem_id' => $this->course->id]);
        }
    }

    /**
     * @return string
     */
    public function getTitle(): string
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
    public function getDate(): int
    {
        return $this->date;
    }

    /**
     * @return array
     */
    public function getTfIdfScores(): array
    {
        return $this->tf_idf_scores;
    }

    /**
     * @return Course
     */
    public function getCourse()
    {
        return $this->course;
    }
}
