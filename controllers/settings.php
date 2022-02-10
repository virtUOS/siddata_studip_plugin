<?php

/**
 * Class SettingsController
 *
 * @author Niklas Dettmer <ndettmer@uos.de>
 * @author Sebastian Osada <sebastian.osada@uni-osnabrueck.de>
 * @author Dennis Benz <dbenz@uni-osnabrueck.de>
 * @author Philipp Schüttlöffel <schuettloeffel@zqs.uni-hannover.de>
 *
 */
class SettingsController extends SiddataControllerAbstract
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $this->factory = new Flexi_TemplateFactory($this->plugin->getPluginPath() . '/templates');

        // add specific stylesheet and script - see also SiddataPlugin->perform()
        PageLayout::addStylesheet($this->plugin->getPluginURL() . '/assets/stylesheets/base.css?v=' . $this->plugin->getMetadata()['version']);
        PageLayout::addStylesheet($this->plugin->getPluginURL() . '/assets/stylesheets/privacy.css?v=' . $this->plugin->getMetadata()['version']);
        PageLayout::addScript($this->plugin->getPluginURL() . '/assets/javascripts/settings.js?v=' . $this->plugin->getMetadata()['version']);

        // Helpbar entry
        Helpbar::Get()->addLink("Häufig gestellte Fragen zu Siddata", $this->link_for('faq/index'));

        $this->genders = ['unbekannt', 'männlich', 'weiblich', 'divers'];

        $this->buildSidebarNavigation();
    }

    /**
     * standard route
     * @throws Trails_DoubleRenderError
     */
    public function index_action() {
        $this->redirect('settings/privacy');
    }

    /**
     * route for privacy settings
     */
    public function privacy_action() {
        PageLayout::setTitle('Siddata: Nutzungsbedingungen');
        Navigation::activateItem('siddata/settings/privacy');
        PageLayout::addScript($this->plugin->getPluginURL() . '/assets/javascripts/settings.js?v=' . $this->plugin->getMetadata()['version']);

        // true if siddata terms are accepted
        $this->terms_accepted = UserConfig::get(User::findCurrent()->id)->getValue('SIDDATA_TERMS_ACCEPTED');
        // store initially that privacy policy not seen
        if (!$this->terms_accepted && !isset($_COOKIE["privacy_policy_seen"])) {
            setcookie("privacy_policy_seen", "0");
        }
    }

    /**
     * route for privacy policy
     */
    public function privacy_policy_action() {
        PageLayout::setTitle('Siddata: Datenschutzbestimmungen');
        Navigation::activateItem('siddata/settings/privacy-policy');
        PageLayout::addScript($this->plugin->getPluginURL() . '/assets/javascripts/settings.js?v=' . $this->plugin->getMetadata()['version']);

        $terms_accepted = UserConfig::get(User::findCurrent()->id)->getValue('SIDDATA_TERMS_ACCEPTED');
        if (!$terms_accepted) {
            // store privacy policy seen in persistent variable
            setcookie("privacy_policy_seen", "1");
        }
    }

    /**
     * route for user data release
     * @throws Trails_DoubleRenderError
     */
    public function privacy_data_action() {
        // Redirect to privacy settings if terms not accepted
        if (!UserConfig::get(User::findCurrent()->id)->getValue('SIDDATA_TERMS_ACCEPTED')) {
            $this->redirect("settings/privacy");
            return;
        }

        PageLayout::setTitle('Siddata: Datenfreigabeeinstellungen');
        Navigation::activateItem('siddata/settings/privacy-data');
        PageLayout::addScript($this->plugin->getPluginURL() . '/assets/javascripts/settings.js?v=' . $this->plugin->getMetadata()['version']);

        $actions = new ActionsWidget();
        $actions->addLink('Meine Daten löschen', $this->link_for('settings/delete_student_confirm'),
            Icon::create('trash', Icon::ROLE_CLICKABLE),
            [
                'title' => 'Alle meine Daten unwiderruflich löschen',
                'data-dialog' => 'size=auto;'
            ]);
        Sidebar::Get()->addWidget($actions);

        // true if siddata terms are accepted
        $this->terms_accepted = UserConfig::get(User::findCurrent()->id)->getValue('SIDDATA_TERMS_ACCEPTED');
        $this->first_run = Request::int('first_run');

        // collect Stud.IP data for current user (only for display in views)
        $data = [];
        $user = User::findCurrent();

        // collect studycourses
        $data['studycourses'] = [];
        foreach($user->studycourses as $studycourse) {
            $data['studycourses'][] = [
                'id' => $this->getClient()->getCrypter()->std_encrypt($studycourse->id),
                'subject' => [
                    'name' => $studycourse->studycourse->name,
                    // encrpyt because this normally contains the Stud.IP user id
                    'id' => $studycourse->studycourse->id
                ],
                'degree' => [
                    'name' => $studycourse->degree->name,
                    'id' => $studycourse->degree->id
                ],
                'semester' => $studycourse->semester
            ];
        }

        // collect attended courses
        $data['courses'] = [];
        foreach($user->course_memberships as $membership) {
            $backend_course = $this->getClient()->getCourses($membership->course->id);
            if ($backend_course['http_code'] == 200) {
                $backend_course_response = json_decode($backend_course['response'], true);
                $backend_course_id = $backend_course_response['data'][0]['attributes']['studip_id'];
                if ($membership->course->id == $backend_course_id) {
                    $data['courses'][] = [
                        'name' => $membership->course->name,
                        'id' => $membership->course->id
                    ];
                }
            }
        }

        $this->saved_student = json_decode($this->getManager()->getSettingsStudentAsJson(), true);
        $this->saved_studycourses = json_decode($this->getManager()->getStudyCoursesAsJson(), true);

        if (is_array($this->saved_student['data'][0]['relationships']['institutes_brain']['data'])) {
            $this->saved_inst_ids_brain = array_column($this->saved_student['data'][0]['relationships']['institutes_brain']['data'], 'id');
        }
        if (is_array($this->saved_student['data'][0]['relationships']['institutes_social']['data'])) {
            $this->saved_inst_ids_social = array_column($this->saved_student['data'][0]['relationships']['institutes_social']['data'], 'id');
        }
        if (is_array($this->saved_student['data'][0]['relationships']['courses_brain']['data'])) {
            $this->saved_course_ids_brain = array_column($this->saved_student['data'][0]['relationships']['courses_brain']['data'], 'id');
        }
        if (is_array($this->saved_student['data'][0]['relationships']['courses_social']['data'])) {
            $this->saved_course_ids_social = array_column($this->saved_student['data'][0]['relationships']['courses_social']['data'], 'id');
        }
        $this->saved_gender_brain_set = isset($this->saved_student['data'][0]['attributes']['gender_brain']);
        $this->saved_gender_social_set = isset($this->saved_student['data'][0]['attributes']['gender_social']);
        $this->saved_data_donation = $this->saved_student['data'][0]['attributes']['data_donation'];


        // collect gender
        $data['gender'] = $this->genders[$user->geschlecht];

        // collect institute memberships
        $data['institutes'] = [];
        foreach($user->institute_memberships as $membership) {
            $data['institutes'][] = [
                'name' => $membership->institute->name,
                'id' => $membership->institute->id
            ];
        }

        // save as member variable for access in views
        $this->studip_data = $data;
    }

    /**
     * Route for confirmation of deleting personal data
     */
    public function delete_student_confirm_action() {
        // configure page
        PageLayout::setTitle('Bitte die Aktion bestätigen');
        PageLayout::addStylesheet($this->plugin->getPluginURL() . '/assets/stylesheets/activity.css?v=' . $this->plugin->getMetadata()['version']);
    }

    /**
     * Route for deleting personal data
     * @throws Trails_DoubleRenderError
     */
    public function delete_student_action() {
        $response = $this->getClient()->deleteStudent();
        $this->processResponse($response,
            'Fehler beim Löschen der Nutzerdaten.<br>',
            'Alle Daten wurden gelöscht.'
        );

        if ($response['http_code'] == 200) {
            $this->getManager()->invalidateCache();
            UserConfig::get(User::findCurrent()->id)->store('SIDDATA_USER_NAV', False);
            UserConfig::get(User::findCurrent()->id)->store('SIDDATA_TERMS_ACCEPTED', False);
            $this->redirect('profile_settings/index');
        } else {
            $this->redirect('siddata/index');
        }

    }

    /**
     * route for recommender settings
     * @throws Trails_DoubleRenderError
     */
    public function recommender_action() {
        // Redirect to privacy settings if terms not accepted
        if (!UserConfig::get(User::findCurrent()->id)->getValue('SIDDATA_TERMS_ACCEPTED')) {
            $this->redirect("settings/privacy");
            return;
        }

        PageLayout::setTitle('Siddata: Funktionseinstellungen');
        Navigation::activateItem('siddata/settings/recommender');
        PageLayout::addScript($this->plugin->getPluginURL() . '/assets/javascripts/settings.js?v=' . $this->plugin->getMetadata()['version']);

        $recommenders = json_decode($this->getManager()->getRecommenderAsJson(), true)['data'];
        if (is_array($recommenders)) {
            $this->available_recommenders = $recommenders;
            $this->activated_recommender_ids = array_column(
                array_filter(
                    $this->available_recommenders,
                    function ($r) {
                        return $r['attributes']['enabled'];
                    }
                ), 'id'
            );
        }
    }

    /**
     * route for profile settings
     */
    public function profile_action() {
        PageLayout::setTitle('Siddata: Profileinstellungen');
        Navigation::activateItem('siddata/settings/profile');
        PageLayout::addScript($this->plugin->getPluginURL() . '/assets/javascripts/settings.js?v=' . $this->plugin->getMetadata()['version']);

        $this->profile_settings_link = $this->link_for("profile_settings/index");
    }

    /**
     * Builds the sidebar navigation items for the settings page
     */
    private function buildSidebarNavigation() {
        $terms_accepted = UserConfig::get(User::findCurrent()->id)->getValue('SIDDATA_TERMS_ACCEPTED');

        $nav_item = new Navigation('Nutzungsbedingungen');
        $nav_item->setURL(PluginEngine::getURL(
            $this->plugin,
            array(),
            'settings/privacy'
        ));
        Navigation::addItem('/siddata/settings/privacy', $nav_item);

        $nav_item = new Navigation('Datenschutzbestimmungen');
        $nav_item->setURL(PluginEngine::getURL(
            $this->plugin,
            array(),
            'settings/privacy_policy'
        ));
        Navigation::addItem('/siddata/settings/privacy-policy', $nav_item);

        if ($terms_accepted) {
            $nav_item = new Navigation('Datenfreigabe');
            $nav_item->setURL(PluginEngine::getURL(
                $this->plugin,
                array(),
                'settings/privacy_data'
            ));
            Navigation::addItem('/siddata/settings/privacy-data', $nav_item);

            $nav_item = new Navigation('Funktionen');
            $nav_item->setURL(PluginEngine::getURL(
                $this->plugin,
                array(),
                'settings/recommender'
            ));
            Navigation::addItem('/siddata/settings/recommender', $nav_item);
        }

        $config = Config::get();
        if (!$config['SIDDATA_nav']) {
            $nav_item = new Navigation('Profileinstellungen');
            $nav_item->setURL(PluginEngine::getURL(
                $this->plugin,
                array(),
                'settings/profile'
            ));
            Navigation::addItem('/siddata/settings/profile', $nav_item);
        }
    }

    /**
     * Processes the terms accepting form
     * @throws Trails_DoubleRenderError
     */
    public function terms_action() {
        $terms_accepted = Request::int('terms_accepted');

        if ($terms_accepted) {
            UserConfig::get(User::findCurrent()->id)->store('SIDDATA_TERMS_ACCEPTED', true);
            $this->plugin->postSuccess('Deine Einverständniserklärung wurde gespeichert. Bitte entscheide dich, welche Daten du teilen möchtest.');
            $this->redirect('settings/privacy_data?first_run=1');
        } else {
            $this->redirect('settings');
        }
    }

    /**
     * Route for submitting shared data
     * @throws Trails_DoubleRenderError
     */
    public function data_action() {
        // Redirect to privacy settings if terms not accepted
        if (!UserConfig::get(User::findCurrent()->id)->getValue('SIDDATA_TERMS_ACCEPTED')) {
            $this->redirect("settings/privacy");
            return;
        }

        $this->studip_data = json_decode(Request::get('siddata-studip-data'), true);
        $first_run = Request::int('first_run');

        $studip_uid = User::findCurrent()->getId();
        $user_origin_id = $this->getClient()->getCrypter()->std_encrypt($studip_uid);

        // build user object
        $user = [
            'type' => 'SiddataUser',
            'id' => $user_origin_id,
            'attributes' => [
                'gender_brain' => Request::get('siddata-share-gender-brain')? $this->studip_data['gender'] : null,
                'gender_social' => Request::get('siddata-share-gender-social')? $this->studip_data['gender'] : null,
                'data_donation' => Request::get('siddata-share-usage-data')? true: false,
            ],
            'relationships' => [
                'courses_brain' => ['data' => []],
                'institutes_brain' => ['data' => []],
                'courses_social' => ['data' => []],
                'institutes_social' => ['data' => []],
            ]
        ];

        if (isset($_COOKIE["privacy_policy_seen"])) {
            $user['attributes']['data_regulations'] = $_COOKIE["privacy_policy_seen"]? true: false;
        }

        $studycourses = [
            'data' => []
        ];

        // iterate over studycourses and collect permissions
        foreach ($this->studip_data['studycourses'] as $sc) {
            $share_subject_brain = Request::get('siddata-share-sc-subject-brain_'.$sc['id']) ? true: false;
            $share_degree_brain = Request::get('siddata-share-sc-degree-brain_'.$sc['id']) ? true: false;
            $share_semester_brain = Request::get('siddata-share-sc-semester-brain_'.$sc['id']) ? true: false;
            $share_subject_social = Request::get('siddata-share-sc-subject-social_'.$sc['id']) ? true: false;
            $share_degree_social = Request::get('siddata-share-sc-degree-social_'.$sc['id']) ? true: false;
            $share_semester_social = Request::get('siddata-share-sc-semester-social_'.$sc['id']) ? true: false;

            if ($share_subject_brain or $share_subject_social or $share_degree_brain or $share_degree_social or $share_semester_brain or $share_semester_social) {

                $studycourse = [
                    'type' => 'UserStudyCourse',
                    'attributes' => [
                        'studip_id' => $sc['id']
                    ],
                    'relationships' => [
                        'user' => [
                            'data' => [
                                [
                                    'type' => 'SiddataUser',
                                    'id' => $user_origin_id
                                ]
                            ]
                        ],
                    ]
                ];

                if ($share_subject_brain or $share_subject_social) {
                    $studycourse['relationships']['subject']['data'][] = [
                        'type' => 'StudipSubject',
                        'id' => $sc['subject']['id']
                    ];
                }
                if ($share_degree_brain or $share_degree_social) {
                    $studycourse['relationships']['degree']['data'][] = [
                        'type' => 'StudipDegree',
                        'id' => $sc['degree']['id']
                    ];
                }
                if ($share_semester_brain or $share_semester_social) {
                    $studycourse['attributes']['semester'] = $sc['semester'];
                }

                $studycourse['attributes']['share_subject_brain'] = $share_subject_brain;
                $studycourse['attributes']['share_subject_social'] = $share_subject_social;
                $studycourse['attributes']['share_degree_brain'] = $share_degree_brain;
                $studycourse['attributes']['share_degree_social'] = $share_degree_social;
                $studycourse['attributes']['share_semester_brain'] = $share_semester_brain;
                $studycourse['attributes']['share_semester_social'] = $share_semester_social;

                $studycourses['data'][] = $studycourse;
            }

        }

        // iterate over institutes and collect permissions
        foreach ($this->studip_data['institutes'] as $inst) {
            $share_inst_brain = Request::get('siddata-share-institute-brain_'.$inst['id'])? true: false;
            $share_inst_social = Request::get('siddata-share-institute-social_'.$inst['id'])? true: false;

            if ($share_inst_brain) {
                $user['relationships']['institutes_brain']['data'][] = [
                    'type' => 'StudipInstitute',
                    'id' => $inst['id']
                ];
            }
            if ($share_inst_social) {
                $user['relationships']['institutes_social']['data'][] = [
                    'type' => 'StudipInstitute',
                    'id' => $inst['id']
                ];
            }

        }

        // iterate over courses and collect permissions
        foreach ($this->studip_data['courses'] as $c) {
            $share_course_brain = Request::get('siddata-share-course-brain_'. $c['id'])? true: false;
            $share_course_social = Request::get('siddata-share-course-social_'. $c['id'])? true: false;

            if ($share_course_brain) {
                $user['relationships']['courses_brain']['data'][] = [
                    'type' => 'StudipCourse',
                    'id' => $c['id']
                ];
            }
            if ($share_course_social) {
                $user['relationships']['courses_social']['data'][] = [
                    'type' => 'StudipCourse',
                    'id' => $c['id']
                ];
            }
        }

        // send requests to backend
        $json_scs = SiddataDataManager::json_encode($studycourses);
        $json_user = SiddataDataManager::json_encode(['data' => $user]);
        $this->processResponse($this->getClient()->postStudyCourses($json_scs), 'Fehler beim Senden der Studieninformationen.<br>');
        $this->processResponse($this->getClient()->patchStudent($json_user), 'Fehler beim Senden der Nutzerdaten<br>');

        $config = Config::get();
        if ($config['SIDDATA_Debug_Info']) {
            $this->jsonDump($json_scs, 'StudyCourses');
            $this->jsonDump($json_user, 'User');
        }
        // route depending on first start or later
        if ($first_run == 1) {
            $this->redirect('siddata');
        }
        else {
            $this->redirect('settings/privacy_data');
        }
    }

    /**
     * Route for submitting recommenders to be activated for the current user
     * @throws Trails_DoubleRenderError
     */
    public function post_recommender_action () {
        // collect chosen recommenders
        $chosen = Request::getArray('siddata-activate-rec');
        $recommenders = json_decode($this->getManager()->getRecommenderAsJson(), true)['data'];
        for ($i = 0; $i < count($recommenders); $i++) {
            if (in_array($recommenders[$i]['id'], $chosen) || $recommenders[$i]['attributes']['name']=="Startseite") {
                $recommenders[$i]['attributes']['enabled'] = true;
            } else {
                $recommenders[$i]['attributes']['enabled'] = false;
            }
        }

        // build json data
        $recommenders = [
            'data' => $recommenders
        ];

        // send request
        $this->processResponse($this->getClient()->patchRecommender(SiddataDataManager::json_encode($recommenders)),
            'Beim Aktivieren der Funktionen ist ein Fehler aufgetreten.<br>',
            'Die Recommender-Module wurden aktiviert.',
            'Beim Aktivieren der Funktionen ist ein Fehler aufgetreten.'
        );
        $this->redirect('settings/recommender');
    }

    /**
     * Route for displaying data currently known by Siddata
     */
    public function saved_action() {
        $this->remote_data = $this->getClient()->getUserData();
    }

    /**
     * Sending data sharing permission to Brain
     */
    private function sendAllowed() {
        $json = SiddataDataManager::json_encode($_SESSION['SIDDATA_share']);
        $this->processResponse($this->getClient()->sendUserData($json),
            'Fehler beim Senden der Datenschutzangaben.',
            'Vielen Dank für die Unterstützung!',
            'Es ist ein Fehler beim Übermitteln Ihrer Datenschutzangaben aufgetreten. '
            . $this->plugin->error_msg);
    }

    /**
     * Retrieving user's current data sharing permission from Brain
     */
    private function updateAllowed() {
        $_SESSION['SIDDATA_share'] = $this->remote_data = $this->getClient()->getUserData();

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
}
