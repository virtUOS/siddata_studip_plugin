<?php

if (!function_exists('array_key_first')) {
    require_once('public/plugins_packages/virtUOS/SiddataPlugin/utils/array_key_first.php');
}

/**
 * Class SiddataController
 * @author Niklas Dettmer <ndettmer@uos.de>
 * @author Sebastian Osada <sebastian.osada@uni.osnabrueck.de>
 * @author Dennis Benz <dbenz@uni-osnabrueck.de>
 * @author Philipp Schüttlöffel <schuettloeffel@zqs.uni-hannover.de>
 *
 * There are two different representations of the data received from brain. First there is the JSON-String in the
 * SiddataCache (inheriting from StudipDbCache). This one is saved in the Stud.IP-Database and persists over 12 hours.
 * Second there is the class structure created from the cached data. It is saved into $this->recommenders which is recreated
 * for each new request as necessary.
 */
class SiddataController extends SiddataControllerAbstract
{

    private $recommenders;
    private $recommendersHandled = [];

    function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        // Redirect to privacy settings if terms not accepted
        if (!UserConfig::get(User::findCurrent()->id)->getValue('SIDDATA_TERMS_ACCEPTED')) {
            $this->redirect("settings/privacy");
            return;
        }

        $this->factory = new Flexi_TemplateFactory($this->plugin->getPluginPath() . '/templates');

        // add specific stylesheet and script - see also SiddataPlugin->perform()
        PageLayout::addScript($this->plugin->getPluginURL() . '/assets/javascripts/activity.js?v=' . $this->plugin->getMetadata()['version']);
        PageLayout::addScript($this->plugin->getPluginURL() . '/assets/javascripts/goals.js?v=' . $this->plugin->getMetadata()['version']);
        PageLayout::addStylesheet($this->plugin->getPluginURL() . '/assets/stylesheets/base.css');

        // Helpbar entry
        Helpbar::Get()->addLink("Häufig gestellte Fragen zu Siddata", $this->link_for('faq/index'));

        // extend sidebar navigation depending on backend data
        // Which recommenders shall be shown?
        $this->buildSidebarNavigation();

        // configuration
        $config = Config::get();

        // build a button for emptying cache if in debug mode
        if ($config['SIDDATA_Debug_Info']) {
            $this->buildEmptyCacheButton();
        }

        // forced feedback configuration
        $_SESSION['SIDDATA_forced_feedback'] = $config['SIDDATA_forced_feedback'];

