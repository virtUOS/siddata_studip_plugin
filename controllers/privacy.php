<?php

/**
 * Class PrivacyController
 * @author Niklas Dettmer <ndettmer@uos.de>
 * @author Sebastian Osada <sebastian.osada@uni-osnabrueck.de>
 * @author Dennis Benz <dbenz@uni-osnabrueck.de>
 */
class PrivacyController extends SiddataControllerAbstract
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $this->factory = new Flexi_TemplateFactory($this->plugin->getPluginPath() . '/templates');

        // add specific stylesheet and script - see also SiddataPlugin->perform()
        PageLayout::addStylesheet($this->plugin->getPluginURL() . '/assets/stylesheets/privacy.css?v=' . $this->plugin->getMetadata()['version']);

        // Helpbar entry
        Helpbar::Get()->addLink("Häufig gestellte Fragen zu Siddata", $this->link_for('faq/index'));

        $this->genders = ['unbekannt', 'männlich', 'weiblich', 'divers'];

        // update user's permissions
        $this->updateAllowed();
    }

    /**
     * standard route
     */
    public function index_action() {
        PageLayout::setTitle('Siddata: Verwaltung meiner persönlichen Daten');
        Navigation::activateItem('siddata/privacy');

        // collect Stud.IP data for current user (only for display in views)
        $data = [];
        $user = User::findCurrent();

        // collect studycourses
        $data['subjects'] = [];
        $data['degrees'] = [];
        $data['semesters'] = [];
        foreach($user->studycourses as $studycourse) {
            $data['subjects'][] = $studycourse->studycourse_name;
            $data['degrees'][] = $studycourse->degree_name;
            $data['semesters'][] = $studycourse->semester;
        }

        // collect attended courses
        $data['courses'] = [];
        foreach($user->course_memberships as $membership) {
            $data['courses'][] = $membership->course_name;
        }

        // collect gender
        $data['gender'] = $this->genders[$user->geschlecht];

        // collect institute memberships
        $data['institutes'] = [];
        foreach($user->institute_memberships as $membership) {
            $data['institutes'][] = $membership->institute_name;
        }

        // save as member variable for access in views
        $this->studip_data = $data;
    }

    /**
     * Route for submitting data sharing permission
     * @throws Trails_DoubleRenderError
     */
    public function data_action() {
        // ternary operator to ensure boolean value
        $_SESSION['SIDDATA_share'] = [
            "subject" => [
                "brain" => (Request::get('siddata-share-subject-brain') ? true: false),
                "social" => (Request::get('siddata-share-subject-social') ? true: false)
            ],
            "degree" => [
                "brain" => (Request::get('siddata-share-degree-brain') ? true: false),
                "social" => (Request::get('siddata-share-degree-social') ? true: false)
            ],
            "semester" => [
                "brain" => (Request::get('siddata-share-semester-brain') ? true: false),
                "social" => (Request::get('siddata-share-semester-social') ? true: false)
            ],
            "courses" => [
                "brain" => (Request::get('siddata-share-courses-brain') ? true: false),
                "social" => (Request::get('siddata-share-courses-social') ? true: false)
            ],
            "gender" => [
                "brain" => (Request::get('siddata-share-gender-brain') ? true: false),
                "social" => (Request::get('siddata-share-gender-social') ? true: false)
            ],
            "institutes" => [
                "brain" => (Request::get('siddata-share-institutes-brain') ? true: false),
                "social" => (Request::get('siddata-share-institutes-social') ? true: false)
            ],
            "usetimes" => [
                "brain" => (Request::get('siddata-share-usetimes-brain') ? true: false),
                "social" => (Request::get('siddata-share-usetimes-social') ? true: false)
            ]
        ];

        $this->sendAllowed();

        $this->redirect('privacy/index');
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
            'Fehler beim Senden der Datenschutzangaben.<br>',
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

}
