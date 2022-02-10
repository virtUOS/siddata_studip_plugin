<?php
// Please leave these requires untouched due to StudipAutoloader not applicable in all conditions
require_once('plugins_packages/virtUOS/SiddataPlugin/lib/SiddataCache.php');
require_once('plugins_packages/virtUOS/SiddataPlugin/lib/SiddataRestClient.php');

/**
 * Class SiddataDataManager
 *
 * @author Niklas Dettmer <ndettmer@uos.de>
 * @author Dennis Benz <dbenz@uni-osnabrueck.de>
 * @author Sebastian Osada <sebastian.osada@uni-osnabrueck.de>
 */
class SiddataDataManager
{
    /**
     * @var SiddataCache
     */
    private $cache;

    /**
     * @var SiddataCrypt
     */
    private $crypter;

    /**
     * @var SiddataRestClient
     */
    private $rest_client;

    /**
     * SiddataDataManager constructor.
     * @param SiddataRestClient $rest_client
     */
    public function __construct($rest_client)
    {
        $this->cache = SiddataCache::getInstance();
        $this->crypter = new SiddataCrypt();
        $this->rest_client = $rest_client;
    }

    /**
     * @param $arr
     * @return bool array contains all necessary fields for creating a SiddataRecommender
     */
    private static function validRecommender ($arr) {
        return array_key_exists('id', $arr) and isset($arr['id']);
    }

    /**
     * @param $arr
     * @return bool array contains all necessary fields for creating a SiddataGoal
     */
    private static function validGoal ($arr) {
        return array_key_exists('id', $arr) and isset($arr['id'])
            and array_key_exists('goal', $arr) and isset($arr['goal']);
    }

    /**
     * @param $arr
     * @return bool array contains all necessary fields for creating a SiddataActivity
     */
    private static function validActivity ($arr) {
        if ($arr['type'] == 'question'
            and $arr['answer_type'] == 'selection'
            and !count($arr['selection_answers']))
        {
            return false;
        }
        return array_key_exists('id', $arr) and isset($arr['id'])
            and array_key_exists('type', $arr) and isset($arr['type'])
            and array_key_exists('status', $arr) and isset($arr['status']);
    }

    /**
     * @param SiddataRecommender[] $recommender  array of SiddataRecommender objects
     * @return SiddataActivity[]
     */
    public function getNewActivities($recommender) {
        return $this->getStatusActivities($recommender, 'new');
    }

    /**
     * @param SiddataRecommender[] $recommender  array of SiddataRecommender objects
     * @return SiddataActivity[]
     */
    public function getActiveActivities($recommender) {
        return $this->getStatusActivities($recommender, 'active');
    }

    /**
     * @param SiddataRecommender[] $recommender  array of SiddataRecommender objects
     * @return SiddataActivity[]
     */
    public function getDoneActivities($recommender) {
        return $this->getStatusActivities($recommender, 'done');
    }

    /**
     * @param SiddataRecommender[] $recommender  array of SiddataRecommender objects
     * @return SiddataActivity[]
     */
    public function getSnoozedActivities($recommender) {
        return $this->getStatusActivities($recommender, 'snoozed');
    }

    /**
     * @param SiddataRecommender[] $recommender  array of SiddataRecommender objects
     * @return SiddataActivity[]
     */
    public function getDiscardedActivities($recommender) {
        return $this->getStatusActivities($recommender, 'discarded');
    }

    /**
     * @param SiddataRecommender[] $recommender array of SiddataRecommender objects
     * @param string $status status identifier
     * @return SiddataActivity[] all Activities with given status
     */
    public function getStatusActivities($recommender, $status) {
        $activities = array();
        foreach ($recommender as $rec) {
            foreach ($rec->getAllActivities() as $activity) {
                if ($activity["status"] == $status) {
                    array_push($activities, $activity);
                }

            }
        }

        return $activities;
    }

