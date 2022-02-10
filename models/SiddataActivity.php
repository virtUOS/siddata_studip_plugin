<?php


/**
 * Class SiddataActivity model representation of the activity database entity
 *
 * @author Niklas Dettmer <ndettmer@uos.de>
 * @author Sebastian Osada <sebastian.osada@uni-osnabrueck.de>
 * @author Philipp Schüttlöffel <schuettloeffel@zqs.uni-hannover.de>
 */
class SiddataActivity implements ArrayAccess
{
    private $id;                    // int

    private $goal;                  // SiddataGoal
    private $type;                  // string

    private $course_id;             // string, id in Stud.IP db
    private $event_id;              // string, id in Stud.IP db

    private $feedback_value;        // int
    private $feedback_text;         // string
    private $feedback_size;         // int

    private $status;                // string
    private $rebirth;               // boolean

    private $question_text;         // string
    private $answer_type;           // string
    private $answers;               // array of strings
    private $selection_answers;     // array of strings

    private $notes;                 // list of strings

    private $dueDate;                // time
    private $order;                     // int
    private $form;                      //int

    private $title;                 // string
    private $description;           // string
    private $url;
    private $url_text;              // string
    private $category;
    private $image;                 // string URL
    private $color_theme;           // string

    // course and event
    private $place;                 // string
    private $date;                  // string

    private $button_text;           // string

    // websites
    private $interactions;          // int

    // set dynamically
    private $semester_id;           // string
    private $og;                    // OpenGraphURLCollection

    private $question;
    private $course;
    private $event;
    private $resource;
    private $person;

    protected static $FEEDBACK_TITLES = [
        'worst' => 'Völlig falsch!',
        'verybad' => 'Unpassend',
        'bad' => 'Wenig hilfreich',
        'neutral' => 'Weder gut, noch schlecht',
        'good' => 'Hilfreich',
        'verygood' => 'Sehr hilfreich',
        'best' => 'Perfekt!'
    ];


