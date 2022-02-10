<?php


/**
 * Class SiddataRecommender
 *
 * @author Niklas Dettmer <ndettmer@uos.de>
 * @author Dennis Benz <dbenz@uni-osnabrueck.de>
 * @author Sebastian Osada <sebastian.osada@uni-osnabrueck.de>
 */
class SiddataRecommender
{

    private $id;
    private $name;
    private $description;
    private $image;
    private $order;
    private $enabled;
    private $student;
    private $goals;
    private $activities;
    private $data_info;
    private $color_theme;

    /**
     * SiddataRecommender constructor.
     * @param int $id
     * @param string $name
     * @param string $description
     * @param null $image
     * @param int|null $order
     * @param bool $enabled
     * @param SiddataStudent|null $student
     * @param array $goals
     * @param array $activities
     * @param string $data_info
     * @param string $color_theme
     */
    public function __construct(
        $id,
        $name = 'Recommender',
        $description='',
        $image=null,
        $order=null,
        $enabled=false,
        $data_info='',
        $student=null,
        $goals = array(),
        $activities=array(),
        $color_theme=''
    ) {
        $this->id               = $id;
        $this->name             = $name;
        $this->description      = $description;
        $this->image            = $image;
        $this->order            = $order;
        $this->enabled          = $enabled;
        $this->student          = $student;
        $this->goals            = $goals;
        $this->activities       = $activities;
        $this->data_info        = $data_info;
        $this->color_theme      = $color_theme;
    }

    /**
     * @return string
     */
    public function getDataInfo(): string
    {
        return $this->data_info;
    }

    /**
     * @return SiddataGoal[] array of SiddataGoal objects
     */
    public function getGoals() {
        return $this->goals;
    }

    /**
     * @param int $id
     * @return SiddataGoal|null
     */
    public function getGoal($id) {
        if($this->getGoals()[$id]) {
            return $this->getGoals()[$id];
        }
        return null;
    }

    /**
     * Get only activities directly attached to this object
     * @param boolean $for_view take view limiter in $_SESSION['SIDDATA_view'] into account
     * @return array a list of all activities belonging directly to this recommender
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
                            return $a['status'] == 'active' or $a->getStatus() == 'new';
                        }
                    );
                    break;

            }
            return $activities;
        }
        return $this->activities;
    }

    /**
     * Get all the activities of this recommender and all its goals
     * @param boolean $for_view take view limiter in $_SESSION['SIDDATA_view'] into account
     * @return array a list of all recommended activities received by this list
     */
    public function getAllActivities($for_view=false) {
        $activities = $this->getActivities($for_view);

        foreach($this->getGoals() as $goal) {
            foreach($goal->getActivities($for_view) as $activity) {
                $activities[] = $activity;
            }
        }

        return $activities;
    }

    /**
     * @param int $id
     * @return SiddataActivity|null
     */
    public function getActivity($id) {
        if ($activities = array_filter($this->activities, function($activity) use($id) { return $activity->getId() == $id; })) {
            return $activities[0];
        }
        foreach($this->getGoals() as $goal) {
            if($goal->getActivity($id)) {
                return $goal->getActivity($id);
            }
        }
        return null;
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
    public function getName() {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getColorTheme() {
        return $this->color_theme;
    }

    /**
     * @return boolean
     */
    public function isEnabled() {
        return $this->enabled;
    }

    /**
     * @return SiddataStudent|null
     */
    public function getStudent() {
        return $this->student;
    }

    /**
     * @param SiddataStudent $student
     */
    public function setStudent(SiddataStudent $student) {
        $this->student = $student;
    }

    /**
     * @param int $id
     */
    public function deleteGoal($id) {
        unset($this->goals[$id]);
    }

    /**
     * @param int $id
     */
    public function deleteActivity($id) {
        $goal_id = $this->getActivity($id)['goal'];
        $this->getGoal($goal_id)->deleteActivity($id);
    }

    /**
     * @param SiddataGoal $goal
     */
    public function addGoal(SiddataGoal& $goal) {
        $goal->setRecommender($this);
        if (!in_array($goal, $this->goals)) {
            $this->goals[$goal["id"]] = $goal;
        }
    }

    /**
     * @param SiddataActivity $activity
     */
    public function addActivity(SiddataActivity& $activity) {
        $this->activities[] = $activity;
    }
}
