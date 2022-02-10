<?php
// To cope with lots of data being fetched from DB
@ini_set('memory_limit','384M');
/**
 * Class SiddataCronjob
 *
 * Cronjob collecting data from the Stud.IP DB
 *
 * @author Niklas Dettmer <ndettmer@uos.de>
 * @author Sebastian Osada <sebastian.osada@uni-osnabrueck.de>
 * @author Philipp Schüttlöffel <schuettloeffel@zqs.uni-hannover.de>
 */
class SiddataCronjob extends CronJob
{

    private $time_offset;
    private $course_visible = 1;
    private $course_sem_type = 1;

    /**
     * @return string
     */
    public static function getName() {
        return _('Siddata-Collector');
    }

    /**
     * @return string
     */
    public static function getDescription() {
        return _('Dieser Cronjob sammelt Veranstaltungs- und Termindaten aus der Stud.IP-Datenbank und schickt diese an einen Siddata-REST-Server.');
    }

    /**
     * @return array
     */
    public static function getParameters() {
        return [];
    }

    /**
     * Setup method.
     */
    public function setUp() {
        // Please leave these requires untouched due to StudipAutoloader not applicable in all conditions
        require_once 'public/plugins_packages/virtUOS/SiddataPlugin/lib/SiddataRestClient.php';
        require_once('plugins_packages/virtUOS/SiddataPlugin/lib/SiddataDataManager.php');

        // time offset for each iteration
        $config = Config::get();
        $this->time_offset = 60 * 60 * 24 * 30 * $config['SIDDATA_Collector_offset'];
    }