    /**
     * @param string $id
     * @return bool goal exists in current data
     */
    public function goalExists($id) {
        $goals = $this->extractGoals($this->getRecommenderStructure());
        if (empty(array_filter($goals, function($g) use($id) { return $g->getId() == $id; }))) {
            return false;
        }
        return true;
    }

    /**
     * @param string $id id of goal property
     * @return bool goal property exists in current data
     */
    public function propertyExists($id) {
        return $this->findProperty($id) !== false;
    }

    /**
     * @param string $id id of wanted goal property
     * @return bool|mixed goal which the property belongs to or false, if not found
     */
    public function findProperty($id) {
        $goals = $this->extractGoals($this->getRecommenderStructure());
        $properties = [];
        foreach ($goals as $goal) {
            foreach ($goal['properties'] as $prop) {
                $properties[] = $prop;
            }
        }
        if ($index = array_search($id, array_column($properties, 'id')) !== null) {
            return $properties[$index];
        }

        $goals = $this->extractGoals($this->createStructureFromJsonApi($this->getGoalsAsJson()));
        $properties = [];
        foreach ($goals as $goal) {
            $properties[] = $goal['properties'];
        }
        if ($index = array_search($id, array_column($properties, 'id')) !== null) {
            return $properties[$index];
        }
        return false;
    }

    /**
     * @param string $rec_id
     * @param bool $cached use cached data
     * @return SiddataRecommender|null
     */
    public function findRecommender($rec_id, $cached=true) {
        if ($cached) {
            // first search in cache
            $recommenders = $this->getAllRecommender();
            // search recommender in array of recommenders
            $recommender = array_values(array_filter($recommenders, function($r) use($rec_id) { return $r->getId() == $rec_id; }))[0];
            if (isset($recommender)) {
                return $recommender;
            }
        }
        // If not exist in cache, get recommender from backend
        return $this->extractRecommenders(
            $this->createStructureFromJsonApi(
                $this->rest_client->getRecommender($rec_id, ['activities,goals,goals.activities'])['response']
            )
        )[0];
    }

    /**
     * @param $id
     * @param bool $cached use cached data
     * @return SiddataGoal|null
     */
    public function findGoal($id, $cached=true) {
        if ($cached) {
            $goals = $this->getAllGoals();
            $goal = array_values(array_filter($goals, function($g) use($id) { return $g->getId() == $id; }))[0];
            if (isset($goal)) {
                return $goal;
            }
        }
        return $this->extractGoals(
            $this->createStructureFromJsonApi(
                $this->rest_client->getGoal($id, ['activities'])['response']
            )
        )[0];
    }

    /**
     * @param $id
     * @param bool $cached use cached data
     * @return SiddataActivity|null
     */
    public function findActivity($id, $cached=true) {
        if ($cached) {
            $activities = $this->getAllActivities();
            $activity = array_values(array_filter($activities, function($a) use($id) { return $a->getId() == $id; }))[0];
            if (isset($activity)) {
                return $activity;
            }
        }
        return $this->extractActivities(
            $this->createStructureFromJsonApi(
                $this->rest_client->getActivity($id, ['question'])['response']
            )
        )[0];
    }

    /**
     * @return mixed latest chdate of the cached data
     */
    public function getChdate() {
        return time();
    }

    /**
     * @param string $id
     * @return bool true if activity with given $id exists in current data
     */
    public function activityExists($id) {
        $activities = $this->extractActivities($this->getRecommenderStructure());
        if (empty(array_filter($activities, function($a) use($id) { return $a->getId() == $id; }))) {
            return false;
        }
        return true;
    }

    /**
     * Returns encoded json string of student route or cached json
     * @return string json string of student
     */
    public function getStudentAsJson() {
        $student_cache = $this->cache->getCacheData("student");
        if (!$student_cache) {
            $backend_student = $this->rest_client->getStudent(User::findCurrent()->getId());
            if ($backend_student['meta']['caching_allowed']) {
                $this->cache->updateCache("student", $backend_student['response']);
            }
            return $backend_student['response'];
        }
        return $student_cache;
    }

