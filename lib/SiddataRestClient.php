<?php
// Please leave these requires untouched due to StudipAutoloader not applicable in all conditions
require_once('plugins_packages/virtUOS/SiddataPlugin/lib/SiddataCrypt.php');
require_once('plugins_packages/virtUOS/SiddataPlugin/lib/SiddataDataManager.php');
require_once('plugins_packages/virtUOS/SiddataPlugin/lib/SiddataDebugLogger.php');

/**
 * Class SiddataRestClient
 * This class performs all REST requests to the Siddata Backend API
 *
 * @author Niklas Dettmer <ndettmer@uos.de>
 * @author Sebastian Osada <sebastian.osada@uni-osnabrueck.de>
 * @author Dennis Benz <dbenz@uni-osnabrueck.de>
 * @author Philipp Schüttlöffel <schuettloeffel@zqs.uni-hannover.de>
 */
class SiddataRestClient
{
    private static $valid_methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];

    private $url;
    private $proxy_url;
    private $proxy_port;
    private $debug;
    private $crypter;
    private $error_msg;
    private $last_msg;
    private $api_key;

    protected static $instance = null;

    /**
     * Singleton pattern
     * @param string $url
     * @param string $proxy_url
     * @param int $proxy_port
     * @param bool $debug
     * @param string $error_msg
     * @param string $api_key
     * @return SiddataRestClient
     */
    public static function getInstance($url='http://localhost:8000/backend/api/', $proxy_url=null, $proxy_port=80, $debug=false, $error_msg=null, $api_key=null) {
        // create new instance if there is none
        if (self::$instance === null) {
            return new self($url, $proxy_url, $proxy_port, $debug, $error_msg, $api_key);
        }

        // update attributes on existing instance
        if (isset($url)) {
            self::$instance->setURL($url);
        }
        if (isset($proxy_url)) {
            if (isset($proxy_port)) {
                self::$instance->setProxy($proxy_url, $proxy_port);
            } else {
                self::$instance->setProxy($proxy_url);
            }
        }
        if (isset($debug)) {
            self::$instance->setDebug($debug);
        }
        if (isset($error_msg)) {
            self::$instance->setErrorMsg($error_msg);
        }
        if (isset($api_key)) {
            self::$instance->setApiKey($api_key);
        }
        return self::$instance;
    }

    protected function __clone() {
        return false;
    }

    /**
     * SiddataRestClient constructor.
     * @param string $url
     * @param string $proxy_url
     * @param int $proxy_port
     * @param bool $debug
     * @param string $error_msg
     * @param string $api_key
     */
    private function __construct($url='http://localhost:8000/backend/api', $proxy_url=null, $proxy_port=80, $debug=false, $error_msg=null, $api_key=null) {
        $this->setURL($url);
        $this->proxy_url = $proxy_url;
        $this->proxy_port = $proxy_port;
        $this->debug = $debug;
        $this->error_msg = $error_msg;
        $this->api_key = $api_key;
        if (isset($_SESSION['SIDDATA_last_msg'])) {
            $this->last_msg = $_SESSION['SIDDATA_last_msg'];
        } else {
            $this->last_msg = $_SESSION['SIDDATA_last_msg'] = 0;
        }

        $this->crypter = new SiddataCrypt();

        if(empty($this->crypter)) {
            $this->crypter = new SiddataCrypt();
        }
    }

    /**
     * @param $url string URL to be set
     * @return bool true if url is valid and therefore is set
     */
    public function setURL($url) {
        $valid = filter_var($url, FILTER_VALIDATE_URL);
        if ($valid) {
            if (substr($url, strlen($url)-1, 1) != '/') {
                $url .= '/';
            }
            $this->url = $url;
        }
        return $valid;
    }

    /**
     * @return string
     */
    public function getURL() {
        return $this->url;
    }

    /**
     * @param $url string
     * @param int $port
     * @return bool true if url is valid and therefore the proxy is set
     */
    public function setProxy($url, $port=80) {
        $valid = filter_var($url, FILTER_VALIDATE_URL);
        if ($valid) {
            $this->proxy_url = $url;
            $this->proxy_port = $port;
        }
        return $valid;
    }

    /**
     * @return array ["url" => proxy-url, "port" => proxy-port]
     */
    public function getProxy() {
        return ["url" => $this->proxy_url, "port" => $this->proxy_port];
    }

    /**
     * @param bool $debug
     */
    public function setDebug($debug=true) {
        $this->debug = $debug;
    }

    /**
     * @param string $msg
     */
    public function setErrorMsg($msg) {
        $this->error_msg = $msg;
    }

    /**
     * @param string $api_key
     */
    public function setApiKey($api_key) {
        $this->api_key = $api_key;
    }

    /**
     * REST-GET request for student data
     * @param $uid
     * @param string $include
     * @param bool $caching
     * @param null|string $used_rec
     * @return array response array ['http_code', 'response', 'meta']
     */
    public function getStudent($uid, $include='', $caching=false, $used_rec=null) {
        $route = 'student';
        $user_origin_id = $this->crypter->std_encrypt($uid);
        $params = [
            'used_rec' => $used_rec,
            'user_origin_id' => $user_origin_id,
            'include' => $include
        ];
        return $this->performRequest($route, $params, 'GET', array(), $caching);
    }

    /**
     * REST-GET request for recommender data
     * @param null $rid
     * @param string $include
     * @param bool $caching
     * @param null $used_rec
     * @return array response array ['http_code', 'response', 'meta']
     */
    public function getRecommender($rid=null, $include='', $caching=true, $used_rec=null) {
        $route = 'recommender';
        if ($rid) {
            $route .= '/' . $rid;
        }
        $params = [
            'used_rec' => $used_rec,
            'include' => $include
        ];
        return $this->performRequest($route, $params, 'GET', array(), $caching);
    }

    /**
     * REST-GET request for goal data
     * @param null $gid
     * @param string $include
     * @param bool $caching
     * @param null $used_rec
     * @return array response array ['http_code', 'response', 'meta']
     */
    public function getGoal($gid=null, $include='', $caching=true, $used_rec=null) {
        $route = 'goal';
        if ($gid) {
            $route .= '/' . $gid;
        }
        $params = [
            'used_rec' => $used_rec,
            'include' => $include
        ];
        return $this->performRequest($route, $params, 'GET', array(), $caching);
    }

    /**
     * REST-GET request for activity data
     * @param null $aid
     * @param string $include
     * @param bool $caching
     * @param null $used_rec
     * @return array response array ['http_code', 'response', 'meta']
     */
    public function getActivity($aid=null, $include='', $caching=true, $used_rec=null) {
        $route = 'activity';
        if ($aid) {
            $route .= '/' . $aid;
        }
        $params = [
            'used_rec' => $used_rec,
            'include' => $include
        ];
        return $this->performRequest($route, $params, 'GET', array(), $caching);
    }

    /**
     * REST-GET request for studycourse data
     * @param null $scid
     * @param string $include
     * @param bool $caching
     * @param null $used_rec
     * @return array response array ['http_code', 'response', 'meta']
     */
    public function getStudyCourses($scid=null, $include='', $caching=false, $used_rec=null) {
        $route = 'studycourse';
        if ($scid) {
            $route .= '/' . $scid;
        }
        $params = [
            'used_rec' => $used_rec,
            'include' => $include
        ];
        return $this->performRequest($route, $params, 'GET', [], $caching);
    }

    /**
     * REST-GET request for course data
     * @param null $cid
     * @param string $include
     * @param null $user_id
     * @param bool $caching
     * @param null $used_rec
     * @return array response array ['http_code', 'response', 'meta']
     */
    public function getCourses($cid=null, $include='', $user_id=null, $caching=false, $used_rec=null) {
        $route = 'course';
        if ($cid) {
            $route .= '/' . $cid;
        }
        $params = [
            'used_rec' => $used_rec,
            'include' => $include,
            'user_origin_id' => $user_id? : ''
        ];
        return $this->performRequest($route, $params, 'GET', [], $caching);
    }

    /**
     * REST-PATCH request for recommender data
     * @param string $data
     * @param string $rec_id
     * @param string $feature
     * @return array|null
     */
    public function patchRecommender($data, $rec_id='', $feature='') {
        $route = 'recommender';
        if ($rec_id) {
            $route .= '/' . $rec_id;
        }
        return $this->performRequest($route, ['used_rec' => $feature], 'PATCH', [$data]);
    }

    /**
     * REST-PATCH request for activity data
     * @param string $activity_id
     * @param string $patch patch object as json string
     * @param string|null $feature feature in which the request was triggered
     * @return array|null
     */
    public function patchActivity($activity_id, $patch, $feature=null) {
        return $this->performRequest('activity/' . $activity_id, ['used_rec' => $feature], 'PATCH', [$patch]);
    }

    /**
     * REST-PATCH request for goal data
     * @param string $patch patch object as json string
     * @param string|null $feature feature in which the request was triggered
     * @return array|null
     */
    public function patchGoal($goal_id, $patch, $feature=null) {
        return $this->performRequest('goal/' . $goal_id, ['used_rec' => $feature], 'PATCH', [$patch]);
    }

    /**
     * REST-DELETE request for activity data
     * @param $activity_id
     * @param string|null $feature
     * @return array|null
     */
    public function deleteActivity($activity_id, $feature=null) {
        $params = [];
        if ($feature) {
            $params['used_rec'] = $feature;
        }
        return $this->performRequest('activity/' . $activity_id, $params, 'DELETE');
    }

    /**
     * REST-POST request for user data
     * @param string $data JSON formatted string
     * @return array|null success
     */
    public function sendUserData($data) {
        return $this->performRequest("userdata", ['used_rec' => 'settings'], 'POST', [$data]);
    }

    /**
     * REST-POST request for person data
     * @param string $data JSON formatted string
     * @param null $used_rec
     * @return array|null
     */
    public function postPerson($data, $used_rec=null) {
        return $this->performRequest("person", ['used_rec' => $used_rec], 'POST', [$data]);
    }

    /**
     * REST-POST request for studycourse data
     * @param string $data
     * @return array|null
     */
    public function postStudyCourses($data) {
        return $this->performRequest("studycourse", [], 'POST', [$data]);
    }

    /**
     * REST-PATCH request for student data
     * @param string $data
     * @return array|null
     */
    public function patchStudent($data) {
        return $this->performRequest("student", [], 'PATCH', [$data]);
    }

    /**
     * REST-DELETE request for student data
     * @return array|null
     */
    public function deleteStudent() {
        return $this->performRequest("student", [], 'DELETE');
    }

    /**
     * REST-GET request for user data
     * @return array|null associative array of either sharing permissions or shared data
     */
    public function getUserData() {
        return $this->performRequest("userdata", ['used_rec' => 'settings'], 'GET', [], false);
    }

    /**
     * Wrapper for profession goals
     * @param string $goal_data JSON formatted string
     * @return array|null
     */
    public function sendProfession($goal_data) {
        return $this->sendGoal($goal_data, 'professions');
    }

    /**
     * REST-POST request for todo-activities data
     * @param string $todo_data JSON formatted string
     * @return array|null
     */
    public function sendTodo($todo_data) {
        return $this->performRequest("activities", ['used_rec' => 'todos'], 'POST', [$todo_data]);
    }

    /**
     * REST-POST request for goal data
     * @param string $goal_data JSON formatted string
     * @param string $feature feature in which the request was triggered
     * @return array
     */
    public function sendGoal($goal_data, $feature=null) {
        return $this->performRequest("goals", ['used_rec' => $feature], 'POST', [$goal_data]);
    }

    /**
     * REST-DEL request for goal data
     * @param string $goal_id
     * @param null $feature
     * @return array|null
     */
    public function deleteGoal ($goal_id, $feature=null) {
        $params = [];
        if ($feature) {
            $params['used_rec'] = $feature;
        }
        return $this->performRequest('goal/' . $goal_id, $params, 'DELETE');
    }

    /**
     * REST-POST request for course data
     * @param string $data
     * @param string $complete
     * @return array|null
     */
    public function postCourses($data, $complete) {
        // no feature parameter required because this function is never triggered by any user interaction
        return $this->performRequest("course", ['complete' => $complete], 'POST', [$data], false);
    }

    /**
     * REST-POST request for course dates data
     * @param string $data
     * @param string $complete
     * @return array|null
     */
    public function postCourseDates($data, $complete) {
        // no feature parameter required because this function is never triggered by any user interaction
        return $this->performRequest("event", ['complete' => $complete], 'POST', [$data], false);
    }

    /**
     * REST-POST request for institute data
     * @param string $data
     * @return array|null
     */
    public function postInstitutes($data) {
        // no feature parameter required because this function is never triggered by any user interaction
        return $this->performRequest("institute", [], 'POST', [$data], false);
    }

    /**
     * REST-POST request for subject data
     * @param string $data
     * @return array|null
     */
    public function postSubjects($data) {
        // no feature parameter required because this function is never triggered by any user interaction
        return $this->performRequest("subject", [], 'POST', [$data], false);
    }

    /**
     * REST-POST request for degree data
     * @param string $data
     * @return array|null
     */
    public function postDegrees($data) {
        // no feature parameter required because this function is never triggered by any user interaction
        return $this->performRequest("degree", [], 'POST', [$data], false);
    }

    /**
     * REST-POST request for lecturer data
     * @param $data
     * @return array|null
     */
    public function postLecturers($data) {
        return $this->performRequest("person", [], 'POST', [$data], false);
    }

    /**
     * @return SiddataCrypt
     */
    public function getCrypter() {
        return $this->crypter;
    }

    /**
     * Function to perform a REST-request using cURL
     * @param string $route brain route
     * @param array $params URL parameters
     * @param string $method REST method
     * @param array $fields fields to post
     * @param bool $caching use caching
     * @return array|null ["http_code" => http_code, "response" => response, "meta" => ["caching_allowed" => bool]]
     */
    protected function performRequest($route, $params=array(), $method='GET', $fields=array(), $caching=true) {
        $url = $this->getURL() . $route . "?origin=" . SiddataRestClient::getOrigin()
            . "&api_key=" . $this->api_key;

        // pseudonymization
        if (!isset($params['user_origin_id'])) {
            $user_id = User::findCurrent()->id;
            $user_origin_id = $this->crypter->std_encrypt($user_id);
            $url .= "&user_origin_id=" . $user_origin_id;
        }

        // additional request parameters
        foreach ($params as $key => $value) {
            $url .= "&" . $key . "=" . $value;
        }
        if (!$params['used_rec']) {
            $url .= '&feature=default';
        }

        $manager = new SiddataDataManager($this);
        if ($caching and $method == 'GET') {
            $chdate = $manager->getChdate();
            if (isset($chdate)) {
                $url .= '&chdate=' . $chdate;
            }
        }

        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $manager->invalidateCache();
        }

        // create curl object
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($method == 'GET') {
            // we are good to go
        } else if ($method == 'POST') {
            if (!empty($fields)) {
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                foreach ($fields as $field) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $field);
                }
            } else if ($this->debug) {
                PageLayout::postInfo("No fields to post.");
            }
        } else if (self::validMethod($method)) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            if ($method == 'PATCH' or $method == 'PUT') {
                curl_setopt($ch, CURLOPT_POST, 1);
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            foreach ($fields as $field) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $field);
            }
        } else if ($this->debug) {
            PageLayout::postError("Method . " . $method . " is not valid.");
            return null;
        }

        // insert proxy
        $proxy = $this->getProxy();
        if ($proxy["url"]) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy["url"]);
            curl_setopt($ch, CURLOPT_PROXYPORT, $proxy["port"]);
        }

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // debug information
        if ($this->debug) {
            $request = [];
            $request['url'] = $url;
            $request['method'] = $method;
            $request['http_response_code'] = $http_code;
            $request['response_body'] = $response;
            $request['fields'] = $fields;
            $request['response_time'] = [
                'CURLINFO_TOTAL_TIME' => curl_getinfo($ch, CURLINFO_TOTAL_TIME),
                'CURLINFO_NAMELOOKUP_TIME' => curl_getinfo($ch, CURLINFO_NAMELOOKUP_TIME),
                'CURLINFO_CONNECT_TIME' => curl_getinfo($ch, CURLINFO_CONNECT_TIME),
                'CURLINFO_PRETRANSFER_TIME' => curl_getinfo($ch, CURLINFO_PRETRANSFER_TIME),
                'CURLINFO_STARTTRANSFER_TIME' => curl_getinfo($ch, CURLINFO_STARTTRANSFER_TIME),
                'CURLINFO_REDIRECT_TIME' => curl_getinfo($ch, CURLINFO_REDIRECT_TIME),
                'CURLINFO_APPCONNECT_TIME' => curl_getinfo($ch, CURLINFO_APPCONNECT_TIME)
                ];
            SiddataDebugLogger::log($request);
            SiddataDebugLogger::dataDump(serialize($params), $route.'_'.$method.'_params_serialized.txt', get_class());
            SiddataDebugLogger::dataDump(serialize($fields), $route.'_'.$method.'_fields_serialized.txt', get_class());
        }

        $this->processRespCode($http_code);

        $meta = [];
        $meta["caching_allowed"] = $this->cachingAllowed($http_code);

        return ["http_code" => $http_code, "response" => $response, "meta" => $meta];
    }

    /**
     * Check if method is valid
     * @param string $method
     * @return bool
     */
    protected static function validMethod($method) {
        return in_array($method, SiddataRestClient::getValidMethods());
    }

    /**
     * Get list of valid methods
     * @return array Array containing the valid method strings
     */
    protected static function getValidMethods() {
        return SiddataRestClient::$valid_methods;
    }

    /**
     * Creating an identifier for current Stud.IP-Instance
     * @return string
     */
    private static function getOrigin() {
        $config = Config::get();
        if ($config['SIDDATA_origin']) {
            return $config['SIDDATA_origin'];
        }

        $server_name = '';

        // source: /vendor/phpCAS/CAS/Client.php
        if (!empty($_SERVER['HTTP_X_FORWARDED_HOST'])) {
            $hosts = explode(',', $_SERVER['HTTP_X_FORWARDED_HOST']);
            return $hosts[0];
        } else if (!empty($_SERVER['HTTP_X_FORWARDED_SERVER'])) {
            $server_name = $_SERVER['HTTP_X_FORWARDED_SERVER'];
        } else {
            if (empty($_SERVER['SERVER_NAME'])) {
                $server_name = $_SERVER['HTTP_HOST'];
            } else {
                $server_name = $_SERVER['SERVER_NAME'];
            }
        }

        $name_comps = explode('.', $server_name);
        $rev_name_comps = array_reverse($name_comps);
        $origin = '';

        foreach ($rev_name_comps as $key => $comp) {
            if ($key > 0) {
                $origin .= '.';
            }
            $origin .= $comp;
        }

        $config->store('SIDDATA_origin', $origin);

        return $origin;
    }

    /**
     * Info messages for most common http response codes
     * @param int $code HTTP response code
     */
    private function processRespCode($code) {
        if ($this->msgAllowed()) {
            switch ($code) {
                case 200:
                    // ok
                    break;
                case 400:
                    PageLayout::postError('Die Anfrage an den Siddata-Server war fehlerhaft. ' . $this->error_msg);
                    break;
                case 401:
                    error_log($code.' - unauthorized call to Siddata webservice - check SIDDATA_origin and SIDDATA_api_key');
                    PageLayout::postError('Diese Stud.IP-Instanz ist nicht für die Nutzung des Siddata Webservice konfiguriert. ' . $this->error_msg);
                    break;
                case 500:
                    // Internal server error
                    PageLayout::postError('Es gab einen Fehler im Siddata-Server. ' . $this->error_msg);
                    break;
                case 0:
                    // No response
                    PageLayout::postError('Der Siddata-Server ist zur Zeit nicht erreichbar. ' . $this->error_msg);
                    break;
            }
        }
    }

    /**
     * Preventing error message overload by limiting messages per time
     * @return bool true if enough time has passed since last message
     */
    private function msgAllowed() {
        $time = time();
        $allowed = ($time - $this->last_msg) > 1;
        if ($allowed) {
            $this->last_msg = $time;
            $_SESSION['SIDDATA_last_msg'] = $time;
        }
        return $allowed;
    }

    /**
     * Check if caching is allowed for current response
     * @param $http_code string http status code
     * @return bool
     */
    private function cachingAllowed($http_code) {
        if ($http_code >= 400) {
            return false;
        }
        return true;
    }
}