    /**
     * Execute the cronjob.
     *
     * @param mixed $last_result What the last execution of this cronjob
     *                           returned.
     * @param array $parameters Parameters for this cronjob instance which
     *                          were defined during scheduling.
     */
    public function execute ($last_result, $parameters=[]) {
        $config = Config::get();
        $client = SiddataRestClient::getInstance($config['SIDDATA_Brain_URL'],
            $config['SIDDATA_Proxy_URL'], $config['SIDDATA_Proxy_Port'], $config['SIDDATA_Debug_Info'], $config['SIDDATA_Error_Message'], $config['SIDDATA_api_key']);

        // determine start of the collection interval
        $starttime = 0;
        if ($config['SIDDATA_Collector_startyear']){
            // start date set by admin
            $starttime = DateTime::createFromFormat("d-m-Y H:i", "01-01-" . $config['SIDDATA_Collector_startyear'] . " 00:00")->getTimestamp();
        }
        if (!$starttime) {
            // no or undue configured start date
            // take start date of first course

            $query = "
                SELECT MIN(c.start_time)
                FROM `seminare` c
                WHERE c.visible = :visible
                    AND c.status in
                        (SELECT t.id
                        FROM `sem_types` t
                        WHERE class = :sem_type)";

            $db = DBManager::get()->prepare($query);
            $db->bindValue(':visible', $this->course_visible);
            $db->bindValue(':sem_type', $this->course_sem_type);
            $db->execute();
            $starttime = $db->fetchFirst()[0];
        }
        $endtime = 0;
        if ($config['SIDDATA_Collector_endyear']){
            // start date set by admin
            $endtime = DateTime::createFromFormat("d-m-Y H:i", "01-01-" . $config['SIDDATA_Collector_endyear'] . " 00:00")->getTimestamp();
        }
        if (!$endtime) {
            // no or undue configured end date
            // take start date of last course

            $query = "
                SELECT MAX(c.start_time)
                FROM `seminare` c
                WHERE c.visible = :visible
                    AND c.status in
                        (SELECT t.id
                        FROM `sem_types` t
                        WHERE class = :sem_type)";

            $db = DBManager::get()->prepare($query);
            $db->bindValue(':visible', $this->course_visible);
            $db->bindValue(':sem_type', $this->course_sem_type);
            $db->execute();
            $endtime = intval($db->fetchFirst()[0]);
        }

        $iteration = 0;
        if (isset($config['SIDDATA_Collector_iteration'])) {
            $iteration = $config['SIDDATA_Collector_iteration'];
        }


        // calculate interval start for this iteration considering the already passed iterations
        $offset = $iteration * $this->time_offset;
        // real interval start considering the offset
        $interval_start = $starttime + $offset;
        // end of time interval
        $interval_end = $interval_start + $this->time_offset;
        // postpone end time because endtime is set with the start date of a course
        $endtime += $this->time_offset;

        // init data arrays
        $courses = [
            'data' => []
        ];
        $coursedates = [
            'data' => []
        ];
        $institutes = [
            'data' => []
        ];
        $subjects = [
            'data' => []
        ];
        $degrees = [
            'data' => []
        ];


        // collect course data
        // WARNING: The following SQL statement will currently NOT RETURN courses with unlimited duration (c.duration_time = -1)
        $query = "
            SELECT c.Seminar_id,
                c.name,
                c.beschreibung,
                c.ort,
                c.start_time,
                c.duration_time,
                ss.name AS start_semester,
                es.name AS end_semester
            FROM `seminare` c
            JOIN `semester_data` ss ON ss.beginn = c.start_time
            JOIN `semester_data` es ON es.beginn = (c.start_time + c.duration_time)
            WHERE c.start_time >= :interval_start
                AND c.start_time <= :interval_end
                AND c.start_time <= :endtime
                AND c.visible = :visible
                AND c.status in
                    (SELECT t.id
                    FROM `sem_types` t
                    WHERE class = :sem_type)
            ORDER BY c.start_time, c.Seminar_id";

        $db = DBManager::get()->prepare($query);
        $db->bindValue(':interval_start', $interval_start);
        $db->bindValue(':interval_end', $interval_end);
        $db->bindValue(':endtime', $endtime);
        $db->bindValue(':visible', $this->course_visible);
        $db->bindValue(':sem_type', $this->course_sem_type);
        $db->execute();
        $db_data = $db->fetchAll();

        // format data for backend
        foreach ($db_data as $record) {
            $course = [
                'type' => 'Course',
                'attributes' => [
                    'name' => $record['name'],
                    'description' => $record['beschreibung'],
                    'place' => $record['ort'],
                    'start_time' => intval($record['start_time']),
                    'end_time' => intval($record['start_time']) + intval($record['duration_time']),
                    'start_semester' => $record['start_semester'],
                    'end_semester' => $record['end_semester'],
                    'url' => URLHelper::getLink('dispatch.php/course/details', ['sem_id' => $record['Seminar_id']]),
                    'studip_id' => $record['Seminar_id']
                ]
            ];
            $courses['data'][] = $course;
        }

        $query = "
            SELECT d.termin_id AS coursedate,
                d.date AS coursedate_date,
                d.end_time AS coursedate_endtime,
                d.range_id AS course_id,
                t.issue_id AS topic_id,
                t.title AS topic_title,
                t.description AS topic_description
            FROM `termine` d
            LEFT OUTER JOIN `themen_termine` dt ON dt.termin_id = d.termin_id
            LEFT OUTER JOIN `themen` t ON dt.issue_id = t.issue_id
            WHERE d.date >= :interval_start
                AND d.date <= :interval_end
                AND d.date <= :endtime
            ORDER BY d.date, d.termin_id
        ";

        $db = DBManager::get()->prepare($query);
        $db->bindValue(':interval_start', $interval_start);
        $db->bindValue(':interval_end', $interval_end);
        $db->bindValue(':endtime', $endtime);
        $db->execute();
        $db_data = $db->fetchAll();

        $course_ids = array_column(array_column($courses['data'], "attributes"), "studip_id");
        foreach ($db_data as $record) {
            if (in_array($record['course_id'], $course_ids)) {
                $coursedate = [
                    'type' => 'Event',
                    'attributes' => [
                        'start_time' => intval($record['coursedate_date']),
                        'end_time' => intval($record['coursedate_endtime']),
                        'topic_title' => $record['topic_title'],
                        'topic_description' => $record['topic_description'],
                        'studip_id' => $record['coursedate']
                    ],
                    'relationships' => [
                        'course' => [
                            'data' => [
                                [
                                    'type' => 'StudipCourse',
                                    'id' => $record['course_id']
                                ]
                            ]
                        ]
                    ]
                ];

                $coursedates['data'][] = $coursedate;
            }
        }


        $query = "
            SELECT `Institut_id` AS `id`,
                `Name` AS `name`,
                `url`
            FROM `Institute`
            ORDER BY `id`;
        ";

        $db = DBManager::get()->prepare($query);
        $db->execute();
        $db_data = $db->fetchAll();

        foreach ($db_data as $record) {
            $institute = [
                'type' => 'Institute',
                'attributes' => [
                    'name' => $record['name'],
                    'url' => $record['url'],
                    'studip_id' => $record['id']
                ]
            ];

            $institutes['data'][] = $institute;
        }

        $institute_ids = array_column(array_column($institutes['data'], "attributes"), "studip_id");

        // collect courses' lecturers
        $query = "
            SELECT
                   su.user_id,
                   su.Seminar_id,
                   status,
                   title_front,
                   title_rear,
                   u.Vorname,
                   u.Nachname
            FROM seminar_user AS su
            INNER JOIN auth_user_md5 AS u USING (user_id)
            INNER JOIN user_info USING (user_id)
            INNER JOIN user_inst USING (user_id)
            WHERE seminar_id IN (:cids)
                AND su.visible = 'yes'
                AND su.status = 'dozent'
        ";

        $db = DBManager::get()->prepare($query);
        $db->bindValue(':cids', $course_ids);
        $db->execute();
        $db_data = $db->fetchAll();

        $lecturers = [
            'data' => []
        ];
        foreach ($db_data as $record) {
            $poid = $client->getCrypter()->std_encrypt($record['user_id']);
            $lecturer = [
                'type' => 'Lecturer',
                'attributes' => [
                    'title' => $record['title_front'] . " " . $record['title_rear'],
                    'person_origin_id' => $poid,
                    'first_name' => $record['Vorname'],
                    'surname' => $record['Nachname']
                ],
            ];

            // add lecturer to lecturers
            if (!in_array($lecturer['attributes']['person_origin_id'], array_column(array_column($lecturers['data'], 'attributes'), 'person_origin_id'))) {
                $lecturers['data'][] = $lecturer;
            }

            // add lecturer to course
            $course_key = array_search($record['seminar_id'], array_column(array_column($courses['data'], 'attributes'), 'studip_id'));
            if (!array_key_exists('relationships', $courses['data'][$course_key])) {
                $courses['data'][$course_key]['relationships'] = [];
            }
            if (empty($courses['data'][$course_key]['relationships']) or !array_key_exists('lecturers', $courses['data'][$course_key]['relationships'])) {
                // initialize relationship if it doesn't already exist
                $courses['data'][$course_key]['relationships']['lecturers'] = [
                    'data' => []
                ];
            }

            // add lecturer to course
            $courses['data'][$course_key]['relationships']['lecturers']['data'][] = [
                'type' => 'Person',
                'id' => $poid
            ];
        }


        // collect courses' institutes
        $query = "
            SELECT si.seminar_id, si.institut_id
            FROM `seminar_inst` si
            WHERE si.seminar_id in (:s_ids)
                AND  si.institut_id in (:i_ids);
        ";

        $db = DBManager::get()->prepare($query);
        $db->bindValue(':s_ids', $course_ids);
        $db->bindValue(':i_ids', $institute_ids);
        $db->execute();
        $db_data = $db->fetchAll();

        // add relations to course objects
        foreach ($db_data as $record) {
            $course_key = array_search($record['seminar_id'], array_column(array_column($courses['data'], 'attributes'), 'studip_id'));
            $courses['data'][$course_key]['relationships']['institute'] = [
                'data' => [
                    [
                        'type' => 'Institute',
                        'id' => $record['institut_id']
                    ]
                ]
            ];

        }

        $query = "
            SELECT `fach_id` AS `id`,
                `name`,
                `beschreibung` AS `description`,
                `schlagworte` AS `keywords`
            FROM `fach`
            ORDER BY `id`;
        ";

        $db = DBManager::get()->prepare($query);
        $db->execute();
        $db_data = $db->fetchAll();

        foreach ($db_data as $record) {
            $subject = [
                'type' => 'Subject',
                'attributes' => [
                    'name' => $record['name'],
                    'description' => $record['description'],
                    'keywords' => $record['keywords'],
                    'studip_id' => $record['id']
                ]
            ];

            $subjects['data'][] = $subject;
        }

        $query = "
            SELECT `abschluss_id` AS `id`,
                `name`,
                `beschreibung` AS `description`
            FROM `abschluss`
            ORDER BY `id`;
        ";

        $db = DBManager::get()->prepare($query);
        $db->execute();
        $db_data = $db->fetchAll();

        foreach ($db_data as $record) {
            $degree = [
                'type' => 'Degree',
                'attributes' => [
                    'name' => $record['name'],
                    'description' => $record['description'],
                    'studip_id' => $record['id']
                ]
            ];

            $degrees['data'][] = $degree;
        }


        /* ========================= BREAK CONDITION ========================= */
        // interval end goes beyond collection range
        $complete = $interval_start >= $endtime;


        // POST data
        $all_passed = true;

        $dateFormat = "Y-m-d H:i:s";

        if (($instituteQuatnity = count($institutes['data'])) > 0) {
            echo("\nÜbertragung von Einrichtungs-, Fach- und Abschlussdaten.\n");
            $json_institutes = SiddataDataManager::json_encode($institutes);
            $response = $client->postInstitutes($json_institutes);
            if ($response['http_code'] == 200)  {
                echo("Gesendete Einrichtungen: " . $instituteQuatnity . "\n");
            } else {
                $all_passed = false;
                echo("Fehler! Einrichtungen konnten nicht gesendet werden.\n");
                echo('HTTP-Code: ' . $response["http_code"] . "\n");
            }
        }

        if (($lecturerQuantity = count($lecturers['data'])) > 0) {
            $json_lecturers = SiddataDataManager::json_encode($lecturers);
            $response = $client->postLecturers($json_lecturers);
            if ($response['http_code'] == 200) {
                echo("Gesendete Dozierende: " . $lecturerQuantity . "\n");
            } else {
                $all_passed = false;
                echo("Fehler! Dozierende konnten nicht gesendet werden.\n");
                echo('HTTP-Code: ' . $response['http_code'] . "\n");
            }
        }

        echo("Übertragung von Veranstaltungs-/Termin-Daten \n zwischen ". date($dateFormat, $interval_start) ." und ". date($dateFormat, $interval_end) ." (Ende: ". date($dateFormat, $endtime) .").\n");
        $json_courses = SiddataDataManager::json_encode($courses);
        $courseQuantity = count($courses['data']);
        if ($courseQuantity  > 0) {
            $response = $client->postCourses($json_courses, $complete);
            if ($response['http_code'] == 200) {
                echo("Gesendete Veranstaltungen: " . $courseQuantity . "\n");
            } else {
                $all_passed = false;
                echo("Fehler! Veranstaltungen konnten nicht gesendet werden.\n");
                echo('HTTP-Code: ' . $response["http_code"] . "\n");
            }

            $json_coursedates = SiddataDataManager::json_encode($coursedates);
            if (($eventQuantity = count($coursedates['data'])) > 0) {
                $response = $client->postCourseDates($json_coursedates, $complete);
                if ($response['http_code'] == 200) {
                    echo("Gesendete Termine: " . $eventQuantity . "\n");
                } else {
                    $all_passed = false;
                    echo("Fehler! Termine konnten nicht gesendet werden.\n");
                    echo('HTTP-Code: ' . $response["http_code"] . "\n");
                }
            }
        } else {
            echo("Keine Veranstaltungs-Daten im Zeitraum mit den Kriterien (sem_type=".$this->course_sem_type.", visible=".$this->course_visible.") gefunden.\n");
        }

        if (($subjectQuantity = count($subjects['data'])) > 0) {
            $json_subjects = SiddataDataManager::json_encode($subjects);
            $response = $client->postSubjects($json_subjects);
            if ($response['http_code'] == 200)  {
                echo("Gesendete Fächer: " . $subjectQuantity . "\n");
            } else {
                $all_passed = false;
                echo("Fehler! Fächer konnten nicht gesendet werden.\n");
                echo('HTTP-Code: ' . $response["http_code"] . "\n");
            }
        }

        if (($degreeQuantity = count($degrees['data'])) > 0) {
            $json_degrees = SiddataDataManager::json_encode($degrees);
            $response = $client->postDegrees($json_degrees);
            if ($response['http_code'] == 200)  {
                echo("Gesendete Abschlüsse: " . $degreeQuantity . "\n");
            } else {
                $all_passed = false;
                echo("Fehler! Abschlüsse konnten nicht gesendet werden.\n");
                echo('HTTP-Code: ' . $response["http_code"] . "\n");
            }
        }

        if ($all_passed) {

            // delete all one-time schedules
            $task = SiddataCronjob::register();
            $schedules = CronjobSchedule::findByTask_id($task->id);
            foreach ($schedules as $schedule) {
                if($schedule->type == "once") {
                    $schedule->delete();
                }
            }

            if ($complete) {
                // reset iteration counter
                $config->store('SIDDATA_Collector_iteration', 0);

                // if all courses were collected, shorten collect interval
                $curr_sem_start = Semester::findCurrent()->beginn;
                if (!$config['SIDDATA_Collector_startyear']) {
                    // year of last semester
                    $new_start = $curr_sem_start - $this->time_offset;
                    $new_start_string = date("Y", $new_start);
                    $config->store('SIDDATA_Collector_startyear', $new_start_string);
                }
                if (!$config['SIDDATA_Collector_endyear']) {
                    // ensure next 2 semesters are collected as well
                    $new_end = $curr_sem_start + 3 * $this->time_offset;
                    $new_end_string = date("Y", $new_end);
                    $config->store('SIDDATA_Collector_endyear', $new_end_string);
                }

                echo("\nENDE: Übertragung ERFOLGREICH abgeschlossen. Warten bis zur nächsten regelmäßigen Ausführung.");
            } else {
                // register once again 30 minutes from now
                $schedule = $task->scheduleOnce(time() + (60 * 30));
                $schedule->activate();

                $config->store('SIDDATA_Collector_iteration', $iteration + 1);
                echo("\nÜbertragung pausiert! \n\n");
                echo("Dieser Teil der Daten wurde ERFOLGREICH übertragen.\n");
                echo("Die Übertragung wird in 30 Minuten mit einer erneuten Ausführung des Cronjobs fortgesetzt.");
            }
        }


        if ($config['SIDDATA_Debug_Info']) {
            // dump objects - additional dump compared to serialized dump made in SiddataRestClient
            SiddataDebugLogger::dataDump(serialize($courses), $interval_start.'-'.$interval_end.'_serialized_courses.txt', get_class());
            SiddataDebugLogger::dataDump(serialize($coursedates), $interval_start.'-'.$interval_end.'_serialized_coursedates.txt', get_class());
            SiddataDebugLogger::dataDump(serialize($institutes), $interval_start.'-'.$interval_end.'_serialized_institutes.txt', get_class());
            SiddataDebugLogger::dataDump(serialize($subjects), $interval_start.'-'.$interval_end.'_serialized_subjects.txt', get_class());
            SiddataDebugLogger::dataDump(serialize($degrees), $interval_start.'-'.$interval_end.'_serialized_degrees.txt', get_class());
            SiddataDebugLogger::dataDump($json_courses, $interval_start.'-'.$interval_end.'_courses.json', get_class());
            SiddataDebugLogger::dataDump($json_coursedates, $interval_start.'-'.$interval_end.'_coursedates.json', get_class());
            SiddataDebugLogger::dataDump($json_institutes, $interval_start.'-'.$interval_end.'_institutes.json', get_class());
            SiddataDebugLogger::dataDump($json_subjects, $interval_start.'-'.$interval_end.'_subjects.json', get_class());
            SiddataDebugLogger::dataDump($json_degrees, $interval_start.'-'.$interval_end.'_degrees.json', get_class());
        }
    }
}