    /**
     * Returns encoded json string of student route with specific includes for the privacy settings
     * page or cached json
     *
     * @return string json string of student
     */
    public function getSettingsStudentAsJson() {
        $student_cache = $this->cache->getCacheData("settings_student");
        if (!$student_cache) {
            $backend_student = $this->rest_client->getStudent(User::findCurrent()->getId(), 'courses_brain,institutes_brain,courses_social,institutes_social', false, 'settings');
            if ($backend_student['meta']['caching_allowed']) {
                $this->cache->updateCache("settings_student", $backend_student['response']);
            }
            return $backend_student['response'];
        }
        return $student_cache;
    }

    /**
     * Returns encoded json string of studycourses route or cached json
     * @return string json string of all studycourses
     */
    public function getStudyCoursesAsJson() {
        $studycourses_cache = $this->cache->getCacheData("studycourses");
        if (!$studycourses_cache) {
            $backend_studycourses = $this->rest_client->getStudyCourses(null, '', false, 'settings');
            if ($backend_studycourses['meta']['caching_allowed']) {
               $this->cache->updateCache("studycourses", $backend_studycourses['response']);
            }
            return $backend_studycourses['response'];
        }
        return $studycourses_cache;
    }

    /**
     * Returns encoded json string of course route or cached json
     * @return string json string of all courses
     */
    public function getCoursesAsJson() {
        $courses_cache = $this->cache->getCacheData("courses");
        if (!$courses_cache) {
            $backend_courses = $this->rest_client->getCourses();
            if ($backend_courses['meta']['caching_allowed']) {
                $this->cache->updateCache("courses", $backend_courses['response']);
            }
            return $backend_courses['response'];
        }
        return $courses_cache;
    }

    /**
     * Returns encoded json string of goal route or cached json
     * @return string json string of all goals
     */
    public function getGoalsAsJson() {
        $goals_cache = $this->cache->getCacheData("goals");
        if (!$goals_cache) {
            $backend_goals = $this->rest_client->getGoal(null, 'activities');
            if ($backend_goals['meta']['caching_allowed']) {
                $this->cache->updateCache("goals", $backend_goals['response']);
            }
            return $backend_goals['response'];
        }
        return $goals_cache;
    }

    /**
     * Returns encoded json string of recommender route or cached json
     * If recommender_id null, return json of all recommenders
     *
     * @param string|null $recommender_id
     * @return string json string of recommender
     */
    public function getRecommenderAsJson($recommender_id = null) {
        $recommender_cache = $this->cache->getCacheData("recommender" . ($recommender_id ? "/".$recommender_id : ""));
        if (!$recommender_cache) {
            $backend_recommender = $this->rest_client->getRecommender($recommender_id, 'activities,goals,goals.activities', false, null);
            if ($backend_recommender['meta']['caching_allowed']) {
                $this->cache->updateCache("recommender" . ($recommender_id ? "/".$recommender_id : ''), $backend_recommender['response']);
            }
            return $backend_recommender['response'];
        }
        return $recommender_cache;
    }

    /**
     * @return array
     */
    public function getRecommenderStructure() {
        $recommender_json = $this->getRecommenderAsJson();
        return $this->createStructureFromJsonApi($recommender_json);
    }

    /**
     * Returns all cached Recommender
     * @return array Recommender
     */
    public function getAllRecommender() {
        return $this->extractRecommenders($this->getRecommenderStructure());
    }

    /**
     * Returns all cached goals
     * @return array Goals
     */
    public function getAllGoals() {
        return $this->extractGoals($this->getRecommenderStructure());
    }