    /**
     * SiddataActivity constructor.
     * @param int $id
     * @param SiddataGoal $goal
     * @param string $type
     * @param string $course_id
     * @param string $event_id
     * @param int $feedback_value
     * @param string $feedback_text
     * @param int $feedback_size
     * @param string $status
     * @param bool $rebirth
     * @param string $question_text
     * @param string $answer_type
     * @param string $answers
     * @param array $selection_answers
     * @param array $notes
     * @param float $dueDate
     * @param int $order
     * @param int $form
     * @param string $title
     * @param string $description
     * @param string $url
     * @param string $url_text
     * @param null $category
     * @param string $color_theme
     * @param string $image image source URL or data-URI
     * @param string $button_text
     * @param int $interactions
     * @param null $place
     * @param float $date
     */
    public function __construct(
        $id,

        $title,
        $goal                       = null,
        $type                       = null,

        $course_id                  = null,
        $event_id                   = null,

        $feedback_value             = null,
        $feedback_text              = null,
        $feedback_size              = null,

        $status                     = null,
        $rebirth                    = true,

        $question_text              = null,
        $answer_type                = null,
        $answers                    = null,
        $selection_answers          = array(),

        $notes                      = array(),

        $dueDate                    = null,
        $order                      = null,
        $form                       = null,

        $description                = null,
        $url                        = null,
        $url_text                   = null,
        $category                   = null,
        $color_theme                = null,

        $image                      = null,
        $button_text                = '',
        $interactions               = null,
        $place                      = null,
        $date                       = null
    ) {
        $this->id = $id;

        $this->goal = $goal;
        $this->type = $type;

        $this->course_id = $course_id;
        $this->event_id  = $event_id;

        $this->feedback_value = $feedback_value;
        $this->feedback_text = $feedback_text;
        $this->feedback_size = $feedback_size;

        $this->status = $status;
        $this->rebirth = $rebirth;

        $this->question_text = $question_text;
        $this->answer_type = $answer_type;
        $this->answers = $answers;
        $this->selection_answers = $selection_answers;

        $this->notes = $notes;

        $this->dueDate = strtotime($dueDate);
        $this->order = $order;
        $this->form = $form;

        $this->title = $title;
        $this->description = $description;

        if (isset($url) and $url != '' and !filter_var($url, FILTER_VALIDATE_URL)) {
            // url may be a navigation path
            if (Navigation::hasItem($url)) {
                // url is a navigation path
                $url = Navigation::getItem($url)->getURL();
            } else {
                // url is not valid
                $url = null;
            }
        }
        $this->url = $url;
        $this->url_text = $url_text;

        $this->category = $category;

        $this->color_theme = $color_theme;

        $this->image = $image;
        $this->date = $date;
        $this->place = $place;
        $this->button_text = $button_text;

        $this->interactions = $interactions;

        switch ($this->type) {
            case 'resource': case 'todo': case 'question': case 'person':
                break;
            case 'course':
                // set course attributes
                if (isset($this->course_id)){
                    $course = Course::find($this->course_id);

                    if (isset($course)) {
                        if (!isset($this->url) or $this->url == '') {
                            $this->url = URLHelper::getURL('dispatch.php/course/details', ["sem_id" => $this->course_id]);
                        }
                        if (!isset($this->place)) {
                            $this->place = $course->ort;
                        }
                        if (!isset($this->dueDate)) {
                            $this->dueDate = $course->start_time;
                        }
                    }
                }
                break;
            case 'event':
                // set coursedate attributes
                if (isset($this->event_id)) {
                    $event = CourseDate::find($this->event_id);
                    if (isset($event)){
                        if (!isset($this->url)) {
                            $this->url = URLHelper::getURL('dispatch.php/course/dates/details/' . $this->event_id, ['cid' => $event->course->getId()]);
                        }
                        if (!isset($this->place)) {
                            $this->place = $event->raum;
                        }
                        if (!isset($this->dueDate)) {
                            $this->dueDate = $event->date;
                        }
                        if (!isset($this->date)) {
                            $this->date = $event->date;
                        }
                    }
                }
                break;
            default:
        }

        if (isset($this->dueDate)) {
            $this->semester_id = Semester::findByTimestamp($dueDate)->id;
        }

        if (isset($this->url)) {
            $this->og = OpenGraph::extract($this->url);
        }

        $this->course = null;
        $this->question = null;
        $this->event = null;
        $this->resource = null;
        $this->person = null;
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getQuestionText() {
        return $this->question_text;
    }

    /**
     * @return SiddataGoal|null
     */
    public function getGoal() {
        return $this->goal;
    }

    /**
     * @return null[]|SiddataGoal[]
     */
    public function getGoals() {
        return [$this->goal];
    }

    /**
     * @return SiddataRecommender[]
     */
    public function getRecommenders() {
        $recommenders = [];
        foreach ($this->getGoals() as $goal) {
            $recommenders[] = $goal->getRecommender();
        }

        return $recommenders;
    }

    /**
     * @return SiddataStudent|null
     */
    public function getStudent() {
        return $this->getGoals()[0]->getRecommender()->getStudent();
    }

    /**
     * @return mixed
     */
    public function getRecommender() {
        return $this["goal"]->getRecommender();
    }

    /**
     * @return string
     */
    public function getSemesterName() {
        return Semester::find($this->semester_id)->name;
    }

    /**
     * @return string
     */
    public function getButtonText()
    {
        return $this->button_text;
    }

    /**
     * @return int
     */
    public function getInteractions()
    {
        return $this->interactions;
    }

    /**
     * @return bool
     */
    public function getRebirth() {
        return $this->rebirth;
    }

    /**
     * A set $this->image is preferred to an eventually existing CourseAvatar. So if $this->image is set, you will see $this->image even if $this->type is 'course' and the Course has an Avatar.
     * @param array $attributes HTML attributes
     * @return string|null complete HTML img tag
     */
    public function getImage($attributes=[]) {
        if ($this->type == 'course' and $this->course and $this->course->getCourse()) {
            try {
                return CourseAvatar::getAvatar($this->course->getCourse()->id)->getImageTag(Avatar::MEDIUM, $attributes);
            } catch (Exception $e) {
            }
            return null;
        }

        if ($this->type == 'person'){
            if ($this->person) {
                $image = $this->person->getImage();
                if (!isset($image)) {
                    return Avatar::getNobody()->getImageTag(Avatar::MEDIUM, $attributes);
                }
            }
        } else {
            $image = $this->image;
        }
        if (filter_var($image, FILTER_VALIDATE_URL)) {
            $tag = '<img src="' . $image. '"';
            foreach($attributes as $key => $value) {
                $tag .= ' ' . $key . '="' . $value . '"';
            }
            $tag .= ">";
            return $tag;
        } elseif ($this->type == 'person') {
            return Avatar::getNobody()->getImageTag(Avatar::MEDIUM, $attributes);
        }
        return null;
    }

    /**
     * Translates keywords into natural language
     * @return string displayable type (question/course/event/resource/todo) of the activity
     */
    public function getDisplayType() {
        switch ($this->type) {
            case "question":
                return "Frage";
                break;
            case "course":
                return "Kursempfehlung";
                break;
            case "event":
                return "Veranstaltung";
                break;
            case "resource":
                return "Website";
                break;
            case "todo":
                return "To-Do";
                break;
            case "person":
                return "Person";
                break;
            default:
                return "Empfehlung";
        }
    }

    /**
     * Translates keywords into natural language
     * @return string
     */
    public function getDisplayStatus() {
        switch ($this->status) {
            case "done":
                return "bereits abgeschlossen";
                break;
            case "discarded":
                return "verworfen";
                break;
            case "snoozed":
                return "pausiert";
                break;
            default:
                return "";
        }
    }

    /**
     * Determines title of the course or event including default titles
     * @return string|null title of referenced course or event
     */
    public function getEventTitle()
    {
        switch ($this->type) {
            case 'course':
                if (isset($this->course_id)) {
                    try {
                        return Course::find($this->course_id)->name;
                    } catch (Exception $e) {
                        return "Unbekannter Kurs";
                    }
                }
                return '';
            case 'event':
                if (isset($this->event_id)) {
                    return CourseDate::find($this->event_id)->content;
                }
                return '';
            case 'resource': case 'question': case 'person':
                return '';
            default:
                return '';
        }
    }

    /**
     * Determines icon keyword
     * @return string Icon shape
     */
    public function getTypeIcon() {
        switch ($this->type) {
            case 'course':
                return 'seminar';
            case 'event':
                return 'date';
            case 'resource':
                return 'link-extern';
            case 'question':
                return 'question';
            case 'todo':
                return 'log';
            case 'person':
                return 'person';
            default:
                return '';
        }
    }

    /**
     * @return String OpenGraph HTML div
     */
    public function getOpenGraph() {
        if (isset($this->og)) {
            return $this->og->render();
        }
        return "";
    }

    /**
     * @return mixed|string if a course is referenced, description of the course
     */
    public function getCourseDescription() {
        if (!isset($this->course_id)) {
            return "";
        }
        try {
            $course = Course::find($this->course_id);
            return $course['beschreibung'];
        } catch (Exception $e) {
        }
        return null;
    }

    /**
     * @return mixed|string if an event is referenced, description of the event
     */
    public function getEventDescription() {
        if (!isset($this->event_id)) {
            return "";
        }
        $course = Course::find($this->event_id);
        return $course['description'];
    }

    /**
     * @return string|null if a course is referenced, times and rooms of the course
     */
    public function getTimesRooms() {
        if (!isset($this->course_id)) {
            return null;
        }
        try {
            $sem = Seminar::GetInstance($this->course_id);
            return $sem->getDatesTemplate("dates/seminar_html", ['show_room' => true]);
        } catch (Exception $e) {
        }
        return null;
    }

    /**
     * @return bool|string|null if a course is referenced, next date of the course
     */
    public function getNextDate() {
        if (!isset($this->course_id)) {
            return null;
        }
        try {
            $sem = Seminar::GetInstance($this->course_id);
            return $sem->getNextDate();
        } catch (Exception $e) {
        }
        return null;
    }

    /**
     * @return bool|string|null if a course is referenced, first date of the course
     */
    public function getFirstDate() {
        if (!isset($this->course_id)) {
            return null;
        }
        try {
            $sem = Seminar::GetInstance($this->course_id);
            return $sem->getFirstDate();
        } catch (Exception $e) {
        }
        return null;
    }

    /**
     * @return array empty if not a course or course without lecturers, else list of a-tags to the lecturer's Stud.IP profiles
     */
    public function getLecturers() {
        $show_lecturers = [];
        if (!isset($this->course_id)) {
            return $show_lecturers;
        }
        try {
            $sem = Seminar::GetInstance($this->course_id);
            $lecturers = $sem->getMembers('dozent');
            foreach ($lecturers as $lecturer) {
                $show_lecturers[] = '<a href="' . URLHelper::getLink('dispatch.php/profile', ['username' => $lecturer['username']]) . '">'
                    . htmlready(count($lecturers) > 10 ? get_fullname($lecturer['user_id'], 'no_title_short') : $lecturer['fullname'])
                    . '</a>';
            }
            return $show_lecturers;
        } catch (Exception $e) {
        }
        return [];
    }

    /**
     * @return array selection_answers
     */
    public function getAnswers() {
        $html_answers = [];
        foreach ($this->selection_answers as $answer) {
            $html_answers[] = $answer;
        }
        return $html_answers;
    }

    /**
     * @return array notes for this activity
     */
    public function getNotes() {
        return $this->notes;
    }

    /**
     * @return string The String displayed for the url
     */
    public function getURLText() {
        return $this->url_text;
    }

    /**
     * @return SiddataCourse|null
     */
    public function getCourse()
    {
        return $this->course;
    }

    /**
     * @param SiddataCourse $course
     */
    public function setCourse(SiddataCourse $course)
    {
        $course->setActivity($this);
        $this->course = $course;
    }

    /**
     * @return SiddataEvent|null
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param SiddataEvent $event
     */
    public function setEvent(SiddataEvent $event)
    {
        $event->setActivity($this);
        $this->event = $event;
    }

    /**
     * @return SiddataResource|null
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @return float|null
     */
    public function getDueDate()
    {
        return $this->dueDate;
    }

    /**
     * @param SiddataResource $resource
     */
    public function setResource(SiddataResource $resource)
    {
        $resource->setActivity($this);
        $this->resource = $resource;
    }

    /**
     * @return SiddataQuestion|null
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * @param SiddataQuestion $question
     */
    public function setQuestion(SiddataQuestion& $question)
    {
        $question->setActivity($this);
        $this->question = $question;
    }

    /**
     * @return SiddataPerson|null
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param SiddataPerson $person
     */
    public function setPerson(SiddataPerson& $person)
    {
        $person->setActivity($this);
        $this->person = $person;
    }


    /**
     * @return bool true if a course with lecturers is referenced
     */
    public function hasLecturers() {
        if (!isset($this->course_id)) {
            return false;
        }
        try {
            return count(Seminar::GetInstance($this->course_id)->getMembers('dozent')) > 0;
        } catch (Exception $e) {
        }
        return false;
    }

    /**
     * @return bool true if activity has one of the inactive status (discarded, done, snoozed)
     */
    public function inactive() {

        return $this->status == 'discarded' || $this->status == 'done' || $this->status == 'snoozed';
    }

    /**
     * @return bool true if activity can be restored
     */
    public function isRestorable() {
        return $this->status == 'snoozed' || $this->status == 'done' && $this->rebirth || $this->status == 'discarded' && $this->rebirth;
    }

    /**
     * @return bool true if dueDate has been passed
     */
    public function missed() {
        if (!isset($this->dueDate)) {
            return false;
        }
        return $this->dueDate < time();
    }

    public function isQuestionnaire() {
        return $this->goal->isQuestionnaire();
    }

    /**
     * @param SiddataGoal $goal
     */
    public function setGoal(SiddataGoal $goal) {
        $this->goal = $goal;
    }

    /**
     * @param $name image name of the feedback
     * @return string title attribute for the feedback element
     */
    public function getFeedbackTitle($name) {
        if (!array_key_exists($name, self::$FEEDBACK_TITLES)) {
            return null;
        }
        return self::$FEEDBACK_TITLES[$name];
    }

    /**
     * @return Array map: feedback values to feedback names based on <code>$this->feedback_size</code>
     */
    public function getFeedbackNames() {
        switch ($this->feedback_size) {
            case 2:
                return [1 => 'thumbsdown', 2 => 'thumbsup'];
            case 3:
                return [1 => 'verybad', 2 => 'neutral' , 3 => 'verygood'];
            case 4:
                return [1 => 'verybad', 2 => 'bad', 3 => 'good', 4 => 'verygood'];
            case 5:
                return [1 => 'verybad', 2 => 'bad', 3 => 'neutral', 4 => 'good', 5 => 'verygood'];
            case 6:
                return [1 => 'worst', 2 => 'verybad', 3 => 'bad', 4 => 'good', 5 => 'verygood', 6 => 'best'];
            case 7:
                return [1 => 'worst', 2 => 'verybad', 3 => 'bad', 4 => 'neutral', 5 => 'good', 6 => 'verygood', 7 => 'best'];
            default:
                return null;
        }
    }

    /**
     * @return bool check for special button text which disables the submit button
     */
    public function hasButton() {
        return $this->button_text != "~static";
    }

    /**
     * @return array
     */
    public function asAssoc() {
        $arr = [];
        $arr["id"]                          = $this["id"];

        $arr["goal_id"]                     = $this["goal"]["id"];
        $arr["type"]                        = $this["type"];

        $arr["course_id"]                   = $this["course_id"];
        $arr["event"]                       = $this["event_id"];

        $arr["feedback_value"]              = $this["feedback_value"];
        $arr["feedback_text"]               = $this["feedback_text"];
        $arr["feedback_size"]               = $this["feedback_size"];

        $arr["status"]                      = $this["status"];

        $arr["question_text"]               = $this["question_text"];
        $arr["answer_type"]         = $this["answer_type"];
        $arr["answers"]             = $this["answers"];
        $arr["selection_answers"]   = $this["selection_answers"];

        $arr["notes"]                       = $this["notes"];

        $arr["duedate"]                   = $this["duedate"];
        $arr["order"]                       = $this["order"];
        $arr["form"]                        = $this["form"];

        $arr["title"]                       = $this["title"];
        $arr["description"]                 = $this["description"];
        $arr["url"]                         = $this["url"];
        $arr["url_text"]                    = $this["url_text"];
        $arr["category"]                    = $this["category"];
        $arr["image"]                       = $this["image"];

        $arr["place"]                       = $this["place"];
        $arr["date"]                        = $this["date"];

        return $arr;
    }

    /**
     * JSON serialize
     * @return false|string
     */
    public function asJSON() {
        return SiddataDataManager::json_encode($this->asAssoc());
    }

    /**
     * Create activity from JSON string
     * @param string $json json formatted string
     * @return SiddataActivity
     */
    public static function createFromJSON($json) {
        return self::createFromAssoc(json_decode($json, true));
    }

    /**
     * Create activity from assoc array
     * Attention: Goal will not be set
     * @param $arr
     * @return SiddataActivity
     */
    public static function createFromAssoc($arr) {
        return new self(
            $arr["id"],

            $arr["title"],
            null,
            $arr["type"],

            $arr["studip_id"],
            $arr["event"],

            $arr["feedback_value"],
            $arr["feedback_text"],
            $arr["feedback_size"],

            $arr["status"],

            $arr["question_text"],
            $arr["answer_type"],
            $arr["answers"],
            $arr["selection_answers"],

            $arr["notes"],

            $arr["duedate"],
            $arr["order"],
            $arr["form"],

            $arr["description"],
            $arr["url"],
            $arr["url_text"],
            $arr["category"],
            $arr["image"],

            $arr["place"],
            $arr["date"]
        );
    }

    /**
     * Whether a offset exists
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        if ($offset == "semester_name") {
            return isset($this->semester_id);
        }
        if ($offset == "list") {
            $list = $this->getRecommender();
            return isset($list);
        }
        if ($offset == "image") {
            $image = $this->getImage();
            return isset($image);
        }
        if ($offset == "description") {
            return isset($this->description);
        }
        if ($offset == "display_type") {
            return isset($this->type);
        }
        if ($offset == "display_status") {
            return isset($this->type);
        }
        if ($offset == "event_title") {
            return $this->type == 'course' || $this->type = 'event';
        }
        if ($offset == "type_icon") {
            return isset($this->type);
        }
        if ($offset == "course_description") {
            if (!isset($this->course_id)) {
                return false;
            }
            try {
                $course = Course::find($this->course_id);
                return isset($course['beschreibung']);
            } catch (Exception $e) {

            }
            return false;
        }
        if ($offset == "event_description") {
            if (!isset($this->event_id)) {
                return false;
            }
            $course_date = CourseDate::find($this->event_id);
            return isset($course_date['description']);
        }
        if ($offset == 'inactive') {
            return $this->inactive();
        }
        if ($offset == 'times_rooms') {
            if ($this->type != 'course') {
                return false;
            }
            return $this->getTimesRooms() != '';
        }
        if ($offset == 'next_date') {
            if ($this->type != 'course') {
                return false;
            }
            return $this->getNextDate();
        }
        if ($offset == 'first_date') {
            if ($this->type != 'course') {
                return false;
            }
            return $this->getFirstDate();
        }
        if ($offset == 'lecturers') {
            return $this->hasLecturers();
        }
        if ($offset == 'missed') {
            return true;
        }
        return isset($this->$offset);
    }

    /**
     * Offset to retrieve
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        if ($offset == 'semester_name')                 { return $this->getSemesterName(); }
        if ($offset == 'list')                          { return $this->getRecommender(); }
        if ($offset == 'title')                         { return $this->getTitle(); }
        if ($offset == 'image')                         { return $this->getImage(); }
        if ($offset == 'description')                   { return $this->getDescription(); }
        if ($offset == 'display_type')                  { return $this->getDisplayType(); }
        if ($offset == 'display_status')                { return $this->getDisplayStatus(); }
        if ($offset == 'event_title')                   { return $this->getEventTitle(); }
        if ($offset == 'type_icon')                     { return $this->getTypeIcon(); }
        if ($offset == 'og')                            { return $this->getOpenGraph(); }
        if ($offset == 'inactive')                      { return $this->inactive(); }
        if ($offset == 'course_description')            { return $this->getCourseDescription(); }
        if ($offset == 'event_description')             { return $this->getEventDescription(); }
        if ($offset == 'times_rooms')                   { return $this->getTimesRooms(); }
        if ($offset == 'next_date')                     { return $this->getNextDate(); }
        if ($offset == 'first_date')                    { return $this->getFirstDate(); }
        if ($offset == 'lecturers')                     { return $this->getLecturers(); }
        if ($offset == 'missed')                        { return $this->missed(); }
        if ($offset == 'selection_answers')             { return $this->getAnswers(); }
        if ($offset == 'notes')                         { return $this->getNotes(); }
        if ($offset == 'question_text')                 { return $this->getQuestionText(); }
        if ($offset == 'url_text')                      { return $this->getURLText(); }
        if ($offset == 'goal')                          { return $this->getGoal(); }
        if ($offset == 'button_text')                   { return $this->getButtonText(); }
        if ($offset == 'interactions')                   { return $this->getInteractions(); }
        if ($offset == 'duedate'
            || $offset == 'dueDate')                    { return $this->getDueDate(); }

        if ($offset == "feedback_text") {
            return $this->$offset;
        }

        return $this->$offset;
    }

    /**
     * Offset to set
     * @link https://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        if ($offset == "goal") {
            $this->setGoal($value);
        } else if ($offset == "display_type" || $offset == "event_title" || $offset == "type_icon") {
        } else {
            $this->$offset = $value;
        }
    }

    /**
     * Offset to unset
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        if ($offset != "id") {
            $this->$offset = null;
        }
    }

    /**
     * @param array $activities array of SiddataActivity Objects
     * @return mixed
     */
    public static function sortActivities($activities) {
        $sorted = $activities;
        usort($sorted, "compareActivities");
        return $sorted;
    }

    /**
     * @param string $id
     * @param array $attributes
     * @return false|string
     */
    public static function createJsonApiPatch(string $id, array $attributes) {
        return SiddataDataManager::json_encode([
            'data' => [
                'type' => 'Activity',
                'id' => $id,
                'attributes' => $attributes
            ]
        ]);
    }
}

/**
 * Comparator function for activities to determine display order
 * @param SiddataActivity $a
 * @param SiddataActivity $b
 * @return int -1 if $a shall be presented with higher priority, 1 if $b shall be presented with higher priority, 0 if they have equal priority
 */
function compareActivities(SiddataActivity $a, SiddataActivity $b) {

    $ordera = $a["order"];
    $forma = $a["form"];
    $orderb = $b["order"];
    $formb = $b["form"];

    if (isset($forma) and isset($formb) and $forma != $formb) {
        // between different forms sort by form value
        // within same form sort by order value
        $ordera = $forma;
        $orderb = $formb;
    } else if (isset($forma) and ! isset($formb)) {
        if ($forma == $orderb) {
            // If form value and order value equal,
            // prioritize forms over single activities.
            return -1;
        }
        // use form value if present
        $ordera = $forma;
    } else if (!isset($forma) and isset($formb)) {
        if ($ordera == $formb) {
            // If form value and order value equal,
            // prioritize forms over single activities.
            return 1;
        }
        // use form value if present
        $orderb = $formb;
    }

    if ($ordera == $orderb) {
        return 0;
    }
    if (!isset($ordera)) {
        return 1;
    }
    if (!isset($orderb)) {
        return -1;
    }

    return ($ordera < $orderb) ? -1 : 1;
}