        // find out if recommender usage should be submitted
        $student = json_decode($this->getManager()->getStudentAsJson(), true)['data'][0];
        $_SESSION['SIDDATA_data_donation'] = $student['attributes']['data_donation'];
    }

    /**
     * Route for displaying start recommender
     * @param string|null $view
     */
    function index_action($view=null)
    {
        $this->recommender_objs = $this->getManager()->getAllRecommender();
        $this->recommender = array_values(array_filter($this->recommender_objs, function($r) { return $r->getName() == 'Startseite'; }))[0];
        $this->enabledRecommenders = $this->countRecommendersEnabled($this->recommender_objs);

        // Layout config
        PageLayout::setTitle('Mein Studienassistent');
        PageLayout::addStylesheet($this->plugin->getPluginURL() . '/assets/stylesheets/index.css');
        PageLayout::addStylesheet($this->plugin->getPluginURL() . '/assets/stylesheets/activity.css');
        PageLayout::addStylesheet($this->plugin->getPluginURL() . '/assets/stylesheets/goal.css');
        Navigation::activateItem('/siddata/assistant/index');

        // viewswidget
        $this->buildViewsWidget($view, "index");

        // main nav information
        $this->setMainNavInformation();
    }

    /**
     * Route for displaying each recommender
     * @param string $recommender_id
     * @param string|null $view
     * @throws Trails_DoubleRenderError
     */
    function recommender_action($recommender_id, $view=null) {
        if (null == $recommender_id
            or  null == $this->recommender = $this->getManager()->findRecommender($recommender_id))
        {
            $this->redirect('siddata/index');
            return;
        }

        $goals = $this->recommender->getGoals();

        $this->goalQuantity = count($goals);

        $this->buildRecommenderContext($goals);

        Navigation::activateItem('/siddata/assistant/' . $recommender_id);

        // viewswidget
        $this->buildViewsWidget($view, "recommender", $recommender_id);
    }

    function all_action($view=null) {
        $this->recommender_objs = $this->getManager()->getAllRecommender();

        $goals = [];
        foreach ($this->recommender_objs as $r) {
            $goals = array_merge($goals, $r->getGoals());
        }

        $this->buildRecommenderContext($goals);

        Navigation::activateItem('/siddata/assistant/all');

        // viewsvidget
        $this->buildViewsWidget($view, 'all');
    }

    /**
     * displays the main navigation question and computes the answer
     * @param string $answer anwser of the question
     * @throws Trails_DoubleRenderError
     */
    function main_navigation_question_action($answer=null) {
        if (!isset($answer)) {
            // configure page
            PageLayout::setTitle('Bitte entscheide dich');
        } else {
            UserConfig::get(User::findCurrent()->id)->store('SIDDATA_USER_NAV', $answer);
            $this->redirect('siddata/index');
        }
    }

    /**
     * Route for reactivating a new activity (setting it's status from 'new' to 'active')
     * @param $activity_id
     * @param string $context_route route of former action
     * @param string $rec_id ID of user-specific recommender
     * @throws Trails_DoubleRenderError
     */
    function reactivate_action($activity_id, $context_route='index', $rec_id=null) {
        $this->reactivate_activity($activity_id, $context_route, $rec_id);
        $this->processResponse($this->reactivate_activity($activity_id, $context_route, $rec_id),
            'Fehler beim Wiederaufnehmen der Empfehlung.<br>',
            'Die Empfehlung wurde wieder aufgenommen.');
        if($context_route == 'index') {
            $this->redirect('siddata/index');
        } else {
            $this->redirect('siddata/'.$context_route . (isset($rec_id)? '/' . $rec_id: ''));
        }
    }

    /**
     * Route for reactivating a batch of new activities (setting their status from 'new' to 'active')
     * @param $goal_id
     * @param $batch_id
     * @param string $context_route route of former action
     * @param string $rec_id ID of user-specific recommender
     * @throws Trails_DoubleRenderError
     */
    function reactivate_batch_action($goal_id, $batch_id, $context_route='index', $rec_id=null) {
        $batch_activity_ids = $this->getManager()->getBatchActivityIds($goal_id, $batch_id);

        $response_codes = [];
        foreach ($batch_activity_ids as $activity_id) {
            $response_codes[] = $this->reactivate_activity($activity_id, $context_route, $rec_id);
        }
        $this->processResponses($response_codes,
            'Fehler beim Wiederaufnehmen der Empfehlung.<br>',
            'Die Empfehlung wurde wieder aufgenommen.'
        );


        if($context_route == 'index') {
            $this->redirect('siddata/index');
        } else {
            $this->redirect('siddata/'.$context_route . (isset($rec_id)? '/' . $rec_id: ''));
        }
    }

    /**
     * Route for reactivating a new activity (setting it's status from 'new' to 'active')
     * @param $activity_id
     * @param string $context_route route of former action
     * @param string $rec_id ID of user-specific recommender
     * @return array|false|null response code or false
     */
    function reactivate_activity($activity_id, $context_route='index', $rec_id=null) {
        // create patch
        if ($this->getManager()->activityExists($activity_id)) {
            $attributes = [];
            $attributes["status"] = "active";

            $patch = SiddataActivity::createJsonApiPatch($activity_id, $attributes);

            // perform patch
            $used_rec = $_SESSION['SIDDATA_data_donation']? $rec_id: null;
            return $this->getClient()->patchActivity($activity_id, $patch, $used_rec);
        }
        $this->plugin->postError('Diese Empfehlung gibt es nicht.');
        return false;
    }

    /**
     * Route for discarding an activity
     * @param $activity_id
     * @param string $context_route route of former action
     * @param string|null $rec_id ID of user-specific recommender
     * @throws Trails_DoubleRenderError
     */
    function discard_action($activity_id, $context_route='index', $rec_id=null) {
        $fragment = '';
        $this->discard_activity($activity_id, $context_route, $rec_id);
        $this->redirect('siddata/'.$context_route . (isset($rec_id)? '/' . $rec_id: '') . $fragment);
    }

    /**
     * Route for discarding a batch of activity
     * @param $goal_id
     * @param $batch_id
     * @param string $context_route route of former action
     * @param string|null $rec_id ID of user-specific recommender
     * @throws Trails_DoubleRenderError
     */
    function discard_batch_action($goal_id, $batch_id, $context_route='index', $rec_id=null) {
        $fragment = '';

        $batch_activity_ids = $this->getManager()->getBatchActivityIds($goal_id, $batch_id);

        $response_codes = [];
        foreach ($batch_activity_ids as $activity_id) {
            $this->discard_activity($activity_id, $context_route, $rec_id);
        }
        $this->processResponses($response_codes);

        $this->redirect('siddata/'.$context_route . (isset($rec_id)? '/' . $rec_id: '') . $fragment);
    }

    /**
     * Function for discarding an activity
     * @param $activity_id
     * @param string $context_route route of former action
     * @param string|null $rec_id ID of user-specific recommender
     * @return array|false|null response code or false
     */
    function discard_activity($activity_id, $context_route='index', $rec_id=null) {
        // create patch
        if ($this->getManager()->activityExists($activity_id)) {
            $attributes = [];
            $attributes["status"] = "discarded";

            $patch = SiddataActivity::createJsonApiPatch($activity_id, $attributes);

            // get fragment of activity
            $activity = $this->getManager()->findActivity($activity_id);
            $last_position_fragment = $this->getFragment($activity);
            if ($last_position_fragment) {
                $last_position_link = $this->link_for('siddata/'.$context_route . (isset($rec_id)? '/' . $rec_id: '') . $last_position_fragment);
            }

            // perform patch
            $used_rec = $_SESSION['SIDDATA_data_donation']? $rec_id: null;
            $response = $this->getClient()->patchActivity($activity_id, $patch, $used_rec);
            if ($last_position_fragment) {
                $response['response'] .= ' <a href="'.$last_position_link.'" style="'.Icon::create("arr_2down", Icon::ROLE_CLICKABLE)->asCSS(15).'; margin-left: 10px; padding-left: 20px; background-repeat: no-repeat; background-position: 0 -1px;">Zur letzten Position</a><br>';
            }
            return $response;

        }
        $this->plugin->postError('Diese Empfehlung gibt es nicht.');
        return false;
    }

    /**
     * Route for deleting an activity
     * @param $activity_id
     * @param string $context_route route of former action
     * @param string|null $rec_id ID of user-specific recommender
     * @throws Trails_DoubleRenderError
     */
    function delete_action($activity_id, $context_route='index', $rec_id=null) {
        $this->delete_activity($activity_id, $context_route, $rec_id);
        $this->redirect('siddata/'.$context_route . (isset($rec_id)? '/' . $rec_id: ''));
    }

    /**
     * Route for deleting an activity
     * @param $goal_id
     * @param $batch_id
     * @param string $context_route route of former action
     * @param string|null $rec_id ID of user-specific recommender
     * @throws Trails_DoubleRenderError
     */
    function delete_batch_action($goal_id, $batch_id, $context_route='index', $rec_id=null) {
        $batch_activity_ids = $this->getManager()->getBatchActivityIds($goal_id, $batch_id);

        $response_codes = [];
        foreach ($batch_activity_ids as $activity_id) {
            $this->delete_activity($activity_id, $context_route, $rec_id);
        }
        $this->processResponses($response_codes);

        $this->redirect('siddata/'.$context_route . (isset($rec_id)? '/' . $rec_id: ''));
    }

    /**
     * Route for deleting an activity
     * @param $activity_id
     * @param string $context_route route of former action
     * @param string|null $rec_id ID of user-specific recommender
     * @return array|null response code or false
     */
    function delete_activity($activity_id, $context_route='index', $rec_id=null) {
        // add feature information to request
        $used_rec = $_SESSION['SIDDATA_data_donation']? $rec_id: null;
        return $this->getClient()->deleteActivity($activity_id, $used_rec);
    }

    /**
     * Route for temporarily discarding an activity (setting it's status to 'snoozed')
     * @param $activity_id
     * @param string $context_route route of former action
     * @param string $rec_id ID of user-specific recommender
     * @throws Trails_DoubleRenderError
     */
    function snooze_action($activity_id, $context_route='index', $rec_id=null) {
        $this->snooze_activity($activity_id, $context_route, $rec_id);
        $this->redirect('siddata/'.$context_route . (isset($rec_id)? '/' . $rec_id: ''));
    }

    /**
     * Route for temporarily discarding an activity (setting it's status to 'snoozed')
     * @param $goal_id
     * @param $batch_id
     * @param string $context_route route of former action
     * @param string $rec_id ID of user-specific recommender
     * @throws Trails_DoubleRenderError
     */
    function snooze_batch_action($goal_id, $batch_id, $context_route='index', $rec_id=null) {
        $batch_activity_ids = $this->getManager()->getBatchActivityIds($goal_id, $batch_id);

        $response_codes = [];
        foreach ($batch_activity_ids as $activity_id) {
            $this->snooze_activity($activity_id, $context_route, $rec_id);
        }
        $this->processResponses($response_codes);

        $this->redirect('siddata/'.$context_route . (isset($rec_id)? '/' . $rec_id: ''));
    }

    /**
     * Route for temporarily discarding an activity (setting it's status to 'snoozed')
     * @param $activity_id
     * @param string $context_route route of former action
     * @param string $rec_id ID of user-specific recommender
     * @return array|false|null response code or false
     */
    function snooze_activity($activity_id, $context_route='index', $rec_id=null) {
        // create patch
        if ($this->getManager()->activityExists($activity_id)) {
            $attributes = [];
            $attributes['status'] = 'snoozed';
            $patch = SiddataActivity::createJsonApiPatch($activity_id, $attributes);

            // get fragment of activity
            $activity = $this->getManager()->findActivity($activity_id);
            $last_position_fragment = $this->getFragment($activity);
            if ($last_position_fragment) {
                $last_position_link = $this->link_for('siddata/'.$context_route . (isset($rec_id)? '/' . $rec_id: '') . $last_position_fragment);
            }

            // perform patch
            $used_rec = $_SESSION['SIDDATA_data_donation']? $rec_id: null;
            $response = $this->getClient()->patchActivity($activity_id, $patch, $used_rec);
            if ($last_position_fragment) {
                $response['response'] .= ' <a href="'.$last_position_link.'" style="'.Icon::create("arr_2down", Icon::ROLE_CLICKABLE)->asCSS(15).'; margin-left: 10px; padding-left: 20px; background-repeat: no-repeat; background-position: 0 -1px;">Zur letzten Position</a><br>';
            }
            return $response;
        }
        $this->plugin->postError('Diese Empfehlung gibt es nicht.');
        return false;
    }

    /**
     * Route for submitting activity-related rating feedback to Siddata
     * @param int $value
     * @param string $activity_id
     * @param string $context_route route of former action
     * @param string $rec_id ID of user-specific recommender
     * @throws Trails_DoubleRenderError
     */
    function feedback_action($value, $activity_id, $context_route='index', $rec_id=null) {
        // create patch
        if ($this->getManager()->activityExists($activity_id)) {
            $attributes = [];
            $attributes["feedback_value"] = (int) $value;

            $patch = SiddataActivity::createJsonApiPatch($activity_id, $attributes);

            // perform patch
            $used_rec = $_SESSION['SIDDATA_data_donation']? $rec_id: null;
            $this->processResponse($this->getClient()->patchActivity($patch, $used_rec),
                'Fehler beim Senden des Feedbacks.<br>',
                'Vielen Dank für das Feedback!');
        } else {
            $this->plugin->postError('Diese Empfehlung gibt es nicht.');
        }

        $this->redirect('siddata/'.$context_route . (isset($rec_id)? '/' . $rec_id: ''));
    }

    /**
     * Route for patches concerning properties of activity objects
     * @param $activity_id
     * @param string $context_route route of former action
     * @param string|null $rec_id
     * @throws Trails_DoubleRenderError
     */
    function activity_action($activity_id, $context_route='index', $rec_id=null) {
        // determine feature identifier
        $used_rec = $_SESSION['SIDDATA_data_donation']? $rec_id: null;

        $activity = $this->getManager()->findActivity($activity_id);
        // check if activity exists
        if ($activity) {
            $success_msg = "";
            $attributes = [];
            if ($activity['type'] == 'question') {
                $success_msg .= "Die Frage wurde beantwortet. ";
                if (Request::get('siddata-answer_' . $activity_id)) {
                    // question with single answer
                    $question = $activity->getQuestion();
                    if ($question->getType() == 'date') {
                        $date = Request::get('siddata-answer_' . $activity_id);
                        $attributes['answers'] = [DateTime::createFromFormat("d.m.Y", $date)->getTimestamp()];
                    } else {
                        $attributes['answers'] = [Request::get('siddata-answer_' . $activity_id)];
                    }
                } else if (Request::getArray('siddata-answer_' . $activity_id)) {
                    // question with multiple answers
                    $question = $activity->getQuestion();
                    if ($question->getType() == 'datetime') {
                        $answers = Request::getArray('siddata-answer_' . $activity_id);
                        $attributes['answers'] = [DateTime::createFromFormat("d.m.Y G:i", $answers['date']." ".$answers['time'])->getTimestamp()];
                    } else {
                        $send_answers = [];
                        $poss_answers = $question->getSelectionAnswers();
                        if (!empty($poss_answers)) {
                            // we have a selection
                            foreach (Request::getArray('siddata-answer_'. $activity_id) as $index) {
                                $send_answers[] = $poss_answers[$index];
                            }
                        } else {
                            // we have free text answers
                            $send_answers = Request::getArray('siddata-answer_' . $activity_id);
                        }
                        $attributes['answers'] = $send_answers;
                    }
                } else {
                    // no answer submitted
                    PageLayout::postInfo("Es muss mindestens eine Antwort angegeben werden.");
                }
            } elseif ($activity['type'] == 'person' and $activity['person']->isEditable()) {
                // POST person
                $p_attributes = [];

                $file_to_upload = 'siddata-person-image_'.$activity_id;
                $upload_ok = true;
                if (isset($_FILES[$file_to_upload]) and isset($_FILES[$file_to_upload]['name'])) {
                    $tmp_file_name = $_FILES[$file_to_upload]['tmp_name'];

                    if ($_FILES[$file_to_upload]['error'] == UPLOAD_ERR_OK and is_uploaded_file($tmp_file_name)) {
                        $uid = User::findCurrent()->getId();
                        $file_name = $_FILES[$file_to_upload]['name'];
                        $file_ext = "." . strtolower(pathinfo($file_name,PATHINFO_EXTENSION));
                        $save_name = $this->getClient()->getCrypter()->std_encrypt("image_" . $activity_id . $uid) . $file_ext;

                        $file_content = base64_encode(file_get_contents($tmp_file_name));

                        $p_attributes['image'] = $file_content;
                        $p_attributes['image_name'] = $save_name;
                    } else {
                        $upload_ok = false;
                    }
                } elseif (Request::get('siddata-person-delete-image_' . $activity_id)) {
                    $p_attributes['image'] = null;
                    $p_attributes['image_name'] = null;
                }

                if (!$upload_ok) {
                    PageLayout::postError("Beim Hochladen des Bildes ist ein Fehler aufgetreten.");
                    if ($this->debugEnabled()) {
                        SiddataDebugLogger::log($_FILES[$file_to_upload]);
                    }
                }

                if ($p_firstname = Request::get('siddata-person-firstname_'.$activity_id)) {
                    $p_attributes['first_name'] = $this->symmetric_encrypt(trim($p_firstname));
                }
                if ($p_secondname = Request::get('siddata-person-secondname_'.$activity_id)) {
                    $p_attributes['surname'] = $this->symmetric_encrypt(trim($p_secondname));
                }
                if ($p_description = Request::get('siddata-person-description_'.$activity_id)) {
                    $p_attributes['description'] = $this->symmetric_encrypt(trim($p_description));
                }
                if ($p_email = Request::get('siddata-person-email_'.$activity_id)) {
                    $p_attributes['email'] = trim($p_email);
                }

                $p_attributes['user_origin_id'] = $this->getClient()->getCrypter()->std_encrypt(User::findCurrent()->getId());

                if (count($p_attributes) > 0) {
                    $person = [
                        'type' => 'Person',
                        'attributes' => $p_attributes
                    ];
                    $data = SiddataDataManager::json_encode(
                        [
                            'data' => [$person],
                            'relationships' => [
                                'activity' => [
                                    'data' => [
                                        [
                                            'type' => 'Activity',
                                            'id' => $activity_id
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    );

                    $response = $this->getClient()->postPerson($data, $rec_id);
                    $this->processResponse($response,
                        "Fehler beim Absenden der Personendaten.<br>",
                        "Die Personendaten wurden erfolgreich übermittelt."
                    );
                }

            }

            // prevent sending of no answers
            if ($activity['type'] != 'question' or $attributes['answers']) {
                // Shall the activity be finalized?
                if (Request::get('siddata-done')) {
                    $attributes['status'] = 'done';
                    $success_msg .= "Die Empfehlung wurde abgeschlossen. ";
                }

                // add feedback
                $feedback_value = Request::get('siddata-feedback-' . $activity_id);
                $feedback_text = Request::get('siddata-feedback-text-input_' . $activity_id);
                if ($feedback_value or $feedback_text) {
                    $success_msg .= "Vielen Dank für das Feedback!";
                    if($feedback_value) {
                        $attributes['feedback_value'] = (int) $feedback_value;
                    }
                    if($feedback_text) {
                        $attributes['feedback_text'] = $feedback_text;
                    }
                }
                // get fragment of activity
                $last_position_fragment = $this->getFragment($activity);
                if ($last_position_fragment) {
                    $last_position_link = $this->link_for('siddata/'.$context_route . (isset($rec_id)? '/' . $rec_id: '') . $last_position_fragment);
                }

                $patch = SiddataActivity::createJsonApiPatch($activity_id, $attributes);

                // perform patch
                $response = $this->getClient()->patchActivity($activity_id, $patch, $used_rec);
                if ($last_position_fragment) {
                    $response['response'] .= ' <a href="'.$last_position_link.'" style="'.Icon::create("arr_2down", Icon::ROLE_CLICKABLE)->asCSS(15).'; margin-left: 10px; padding-left: 20px; background-repeat: no-repeat; background-position: 0 -1px;">Zur letzten Position</a><br>';
                }
                $this->processResponse($response,
                    'Fehler beim Senden der Activity.<br>',
                    $success_msg);
            }
        } else {
            $this->plugin->postError('Diese Empfehlung gibt es nicht.');
        }

        $this->redirect('siddata/'.$context_route . (isset($rec_id)? '/' . $rec_id: ''));
    }

    /**
     * Route for limiting displayed activities on current site
     * @param string $context_route route of former action
     * @param string $rec_id ID of user-specific recommender
     * @throws Trails_DoubleRenderError
     */
    function limit_action($context_route='index', $rec_id=null) {
        // CURRENTLY NOT IN USE (05.05.20)

        $_SESSION['SIDDATA_limit'] = (int) Request::get('siddata-limit');
        $this->redirect('siddata/'.$context_route . (isset($rec_id)? '/' . $rec_id: ''));
    }

    /**
     * Route for creating new professional goals
     * @param string $context_route route of former action
     * @param string $rec_id ID of user-specific recommender
     * @throws Trails_DoubleRenderError
     */
    function post_profession_action($context_route='index', $rec_id=null) {
        $goal = Request::get('siddata-goal');
        if (isset($goal)) {
            $goal_arr = ["goal" => $goal, "list_id" => "professions"];
            $data = SiddataDataManager::json_encode($goal_arr);

            // submit data
            $this->processResponse($this->getClient()->sendProfession($data),
                'Fehler beim Speichern des Interesses.<br>',
                'Dein Interesse wurde gespeichert.');
        }
        $this->redirect('siddata/'.$context_route . (isset($rec_id)? '/' . $rec_id: ''));
    }

    /**
     * Route for creating new todo-activities
     * @param string $context_route route of former action
     * @param string $rec_id ID of user-specific recommender
     * @throws Trails_DoubleRenderError
     */
    function post_todo_action($context_route='index', $rec_id=null) {
        $todo = Request::get('siddata-todo');
        if (isset($todo)) {
            $data = SiddataDataManager::json_encode(["todo" => $todo]);

            // submit data
            $this->processResponse($this->getClient()->sendTodo($data),
                'Fehler beim Speichen der Aufgabe.<br>',
                'Meine Aufgabe wurde gespeichert.');
        }
        $this->redirect('siddata/'.$context_route . (isset($rec_id)? '/' . $rec_id: ''));
    }

    /**
     * Route for confirmation dialog of a goal deletion
     * @param $goal_id
     * @param string $context_route route of former action
     * @param string $rec_id ID of user-specific recommender
     */
    function delete_goal_confirm_action($goal_id, $context_route='index', $rec_id=null) {
        // configure page
        PageLayout::setTitle('Bitte die Aktion bestätigen');
        PageLayout::addStylesheet($this->plugin->getPluginURL() . '/assets/stylesheets/activity.css?v=' . $this->plugin->getMetadata()['version']);

        // set template variables
        $this->goal_id = $goal_id;
        $this->context_route = $context_route;
        $this->rec_id = $rec_id;
    }

    /**
     * Route for updating goal objects (see also activity_action)
     * @param string $prop_id
     * @param string $context_route
     * @param string|null $rec_id
     * @throws Trails_DoubleRenderError
     */
    function goal_action ($prop_id, $context_route='index', $rec_id=null) {
        $prop_val = Request::get('siddata-edit-goal-property_' . $prop_id);
        $used_rec = $_SESSION['SIDDATA_data_donation']? $rec_id: null;
        if ($prop_val) {
            if ($this->getManager()->propertyExists($prop_id)) {
                $prop = $this->getManager()->findProperty($prop_id);
                $prop['value'] = $prop_val;
                $patch = [];
                $patch['id'] = $prop['goal'];
                $patch['properties'][$prop_id] = $prop;
                if ($this->getClient()->patchGoal(SiddataDataManager::json_encode($patch), $used_rec)['http_code'] != 200) {
                    $this->plugin->postError();
                }
            } else {
                $this->plugin->postError('Diese Empfehlung gibt es nicht.');
            }
        }
        $this->redirect('siddata/'.$context_route . (isset($rec_id)? '/' . $rec_id: ''));
    }

    /**
     * Route for deleting a goal
     * @param $goal_id
     * @param string $context_route route of former action
     * @param string $rec_id ID of user-specific recommender
     * @throws Trails_DoubleRenderError
     */
    function delete_goal_action($goal_id, $context_route='index', $rec_id=null) {
        $used_rec = $_SESSION['SIDDATA_data_donation']? $rec_id: null;
        $response = $this->getClient()->deleteGoal($goal_id, $used_rec);
        $this->processResponse($response,
            'Fehler beim Löschen des Zieles.<br>',
            'Das Ziel wurde gelöscht.');
        if ($response['http_code'] == 200) {
            $this->getManager()->invalidateCache();
        }
        $this->redirect('siddata/'.$context_route . (isset($rec_id)? '/' . $rec_id: ''));
    }

    /**
     * Filter activities by status
     * @param string $view new view
     * @param string $context_route
     * @param string|null $rec_id
     * @throws Trails_DoubleRenderError
     */
    function change_view_action($view, $context_route='index', $rec_id=null) {
        $_SESSION['SIDDATA_view'] = $view;
        $this->redirect('siddata/' . $context_route . (isset($rec_id)? '/' . $rec_id: '') . '/' . $view);
    }

    /**
     * Empty plugin db cache (only visible in debug mode)
     * @throws Trails_DoubleRenderError
     */
    function empty_cache_action() {
        $this->getManager()->invalidateCache();
        $this->redirect('siddata/index');
    }

    /**
     * Form page for creating a todo activity
     * @param string $recommender_id id of the current recommender
     */
    function activity_form_action($recommender_id) {
        PageLayout::setTitle('To-do hinzufügen');
        PageLayout::addStylesheet($this->plugin->getPluginURL() . '/assets/stylesheets/form.css?v=' . $this->plugin->getMetadata()['version']);

        $this->recommender_id = $recommender_id;
        $this->goals = $this->getManager()->findRecommender($recommender_id)->getGoals();
    }

    /**
     * Submit action for new todo activities
     * @throws Trails_DoubleRenderError
     */
    function activity_submit_action() {
        $todo = [];
        $todo['title'] = Request::get('siddata-activity-title-input');
        $todo['description'] = Request::get('siddata-activity-description-input');
        $date = Request::get('ende');
        if ($date) {
            $todo['duedate'] = DateTime::createFromFormat("d.m.Y G:i", $date)->getTimestamp();
        }
        $todo['goal'] = Request::get('siddata-activity-goal-input');

        // hidden properties
        $todo['type'] = 'todo';
        $todo['feedback_size'] = 0;
        $todo['status'] = 'new';

        $this->processResponse($this->getClient()->sendTodo(SiddataDataManager::json_encode($todo)),
            'Fehler beim Erstellen der To-Do.<br>',
            'Mein To-Do wurde gespeichert.');

        $this->redirect('siddata/todos');
    }

    /**
     * Action for a questionnaire submit
     * @param string $goal_id
     * @param string $context_route
     * @param string|null $rec_id
     * @throws Trails_DoubleRenderError
     */
    function questionnaire_action($goal_id, $context_route='index', $rec_id=null) {
        if ($answers = Request::getArray('siddata-questionnaire-answer_'.$goal_id)) {
            $used_rec = $_SESSION['SIDDATA_data_donation']? $rec_id: null;
            $success = true;
            $success_msg = "Die Empfehlung wurde bearbeitet.";

            $feedback_text = null;
            $feedback_value = null;
            $sample_activity = $this->getManager()->findActivity(array_key_first($answers));
            if (isset($sample_activity['form'])) {
                $feedback_value = Request::get('siddata-batch-feedback-' . $goal_id . $sample_activity['form']);
                $feedback_text = Request::get('siddata-batch-feedback-text-input_' . $goal_id . $sample_activity['form']);
            }
            if ($feedback_text or $feedback_value) {
                $success_msg .= "Vielen Dank für das Feedback!";
            }

            foreach ($answers as $activity_id => $answer) {
                $attributes = [];
                $activity = $this->getManager()->findActivity($activity_id, false);
                if ($activity['type'] == 'question') {
                    if (is_array($answer)) {
                        $question = $activity->getQuestion();
                        if ($question->getType() == 'datetime') {
                            $attributes['answers'] = [DateTime::createFromFormat("d.m.Y G:i", $answer['date']." ".$answer['time'])->getTimestamp()];
                        } else {
                            // we have a multi-answer question like checkbox or multitext
                            $send_answers = [];

                            $poss_answers = $activity->getQuestion()->getSelectionAnswers();
                            if (!empty($poss_answers)) {
                                foreach ($answer as $a) {
                                    $send_answers[] = $poss_answers[$a];
                                }
                            } else {
                                foreach ($answer as $a) {
                                    $send_answers[] = $a;
                                }
                            }
                            $attributes['answers'] = $send_answers;
                        }
                    } else {
                        $poss_answers = $activity->getQuestion()->getSelectionAnswers();
                        if (!empty($poss_answers)) {
                            // if we have multiple answer options, only the answer's index will be submitted by the form
                            // we currently only support single answer questions (not multiple answers)
                            $attributes['answers'] = [$poss_answers[$answer]];
                        } else {
                            $question = $activity->getQuestion();
                            if ($question->getType() == 'date') {
                                $attributes['answers'] = [DateTime::createFromFormat("d.m.Y", $answer)->getTimestamp()];
                            } else {
                                $attributes['answers'] = [$answer];
                            }
                        }
                    }
                } elseif ($activity['type'] == 'person' and $activity['person']->isEditable()) {
                    // POST person
                    $p_attributes = [];

                    $file_to_upload = 'siddata-person-image_'.$activity_id;
                    $upload_ok = true;
                    if (isset($_FILES[$file_to_upload]) and $_FILES[$file_to_upload]['error'] != UPLOAD_ERR_NO_FILE) {
                        $tmp_file_name = $_FILES[$file_to_upload]['tmp_name'];

                        if ($_FILES[$file_to_upload]['error'] == UPLOAD_ERR_OK and is_uploaded_file($tmp_file_name)) {
                            $uid = User::findCurrent()->getId();
                            $file_name = $_FILES[$file_to_upload]['name'];
                            $file_ext = "." . strtolower(pathinfo($file_name,PATHINFO_EXTENSION));
                            $save_name = $this->getClient()->getCrypter()->std_encrypt("image_" . $activity_id . $uid) . $file_ext;

                            $file_content = base64_encode(file_get_contents($tmp_file_name));

                            $p_attributes['image'] = $file_content;
                            $p_attributes['image_name'] = $save_name;
                        } else {
                            $upload_ok = false;
                        }
                    } elseif (Request::get('siddata-person-delete-image_' . $activity_id)) {
                        $p_attributes['image'] = null;
                        $p_attributes['image_name'] = null;
                    }

                    if (!$upload_ok) {
                        PageLayout::postError("Beim Hochladen des Bildes ist ein Fehler aufgetreten.");
                        if ($this->debugEnabled()) {
                            SiddataDebugLogger::log($_FILES[$file_to_upload]);
                        }
                    }

                    if ($p_firstname = Request::get('siddata-person-firstname_'.$activity_id)) {
                        $p_attributes['first_name'] = $this->symmetric_encrypt(trim($p_firstname));
                    }
                    if ($p_secondname = Request::get('siddata-person-secondname_'.$activity_id)) {
                        $p_attributes['surname'] = $this->symmetric_encrypt(trim($p_secondname));
                    }
                    if ($p_description = Request::get('siddata-person-description_'.$activity_id)) {
                        $p_attributes['description'] = $this->symmetric_encrypt(trim($p_description));
                    }
                    if ($p_email = Request::get('siddata-person-email_'.$activity_id)) {
                        $p_attributes['email'] = trim($p_email);
                    }

                    $p_attributes['user_origin_id'] = $this->getClient()->getCrypter()->std_encrypt(User::findCurrent()->getId());

                    if (count($p_attributes) > 0) {
                        $person = [
                            'type' => 'Person',
                            'attributes' => $p_attributes
                        ];
                        $data = SiddataDataManager::json_encode(
                            [
                                'data' => [$person],
                                'relationships' => [
                                    'activity' => [
                                        'data' => [
                                            [
                                                'type' => 'Activity',
                                                'id' => $activity_id
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        );

                        $response = $this->getClient()->postPerson($data, $rec_id);
                        $this->processResponse($response,
                            "Fehler beim Absenden der Personendaten.<br>",
                            "Die Personendaten wurden erfolgreich übermittelt."
                        );
                    }
                } else {
                    $attributes['status'] = 'done';
                }

                // add feedback
                if ($feedback_value or $feedback_text) {
                    if($feedback_value) {
                        $attributes['feedback_value'] = (int) $feedback_value;
                    }
                    if($feedback_text) {
                        $attributes['feedback_text'] = $feedback_text;
                    }
                }

                $patch = SiddataActivity::createJsonApiPatch($activity_id, $attributes);
                if ($this->getClient()->patchActivity($activity_id, $patch, $used_rec)['http_code'] != 200) {
                    $success = false;
                }
            }
            if ($success) {
                $this->plugin->postSuccess("Die Empfehlung wurde bearbeitet.");
            } else {
                $this->plugin->postError();
            }
        }
        $this->redirect('siddata/'.$context_route . (isset($rec_id)? '/' . $rec_id: ''));
    }

    /**
     * Route for processing the interaction and showing the url
     * @param string $activity_id
     */
    public function show_resource_action($activity_id) {
        $activity = $this->getManager()->findActivity($activity_id);
        // check if activity exists
        if ($activity) {
            $attributes = [];
            $attributes["interactions"] = $activity->getInteractions() + 1;

            $patch = SiddataActivity::createJsonApiPatch($activity_id, $attributes);

            // perform patch
            $response = $this->getClient()->patchActivity($activity_id, $patch);
        }

        $this->redirect($activity->getResource()->getUrl());
    }

    /**
     * Route for processing the email interaction
     * @param string $activity_id
     */
    public function show_email_action($activity_id) {
        $activity = $this->getManager()->findActivity($activity_id);
        // check if activity exists
        if ($activity) {
            $attributes = [];
            $attributes["interactions"] = $activity->getInteractions() + 1;

            $patch = SiddataActivity::createJsonApiPatch($activity_id, $attributes);

            // perform patch
            $response = $this->getClient()->patchActivity($activity_id, $patch);
        }

        $this->render_nothing();
    }

    /**
     * @return array
     */
    public function getRecommendersHandled() {
        return $this->recommendersHandled;
    }

    public function render_activity($activity, $factory, $controller, $context_route) {
        $template = $factory->open('activity');
        $template->set_attribute('activity', $activity);
        $template->set_attribute('controller', $controller);
        $template->set_attribute('context_route', $context_route);

        echo $template->render();
    }

    public function render_form($batch_buffer, $goal, $factory, $controller, $context_route) {
        if (count($batch_buffer) == 1) {
            $controller->render_activity(reset($batch_buffer), $factory, $controller, $context_route);
            return;
        }

        $template = $factory->open('activity_batch');
        $template->set_attribute('activities', $batch_buffer);
        $template->set_attribute('controller', $controller);
        $template->set_attribute('context_route', $context_route);
        $template->set_attribute('factory', $factory);

        if (!isset($goal)) {
            $goal = reset($goal)['goal'];
        }

        echo $template->render();
    }

    /**
     * @return SiddataRestClient
     */
    private function getClient() {
        return $this->plugin->rest_client;
    }


    /**
     * @return SiddataDataManager
     */
    private function getManager() {
        return $this->plugin->data_manager;
    }

    /**
     * @return bool true, if posting debugging information is enabled
     */
    private function debugEnabled() {
        return $this->plugin->debug;
    }

    /**
     * @param SiddataRecommender|array $recommender
     * @return array select-options for the dropdown menu (key: HTML-value, value: displayed text)
     */
    private function getLimitOptions($recommender) {
        // CURRENTLY NOT IN USE (05.05.20)

        $options = [
            -1 => 'alle Empfehlungen anzeigen',
            0 => 'keine Empfehlungen anzeigen',
        ];
        $activity_count = 0;
        foreach ($this->getManager()->getAllGoals() as $goal) {
            foreach($goal['activities'] as $activity) {
                if ($activity['status'] != 'done' and $activity['status'] != 'snoozed' and $activity['status'] != 'discarded')
                $activity_count++;
                if ($activity_count == 1) {
                    $options[1] = "Nur eine Empfehlung anzeigen";
                } else {
                    $options[$activity_count] = "Nur " . $activity_count . " Empfehlungen anzeigen";
                }
            }
        }
        if (count($options) == 3) {
            unset($options[1]);
        }
        return $options;
    }

    private function buildRecommenderContext($goals) {
        PageLayout::setTitle('Mein Studienassistent');
        PageLayout::addStylesheet($this->plugin->getPluginURL() . '/assets/stylesheets/index.css');
        PageLayout::addStylesheet($this->plugin->getPluginURL() . '/assets/stylesheets/activity.css');
        PageLayout::addStylesheet($this->plugin->getPluginURL() . '/assets/stylesheets/goal.css');
        PageLayout::addStylesheet($this->plugin->getPluginURL() . '/assets/stylesheets/questionnaire.css');

        if (SiddataController::containsGoalOfType($goals, 'carousel')) {
            PageLayout::addStylesheet($this->plugin->getPluginURL() . '/assets/stylesheets/slick.css');
            PageLayout::addScript($this->plugin->getPluginURL() . '/assets/javascripts/slick.js');
            PageLayout::addScript($this->plugin->getPluginURL() . '/assets/javascripts/carousel.js');
        }
    }

    /**
     * Builds the sidebar navigation items for received recommenders
     */
    private function buildSidebarNavigation() {
        // get (minimal) info about currently used recommenders
        $data_unencoded = $this->getManager()->getRecommenderAsJson();
        $data = json_decode($data_unencoded, true);
        $status = 'new';
        // create nav items for recommenders
        if (is_array($data)) {
            $ac_total = 0;

            foreach ($data['data'] as $recommender) {
                $recommender_id = $recommender['id'];
                if ($recommender['attributes']['name'] == 'Startseite'
                    or !$recommender['attributes']['enabled']
                    or Navigation::hasItem('/siddata/assistant/' . $recommender_id)) {
                        // do not count the start page
                        continue;
                }

                $nav_item = new Navigation($recommender['attributes']['name']);
                $nav_item->setURL(PluginEngine::getURL(
                    $this->plugin,
                    array(),
                    'siddata/recommender/' . $recommender_id
                ));

                // count recommender activities
                $recommenders_obj = $this->getManager()->extractRecommenders(
                    $this->getManager()->createStructureFromJsonApi($data_unencoded)
                );
                $recommender_obj = array_values(array_filter($recommenders_obj, function($r) use($recommender_id) { return $r->getId() == $recommender_id; }))[0];

                $ac = 0;
                if (isset($recommender_obj)) {
                    $ac = $this->countActivitiesByStatus($recommender_obj, $status);
                    $ac_total += $ac;
                }

                $nav_item->setBadgeNumber($ac);
                $nav_item->setDescription($recommender['attributes']['description']);
                Navigation::addItem('/siddata/assistant/' . $recommender_id, $nav_item);
            }

            $all_nav_item = new Navigation('Alle Empfehlungen');
            $all_nav_item->setURL(PluginEngine::getURL(
                $this->plugin,
                array(),
                'siddata/all'
            ));

            $all_nav_item->setBadgeNumber($ac_total);
            $all_nav_item->setDescription('Sammlung aller Empfehlungen');
            Navigation::addItem('/siddata/assistant/all', $all_nav_item);
        }
    }

    /**
     * Helper function for buildNavigation() to get count of new activities
     * @param $feature
     * @return int number new activities
     */
    private function countNewActivities($feature) {
        $c=0;
        foreach ($feature['goals'] as $goal) {
            foreach ($goal['activities'] as $activitiy) {
                if ($activitiy['status'] == 'new') {
                    $c++;
                }
            }
        }
        return $c;
    }

    /**
     * Counts activities of a recommender by a given status
     * @param SiddataRecommender $recommender
     * @param string $status
     * @return int number activities
     */
    private function countActivitiesByStatus($recommender, $status) {
        $cnt = 0;

        if (null == $recommender) {
            return $cnt;
        }

        foreach($recommender->getGoals() as $goal) {
            $cnt_activities = 0;
            foreach($goal->getActivities() as $activity) {
                switch ($status) {
                    case 'done':case 'snoozed':case 'discarded':case 'new':case 'active':
                        if ($activity['status'] == $status) {
                            $cnt_activities++;
                        }
                        break;
                    case 'all':case 'open':default:
                        if ($activity['status'] == 'new' or $activity['status'] == 'active') {
                            $cnt_activities++;
                        }
                    break;
                }
            }
            if ($goal['type'] == 'form' and $cnt_activities > 0) {
                $cnt++;
            } else {
                $cnt += $cnt_activities;
            }
        }
        return $cnt;
    }

    /**
     * Count enabled Recommenders
     * @param array $recommender_objs
     * @return int
     */
    private function countRecommendersEnabled( $recommender_objs = []) {
        $c=0;
        foreach ($recommender_objs as $recommender) {
            if ($recommender->isEnabled()) {
                $c++;
            }
        }
        return $c;
    }

    /**
     * Builds an OptionsWidget for the sidebar
     * @param string $route current route
     */
    private function buildOptionsWidget($route) {
        $options_widget = new OptionsWidget();
        Sidebar::Get()->addWidget($options_widget);
    }

    /**
     * widget for changing views / activity filters
     * @param string $view current view
     * @param string $context_route
     * @param string $rec_id current recommender id
     */
    private function buildViewsWidget($view=null, $context_route='index', $rec_id = null) {
        $views_widget = new ViewsWidget();
        $views_widget->addCSSClass('siddata-view');
        // get first view if SIDDATA_view is not set or user selected other recommender
        if (!$_SESSION['SIDDATA_view'] or !$view) {
            $_SESSION['SIDDATA_view'] = 'all';
        }

        if ($context_route == 'index') {
            $recommender_objs = $this->getManager()->getAllRecommender();
            $recommender = array_values(array_filter($recommender_objs, function($r) { return $r->getName() == 'Startseite'; }))[0];
        } else {
            $recommender = $this->getManager()->findRecommender($rec_id);
        }

        $open_cnt = $this->countActivitiesByStatus($recommender, 'open');
        $done_cnt = $this->countActivitiesByStatus($recommender, 'done');
        $snoozed_cnt = $this->countActivitiesByStatus($recommender, 'snoozed');
        $discarded_cnt = $this->countActivitiesByStatus($recommender, 'discarded');

        $mode = $_SESSION['SIDDATA_view'];
        $link = new LinkElement("Offen: ".$open_cnt, $this->link_for('siddata/change_view/all/' . $context_route . (isset($rec_id) ? '/' . $rec_id : '')),
            Icon::create($this->plugin->getPluginURL() . "/assets/images/icons/blue/open.svg", Icon::ROLE_CLICKABLE), ['class'=>'siddata-view-element']);
        $views_widget->addElement($link->setActive($mode == 'all'));
        $link = new LinkElement("Abgeschlossen: ".$done_cnt, $this->link_for('siddata/change_view/done/' . $context_route . (isset($rec_id) ? '/' . $rec_id : '')),
            Icon::create("accept", Icon::ROLE_CLICKABLE), ['class'=>'siddata-view-element']);
        $views_widget->addElement($link->setActive($mode == 'done'));
        $link = new LinkElement("Pausiert: ".$snoozed_cnt, $this->link_for('siddata/change_view/snoozed/' . $context_route . (isset($rec_id) ? '/' . $rec_id : '')),
            Icon::create("pause", Icon::ROLE_CLICKABLE), ['class'=>'siddata-view-element']);
        $views_widget->addElement($link->setActive($mode == 'snoozed'));
        $link = new LinkElement("Verworfen: ".$discarded_cnt, $this->link_for('siddata/change_view/discarded/' . $context_route . (isset($rec_id) ? '/' . $rec_id : '')),
            Icon::create("decline", Icon::ROLE_CLICKABLE), ['class'=>'siddata-view-element']);
        $views_widget->addElement($link->setActive($mode == 'discarded'));
        Sidebar::Get()->addWidget($views_widget);
    }

    /**
     * displays main navigation information if not already answered
     */
    private function setMainNavInformation() {
        // main navigation question
        $config = Config::get();
        if (!$config['SIDDATA_nav'] && is_null(UserConfig::get(User::findCurrent()->id)->getValue('SIDDATA_USER_NAV'))) {
            PageLayout::postInfo('Aktiviere das <a data-dialog="size=auto;" href='.$this->link_for('siddata/main_navigation_question').' >"Siddata-Icon in der Hauptnavigation"</a>.');
        }
    }

    /**
     * build button for emptying cache (only used in debug mode)
     */
    private function buildEmptyCacheButton() {
        $actions = new ActionsWidget();
        $actions->setTitle('Debugging-Aktionen');
        $actions->addLink('Cache leeren', $this->link_for('siddata/empty_cache'),
            Icon::create('trash', Icon::ROLE_CLICKABLE));
        Sidebar::Get()->addWidget($actions);
    }

    protected function processResponse($response, $error_details='', $default_success=null, $default_error=null) {
        if (!$response) {
            if ($default_error) {
                $this->plugin->postError($default_error);
            } else {
                $this->plugin->postError();
            }
        }

        parent::processResponse($response, $error_details, $default_success, $default_error);
    }

    private function processResponses($responses, $error_details='', $default_success=null, $default_error=null) {
        $success = true;
        foreach ($responses as $response) {
            if ($response and $response['http_code'] != 200) {
                $this->processResponse($response, $error_details, $default_success, $default_error);
                $success = false;
            }
        }

        if ($success) {
            $this->processResponse(reset($responses), $error_details, $default_success, $default_error);
        }
    }

    /**
     * creates a fragment which points to previous or next activity of the current activity
     * inside the respective goal
     *
     * @param $activity SiddataActivity current activity
     * @return string fragment pointing to previous or next activity
     */
    private function getFragment($activity) {
        $goal = $this->getManager()->findGoal($activity->getGoal()['id']);

        $prev_activity = $goal->getPreviousActivity($activity['id']);
        $next_activity = $goal->getNextActivity($activity['id']);

        if ($prev_activity) {
            return '#siddata-activity_' . $prev_activity['id'];
        } elseif ($next_activity) {
            return '#siddata-activity_' . $next_activity['id'];
        } else {
            return null;
        }
    }

    private function symmetric_encrypt($message) {
        return $this->getClient()->getCrypter()->symmetric_encrypt($message);
    }

    private function symmetric_decrypt($message) {
        return $this->getClient()->getCrypter()->symmetric_decrypt($message);
    }

    private static function containsGoalOfType($goals, $type) {
        foreach ($goals as $goal) {
            if ($goal->getType() == $type) {
                return true;
            }
        }
    }
}