    /**
     * Returns all cached Activities
     * @return array Activities
     */
    public function getAllActivities() {
        return $this->extractActivities($this->getRecommenderStructure());
    }

    /**
     * Instantiates objects and creates their relationships
     * @param $json
     * @return  array SiddataStudents
     */
    public function createStructureFromJsonApi($json) {
        $entities = [];
        $data = json_decode($json, true);

        if (is_array($data)) {
            if (is_array($data['data']) && is_array($data['included'])){
                $entities = array_merge($data['data'], $data['included']);
            } elseif (is_array($data['data'])) {
                $entities = $data['data'];
            }
        }

        $students = [];
        $recommenders = [];
        $goals = [];
        $activities = [];
        $courses = [];
        $questions = [];
        $resources = [];
        $events = [];
        $persons = [];

        // build objects
        foreach ($entities as $entity) {
            try {
                switch ($entity['type']) {
                    case 'SiddataUser':
                        $students[$entity['id']] = new SiddataStudent(
                            $entity['id'],
                            $entity['attributes']['user_origin_id']
                        );
                        break;
                    case 'Recommender':
                        $recommenders[$entity['id']] = new SiddataRecommender(
                            $entity['id'],
                            $entity['attributes']['name'],
                            $entity['attributes']['description'],
                            $entity['attributes']['image'],
                            $entity['attributes']['order'],
                            $entity['attributes']['enabled'],
                            $entity['attributes']['data_info'],
                            $entity['attributes']['color_theme']
                        );
                        break;
                    case 'Goal':
                        $goals[$entity['id']] = new SiddataGoal(
                            $entity['attributes']['title'],
                            $entity['id'],
                            null,
                            [],
                            [],
                            $entity['attributes']['description'],
                            $entity['attributes']['order'],
                            $entity['attributes']['type'],
                            $entity['attributes']['visible']
                        );
                        break;
                    case 'Activity':
                        $activities[$entity['id']] = new SiddataActivity(
                            $entity['id'],
                            $entity['attributes']['title'],
                            null,
                            $entity['attributes']['type'],
                            null,
                            null,
                            $entity['attributes']['feedback_value'],
                            $entity['attributes']['feedback_text'],
                            $entity['attributes']['feedback_size'],
                            $entity['attributes']['status'],
                            $entity['attributes']['rebirth'],
                            null,
                            null,
                            $entity['attributes']['answers'],
                            [],
                            $entity['attributes']['notes'],
                            $entity['attributes']['duedate'],
                            $entity['attributes']['order'],
                            $entity['attributes']['form'],
                            $entity['attributes']['description'],
                            null,
                            null,
                            null,
                            $entity['attributes']['color_theme'],
                            $entity['attributes']['image'],
                            $entity['attributes']['button_text'],
                            $entity['attributes']['interactions']
                        );
                        break;
                    case 'Question':
                        $questions[$entity['id']] = new SiddataQuestion(
                            $entity['id'],
                            $entity['attributes']['question_text'],
                            $entity['attributes']['answer_type'],
                            $entity['attributes']['selection_answers'] ?: []
                        );
                        break;
                    case 'Course':
                        $courses[$entity['id']] = new SiddataCourse(
                            $entity['id'],
                            $entity['attributes']['title'],
                            $entity['attributes']['description'],
                            $entity['attributes']['url'],
                            $entity['attributes']['date'],
                            $entity['attributes']['TF_IDF_scores'],
                            $entity['attributes']['studip_id']
                        );
                        break;
                    case 'Resource':
                        $resources[$entity['id']] = new SiddataResource(
                            $entity['id'],
                            $entity['attributes']['title'],
                            $entity['attributes']['description'],
                            $entity['attributes']['url'],
                            $entity['attributes']['iframe'],
                            $entity['attributes']['origin'],
                            $entity['attributes']['creator'],
                            $entity['attributes']['format']
                        );
                        break;
                    case 'Event':
                        $events[$entity['id']] = new SiddataEvent(
                            $entity['id'],
                            $entity['attributes']['studip_id'],
                            $entity['attributes']['title'],
                            $entity['attributes']['description'],
                            $entity['attributes']['url'],
                            $entity['attributes']['date'],
                            $entity['attributes']['place'],
                            $entity['attributes']['origin']
                        );
                        break;
                    case 'Person':
                        $persons[$entity['id']] = new SiddataPerson(
                            $entity['id'],
                            $entity['attributes']['image'],
                            $this->crypter->symmetric_decrypt($entity['attributes']['first_name']),
                            $this->crypter->symmetric_decrypt($entity['attributes']['surname']),
                            $entity['attributes']['title'],
                            $entity['attributes']['email'],
                            $this->crypter->symmetric_decrypt($entity['attributes']['role_description']),
                            $entity['attributes']['url'],
                            $entity['attributes']['recommendation_reason'],
                            $entity['attributes']['editable']
                        );
                        break;
                    default:
                }
            } catch (Exception $e) {
                PageLayout::postError("Eine Ressource konnte nicht geladen werden.");
                continue;
            }
        }

        // build relationships
        foreach ($entities as $entity) {
            try {
                switch ($entity['type']) {
                    case 'SiddataUser':
                        if (is_array($entity['relationships']['recommenders']['data'])) {
                            foreach (array_column($entity['relationships']['recommenders']['data'], 'id') as $rid) {
                                if ($recommenders[$rid]) {
                                    $students[$entity['id']]->addRecommender($recommenders[$rid]);
                                }
                            }
                        }
                        if (is_array($entity['relationships']['activities']['data'])) {
                            foreach (array_column($entity['relationships']['activities']['data'], 'id') as $rid) {
                                if ($activities[$rid]) {
                                    $students[$entity['id']]->addActivity($activities[$rid]);
                                }
                            }
                        }
                        break;
                    case 'Recommender':
                        if (is_array($entity['relationships']['goals']['data'])) {
                            foreach (array_column($entity['relationships']['goals']['data'], 'id') as $rid) {
                                if ($goals[$rid]) {
                                    $recommenders[$entity['id']]->addGoal($goals[$rid]);
                                }
                            }
                        }
                        if (is_array($entity['relationships']['activities']['data'])) {
                            foreach (array_column($entity['relationships']['activities']['data'], 'id') as $rid) {
                                if ($activities[$rid]) {
                                    $recommenders[$entity['id']]->addActivity($activities[$rid]);
                                }
                            }
                        }
                        if (is_array($entity['relationships']['students']['data'])) {
                            foreach (array_column($entity['relationships']['students']['data'], 'id') as $rid) {
                                if ($students[$rid]) {
                                    $recommenders[$entity['id']]->setStudent($students[$rid]);
                                    $students[$rid]->addRecommender($recommenders[$entity['id']]);
                                }
                            }
                        }
                        break;
                    case 'Goal':
                        if (is_array($entity['relationships']['activities']['data'])) {
                            foreach (array_column($entity['relationships']['activities']['data'], 'id') as $rid) {
                                if ($activities[$rid]) {
                                    $goals[$entity['id']]->addActivity($activities[$rid]);
                                }
                            }
                        }
                        break;
                    case 'Activity':
                        if (is_array($entity['relationships']['question']['data'])) {
                            foreach (array_column($entity['relationships']['question']['data'], 'id') as $rid) {
                                if ($questions[$rid]) {
                                    $activities[$entity['id']]->setQuestion($questions[$rid]);
                                }
                            }
                        }
                        if (is_array($entity['relationships']['course']['data'])) {
                            foreach (array_column($entity['relationships']['course']['data'], 'id') as $rid) {
                                if ($courses[$rid]) {
                                    $activities[$entity['id']]->setCourse($courses[$rid]);
                                }
                            }
                        }
                        if (is_array($entity['relationships']['resource']['data'])) {
                            foreach (array_column($entity['relationships']['resource']['data'], 'id') as $rid) {
                                if ($resources[$rid]) {
                                    $activities[$entity['id']]->setResource($resources[$rid]);
                                }
                            }
                        }
                        if (is_array($entity['relationships']['event']['data'])) {
                            foreach (array_column($entity['relationships']['event']['data'], 'id') as $rid) {
                                if ($events[$rid]) {
                                    $activities[$entity['id']]->setEvent($events[$rid]);
                                }
                            }
                        }
                        if (is_array($entity['relationships']['person']['data'])) {
                            foreach (array_column($entity['relationships']['person']['data'], 'id') as $rid) {
                                if ($persons[$rid]) {
                                    $activities[$entity['id']]->setPerson($persons[$rid]);
                                }
                            }
                        }
                }
            } catch (Exception $e) {
                PageLayout::postError("Eine Ressource konnte nicht vollständig geladen werden.");
                continue;
            }
        }

        return array_values($students);
    }

