<?php


/**
 * Class SiddataGoal model representation of the goal database entity
 *
 * @author Niklas Dettmer <ndettmer@uos.de>
 * @author Dennis Benz <dbenz@uni-osnabrueck.de>
 */
class SiddataGoal implements ArrayAccess
{

    private $goal;          // String
    private $id;            // int
    private $recommender;          // SiddataRecommender
    private $properties;    // array structure of properties
    private $activities;
    private $description;
    private $order;
    private $type;
    private $visible;

    /**
     * SiddataGoal constructor.
     * @param SiddataGoal $goal
     * @param int $id
     * @param SiddataRecommender $recommender
     * @param array $properties
     * @param array $activities
     * @param string $description
     * @param int|null $order
     * @param string $type
     * @param bool $visibile
     */
    public function __construct(
        $goal,
        $id,
        $recommender = null,
        $properties=array(),
        $activities = array(),
        $description='',
        $order=null,
        $type='',
        $visibile=true
    ) {
        $this->goal         = $goal;
        $this->id           = $id;
        $this->properties   = $properties;
        $this->activities   = $activities;

        $this->description  = $description;
        $this->order        = $order;
        $this->type         = $type;

        $this->visible = $visibile;

        if ($recommender) {
            $this->setRecommender($recommender);
        }
    }

    /**
     * Creates a new goal from JSON string
     * @param string $json
     * @return SiddataGoal
     */
    public function createFromJSON($json) {
        return self::createFromAssoc(json_decode($json, true));
    }

    /**
     * Create a new goal from an associative array
     * @param $arr
     * @return SiddataGoal
     */
    public function createFromAssoc($arr) {
        return new self(
            $arr['goal'],
            $arr['id'],
            $arr['list'],
            $arr['properties'],
            $arr['activities']
        );
    }

    /**
     * Get all activities of this goal
     * @param boolean $for_view take view limiter in $_SESSION['SIDDATA_view'] into account
     * @return array
     */
    public function getActivities($for_view=false) {
        if ($for_view) {
            $activities = [];
            // filter according to current view
            switch ($_SESSION['SIDDATA_view']) {
                case 'done':case 'snoozed':case 'discarded':
                $activities = array_filter($this->activities,
                    function($a) {
                        return $a['status'] == $_SESSION['SIDDATA_view'];
                    }
                );
                break;
                case 'all':default:
                $activities = array_filter($this->activities,
                    function($a) {
                        return $a['status'] == 'active' or $a['status'] == 'new' or $a['status'] == 'immortal';
                    }
                );
                break;

            }
            return $activities;
        }
        return $this->activities;
    }

    /**
     * Get a specific activity of this goal
     * @param int $id
     * @return mixed|null
     */
    public function getActivity($id) {
        return $this->getActivities()[$id]? : null;
    }

    /**
     * Get previous activity of the given one
     * @param int $id current activity id
     * @return SiddataActivity
     */
    public function getPreviousActivity($id) {
        $activities = $this->getActivities(true);
        reset($activities);
        while (current($activities) && key($activities) !== $id) {
            next($activities);
        }
        return prev($activities)? : null;
    }

    /**
     * Get next activity of the given one
     * @param int $id current activity id
     * @return SiddataActivity
     */
    public function getNextActivity($id) {
        $activities = $this->getActivities(true);
        reset($activities);
        while (current($activities) && key($activities) !== $id) {
            next($activities);
        }
        return next($activities)? : null;
    }

    /**
     * Shall the goal be displayed?
     * @return bool|mixed
     */
    public function isVisible()
    {
        return $this->visible;
    }

    /**
     * @return SiddataRecommender
     */
    public function getRecommender() {
        return $this->recommender;
    }

    /**
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return string goal string
     */
    public function getGoal() {
        return $this->goal;
    }

    /**
     * @return SiddataStudent|null
     */
    public function getStudent() {
        return $this->getRecommender()->getStudent();
    }

    /**
     * @param SiddataRecommender $recommender
     */
    public function setRecommender($recommender) {
        $this->recommender = $recommender;
    }

    /**
     * @return bool true if goal is a questionnaire
     */
    public function isQuestionnaire() {
        foreach ($this->properties as $property) {
            if ($property['key'] == 'type') {
                return $property['value'] == 'questionnaire';
            }
        }
        return false;
    }

    /**
     * Delete activity in the frontend
     * @param int $id
     */
    public function deleteActivity($id) {
        unset($this->activities[$id]);
    }

    /**
     * @param SiddataActivity $activity
     */
    public function addActivity(SiddataActivity& $activity) {
        $activity->setGoal($this);
        if (!in_array($activity, $this->activities)) {
            $this->activities[$activity["id"]] = $activity;
        }
    }

    /**
     * @return mixed
     */
    public function getList()
    {
        return $this->recommender;
    }

    /**
     * @return array|mixed
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return int|null
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type? $this->type : '';
    }    // array

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
        if ($offset == 'list') { return $this->getRecommender(); }
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
        if ($offset != "activities") {
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
        if ($offset == "activities"){
            $this->activities = array();
        } else {
            $this->$offset = null;
        }
    }
}