    /**
     * Extracts recommender from structure
     * @param SiddataStudent[] $data
     * @return SiddataRecommender[]
     */
    public function extractRecommenders($data) {
        $recommenders = [];
        foreach ($data as $user) {
            foreach ($user->getRecommenders() as $recommender) {
                $recommenders[] = $recommender;
            }
        }
        return $recommenders;
    }

    /**
     * Extracts goals
     * @param SiddataStudent[] $data
     * @return SiddataGoal[]
     */
    public function extractGoals($data) {
        $goals = [];
        foreach ($this->extractRecommenders($data) as $recommender) {
            foreach ($recommender->getGoals() as $goal) {
                $goals[] = $goal;
            }
        }
        return $goals;
    }

    /**
     * Extracts activities
     * @param SiddataStudent[] $data
     * @return SiddataActivity[]
     */
    public function extractActivities($data) {
        $activities = [];
        foreach ($this->extractGoals($data) as $goal) {
            foreach ($goal->getActivities() as $activity) {
                $activities[] = $activity;
            }
        }
        return $activities;
    }

    /**
     * Expires all caches of active user
     */
    public function invalidateCache() {
        $this->cache->invalidateCache();
    }

    /**
     * Get all activities from given goal which belong to a certain batch
     * @param $goal_id string ID of the goal
     * @param $batch_id string ID of the activity batch
     * @return array array of activities
     */
    public function getBatchActivityIds($goal_id, $batch_id) {
        $goal = $this->findGoal($goal_id);
        $activities = $goal->getActivities();
        $batch_activity_ids = [];
        foreach($activities as $activity) {
            if ($activity["form"] == $batch_id) {
                $batch_activity_ids[] = $activity["id"];
            }
        }
        return $batch_activity_ids;
    }


    /**
     * Mimics json_encode() to supply project-wide options- and error-handling
     * It will try JSON_PARTIAL_OUTPUT_ON_ERROR once.
     *
     * @param $value
     * @param int $options
     * @param int $depth
     * @return false|string
     */
    public static function json_encode($value, $options = 0, $depth = 512) {
        $json = json_encode($value, $options, $depth);
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Error Handling
            error_log(json_last_error_msg());
            // error_log(var_dump($value));
            // Trying to encode partially
            error_log('SIDDATA JSON encode of $value: Will try partial output on error.');
            $json = self::json_encode($value, JSON_PARTIAL_OUTPUT_ON_ERROR, $depth);
        }
        return $json;
    }

    /**
     * Checks if given array contains an activity of the given type
     * @param $arr array array of activities
     * @param $type string activity type
     * @return bool true, if the array contains an activity of the given type, else false
     */
    public static function array_cointains_activity_of_type($arr, $type) {
        foreach ($arr as $activity) {
            if ($activity['type'] == $type) {
                return true;
            }
        }
        return false;
    }

}
